<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\Employee; 
use Illuminate\Http\Request;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Auth; 
use App\Http\Controllers\Controller; 
use App\Notifications\SubstituteTeacherAssignment; 
use App\Notifications\LeaveApplicationSubmittedForHR;
use App\Models\User; 
use App\Notifications\LeaveApplicationSubmittedForAH; 
use App\Notifications\LeaveApplicationSubmittedForAdmin;
use App\Models\LeaveType;
use App\Models\LeaveApplicationClass; 

use App\Exports\LeaveApplicationsExport;
use Maatwebsite\Excel\Facades\Excel;

class LeaveApplicationController extends Controller
{
    /**
     * Display a listing of leave applications for the logged-in employee.
     */
    public function index(Request $request)
    {
        $id = Auth::user()->employee->id;
        
        $leaveTypeId = $request->input('leave_type_id');
        $status = $request->input('status');

        $query = LeaveApplication::with(['employee', 'leaveType'])
            ->where('employee_id', $id)
            ->orderBy('date_filed', 'desc');

        $query->when($leaveTypeId, function ($q, $leaveTypeId) {
            return $q->where('leave_type_id', $leaveTypeId);
        });

        $query->when($status, function ($q, $status) {
            return $q->where('approval_status', $status);
        });

        $leaveApplications = $query->paginate(10);
        
        $employee = Auth::user()->employee;
        $remainingCredits = $employee->getRemainingLeaveCredits();
        
        // For filter dropdowns
        $employees = Employee::orderBy('last_name')->get();
        $leaveTypes = LeaveType::orderBy('name')->get();
        $approvalStatuses = ['pending', 'approved_with_pay', 'approved_without_pay', 'rejected'];

        return view('leave_applications.index', compact('leaveApplications', 'employees', 'leaveTypes', 'approvalStatuses', 'remainingCredits', 'leaveTypeId', 'status'));
    }

    /**
     * Display ALL leave applications (for HR/Admin view).
     */
    public function all(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $leaveTypeId = $request->input('leave_type_id'); 
        $status = $request->input('status'); 

        $query = LeaveApplication::with(['employee', 'leaveType'])
            ->orderBy('date_filed', 'desc');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId); 
        }
        if ($status) {
            $query->where('approval_status', $status);
        }

        $leaveApplications = $query->paginate(10); 
        
        // For filter dropdowns
        $employees = Employee::orderBy('last_name')->get();
        $leaveTypes = LeaveType::all();
        $approvalStatuses = ['pending', 'approved_with_pay', 'approved_without_pay', 'rejected', 'cancelled'];

        return view('leave_applications.all', compact(
            'leaveApplications', 
            'employees', 
            'leaveTypes', 
            'approvalStatuses',
            'employeeId', 
            'leaveTypeId',
            'status'
        ));
    }

    /**
     * Show the form for creating a new leave application.
     */
    public function create()
    {
        $leaveTypes = LeaveType::orderBy('name')->get();
        
        // Crucial fix: even in personal mode, pass an empty collection for $employees 
        // to prevent the "Undefined variable" error in the blade.
        $employees = collect(); 

        $loggedInEmployee = null;
        $remainingCredits = null;

        if (Auth::check() && Auth::user()->employee) {
            $loggedInEmployee = Auth::user()->employee;
            $remainingCredits = $loggedInEmployee->getRemainingLeaveCredits();
        }

        // Required for the teacher/staff sections of the form
        $teachers = Employee::where('role', '!=', 'staff')
                     ->where('user_id', '!=', Auth::id())
                     ->orderBy('last_name')->get();

        $staffPersonnel = Employee::where('user_id', '!=', Auth::id())->orderBy('last_name')->get();

        // Flag to tell the form: "Display personal info, NOT the admin tools"
        $isHrRecordingMode = false;

        return view('leave_applications.create', compact(
            'employees', 'loggedInEmployee', 'teachers', 'staffPersonnel', 
            'leaveTypes', 'remainingCredits', 'isHrRecordingMode'
        ));
    }

    /**
     * Calculate total days including weekends but excluding hardcoded holidays.
     */
    private function calculateWorkDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Calculate total days inclusive (including weekends)
        $totalDays = $start->diffInDays($end) + 1;

        // Hardcoded Holidays from Today (Dec 28, 2025) until Dec 2026
        $holidays = [
            '2025-12-30', '2025-12-31', '2026-01-01', '2026-01-02', 
            '2026-02-25', '2026-04-02', '2026-04-03', '2026-04-04', 
            '2026-04-09', '2026-05-01', '2026-06-12', '2026-08-21', 
            '2026-08-31', '2026-11-01', '2026-11-02', '2026-11-30', 
            '2026-12-08', '2026-12-24', '2026-12-25', '2026-12-30', 
            '2026-12-31',
        ];

        // Count holidays within the date range (inclusive)
        $holidaysInRange = array_filter($holidays, function ($holiday) use ($start, $end) {
            $h = Carbon::parse($holiday);
            return $h->between($start, $end, true);
        });

        // Subtract holidays from total days
        return $totalDays - count($holidaysInRange);
    }

    /**
     * Store a newly created leave application in storage.
     */
    public function store(Request $request)
    {
        $isHrRole = auth()->user()->hasRole('hr');
        $isSelfFiling = (auth()->user()->employee->id == $request->employee_id);
        $isHrDirectRecording = ($isHrRole && !$isSelfFiling);

        $rules = [
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'reason'        => 'required|string|max:1000',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
        ];

        if ($isHrDirectRecording) {
            $rules['approval_status'] = 'required|in:pending,approved_with_pay,approved_without_pay,rejected';
            $rules['hr_remarks']      = 'nullable|string|max:500';
        } else {
            $rules['tasks_endorsed'] = 'nullable|string|max:1000';
            $rules['personnel_to_take_over_id'] = 'nullable|exists:employees,id';
            $rules['classes_data'] = 'nullable|array';
            $rules['classes_data.*.course_code'] = 'nullable|string|max:255';
            $rules['classes_data.*.title'] = 'nullable|string|max:255';
            $rules['classes_data.*.day_time_room'] = 'nullable|string|max:255';
            $rules['classes_data.*.topics'] = 'nullable|string|max:1000';
            $rules['classes_data.*.substitute_teacher_id'] = 'nullable|exists:employees,id';
            $rules['classes_data.*.acknowledgement_signature'] = 'nullable|string|max:255';
        }

        $validatedData = $request->validate($rules);

        $validatedData['total_days'] = $this->calculateWorkDays($validatedData['start_date'], $validatedData['end_date']);
        $validatedData['date_filed'] = now();

        $employee = Employee::find($validatedData['employee_id']);
        $leaveType = LeaveType::find($validatedData['leave_type_id']);
        
        if ($employee && $leaveType) {
            $remainingCredits = $employee->getRemainingLeaveCredits();
            $creditColumn = strtolower(str_replace(' ', '_', $leaveType->name));
            $availableCredits = $remainingCredits[$creditColumn] ?? 0;
            
            if (!$isHrDirectRecording && $availableCredits < $validatedData['total_days']) {
                return redirect()->back()->withInput()
                    ->with('error', "Insufficient credits. Available: {$availableCredits}, Requested: {$validatedData['total_days']}");
            }
        }

        if ($isHrDirectRecording) {
            $validatedData['approval_status'] = $request->approval_status;
            $validatedData['ah_status']       = 'approved'; 
            $validatedData['hr_status']       = 'approved';
            $validatedData['admin_status']    = 'approved';
            
            $hrName = auth()->user()->employee->first_name . ' ' . auth()->user()->employee->last_name;
            $manualNote = $request->hr_remarks ? " - " . $request->hr_remarks : "";
            $validatedData['hr_remarks'] = "Recorded by HR ({$hrName}) on " . now()->format('M d, Y') . $manualNote;

            $validatedData['hr_approved_by'] = auth()->user()->employee->id;
            $validatedData['hr_approved_at'] = now();
        } else {
            $validatedData['approval_status'] = 'pending';
            $validatedData['hr_status']       = 'pending';
            $validatedData['admin_status']    = 'pending';
            $validatedData['ah_status'] = ($employee->role === 'staff') ? 'approved' : 'pending';
            $validatedData['hr_remarks'] = "Self-filed";
        }

        $classesToSave = $validatedData['classes_data'] ?? [];
        unset($validatedData['classes_data']);
        
        $leaveApplication = LeaveApplication::create($validatedData);

        if (!$isHrDirectRecording) {
            if (!empty($classesToSave)) {
                foreach ($classesToSave as $classData) {
                    if (array_filter($classData)) {
                        $leaveClass = $leaveApplication->classesToMiss()->create($classData);
                        if ($leaveClass->substitute_teacher_id && $leaveClass->substituteTeacher->user) {
                            $leaveClass->substituteTeacher->user->notify(new SubstituteTeacherAssignment($leaveClass, $employee->name));
                        }
                    }
                }
            }

            if ($employee->role === 'staff') {
                $hrHeads = User::whereHas('employee', function ($q) { $q->where('role', 'hr'); })->get();
                foreach ($hrHeads as $hr) { $hr->notify(new LeaveApplicationSubmittedForHR($leaveApplication)); }
            } else {
                $academicHeads = User::whereHas('employee', function ($q) { $q->where('role', 'academic_head'); })->get();
                foreach ($academicHeads as $ah) { $ah->notify(new LeaveApplicationSubmittedForAH($leaveApplication)); }
            }
        }

        $route = $isHrDirectRecording ? 'hr.leave_applications.all' : 'leave_applications.index';
        return redirect()->route($route)->with('success', 'Leave record processed successfully.');
    }

    /**
     * Display leave summary.
     */
    public function leaveSummary(Request $request)
    {
        $selectedMonth = $request->input('month', date('Y-m'));
        $date = Carbon::parse($selectedMonth);
        
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        $applications = LeaveApplication::where('approval_status', 'approved_with_pay')
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                      ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth]);
            })
            ->with('employee') 
            ->get();

        $calendar = [];
        for ($d = 1; $d <= $startOfMonth->daysInMonth; $d++) {
            $currentDate = $startOfMonth->copy()->day($d)->toDateString();
            $calendar[$currentDate] = $applications->filter(function ($app) use ($currentDate) {
                return $currentDate >= $app->start_date->toDateString() && 
                       $currentDate <= $app->end_date->toDateString();
            });
        }

        return view('admin.leave-summary', [
            'calendar' => $calendar,
            'monthName' => $date->format('F Y'),
            'currentMonth' => $selectedMonth,
            'startOfWeek' => $startOfMonth->dayOfWeek, 
        ]);
    }
   
    /**
     * Edit leave application.
     */
    public function edit(LeaveApplication $leaveApplication)
    {
        $leaveTypes = LeaveType::orderBy('name')->get();
        $leaveApplication->load(['employee', 'classesToMiss.acknowledgedBy']);
        $employees = Employee::orderBy('last_name')->get();
        
        $teachers = Employee::where('role', '!=', 'staff')
                     ->where('user_id', '!=', Auth::id())
                     ->orderBy('last_name')->get();

        $staffPersonnel = Employee::where('role', 'staff')->orderBy('last_name')->get();
        $loggedInEmployee = Auth::user()->employee;
        $remainingCredits = $loggedInEmployee ? $loggedInEmployee->getRemainingLeaveCredits() : [];

        $isHrRecordingMode = Auth::user()->hasRole('hr') && ($leaveApplication->employee_id !== $loggedInEmployee->id);

        $existingClasses = $leaveApplication->classesToMiss->map(function ($class) {
            $data = $class->toArray();
            $data['acknowledged_by_name'] = $class->acknowledgedBy ? $class->acknowledgedBy->name : null;
            return $data;
        })->toArray();

        return view('leave_applications.edit', compact(
            'leaveApplication', 
            'employees', 
            'loggedInEmployee', 
            'teachers', 
            'staffPersonnel', 
            'existingClasses', 
            'leaveTypes',
            'remainingCredits', 
            'isHrRecordingMode' 
        ));
    }
   
    /**
     * Update leave application.
     */
    public function update(Request $request, LeaveApplication $leaveApplication)
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'reason' => 'required|string|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'tasks_endorsed' => 'nullable|string|max:1000',
            'personnel_to_take_over_id' => 'nullable|exists:employees,id',
            'acknowledgement_personnel_take_over_signature' => 'nullable|string|max:255',
        ];

        $rules['classes_data'] = 'nullable|array';
        $rules['classes_data.*.id'] = 'nullable|exists:leave_application_classes,id';
        $rules['classes_data.*.course_code'] = 'nullable|string|max:255';
        $rules['classes_data.*.title'] = 'nullable|string|max:255';
        $rules['classes_data.*.day_time_room'] = 'nullable|string|max:255';
        $rules['classes_data.*.topics'] = 'nullable|string|max:1000';
        $rules['classes_data.*.substitute_teacher_id'] = 'nullable|exists:employees,id';
        $rules['classes_data.*.acknowledgement_signature'] = 'nullable|string|max:255';

        $validatedData = $request->validate($rules);

        $validatedData['total_days'] = $this->calculateWorkDays($validatedData['start_date'], $validatedData['end_date']);

        $classesToProcess = $validatedData['classes_data'] ?? [];
        unset($validatedData['classes_data']);

        $leaveApplication->update($validatedData);

        $existingClassIds = $leaveApplication->classesToMiss->pluck('id')->toArray();
        $submittedClassIds = collect($classesToProcess)->pluck('id')->filter()->toArray();
        $classesToDelete = array_diff($existingClassIds, $submittedClassIds);
        LeaveApplicationClass::destroy($classesToDelete);

        foreach ($classesToProcess as $classData) {
            $currentClass = null;
            $isDataPresent = array_filter($classData, function($value, $key) {
                return $key !== 'id' && ($value !== null && $value !== '');
            }, ARRAY_FILTER_USE_BOTH);

            if (isset($classData['id']) && in_array($classData['id'], $existingClassIds)) {
                $currentClass = LeaveApplicationClass::find($classData['id']);
                if ($currentClass) {
                    if ($currentClass->sub_ack_at) {
                        unset($classData['substitute_teacher_id']);
                        unset($classData['acknowledgement_signature']);
                    }
                    $currentClass->update($classData);
                }
            } else if ($isDataPresent) {
                $currentClass = $leaveApplication->classesToMiss()->create($classData);
            }

            if ($currentClass && $currentClass->substitute_teacher_id) {
                $shouldNotify = $currentClass->wasRecentlyCreated || ($currentClass->isDirty('substitute_teacher_id') && !$currentClass->sub_ack_at);
                
                if ($shouldNotify) {
                    $substituteTeacher = $currentClass->substituteTeacher;
                    if ($substituteTeacher && $substituteTeacher->user) {
                         $substituteTeacher->user->notify(new SubstituteTeacherAssignment(
                            $currentClass,
                            $leaveApplication->employee->name
                        ));
                    }
                }
            }
        }
        
        return redirect()->route('leave_applications.index')->with('success', 'Leave application updated successfully.');
    }

    /**
     * Display the specified leave application.
     */
    public function show(LeaveApplication $leaveApplication)
    {
        $leaveApplication->load(['employee', 'classesToMiss.substituteTeacher']);
        return view('leave_applications.show', compact('leaveApplication'));
    }

    /**
     * Destroy leave application.
     */
    public function destroy(LeaveApplication $leaveApplication)
    {
        if ($leaveApplication->approval_status !== 'pending') {
            return redirect()->route('leave_applications.index')
                             ->with('error', 'Only pending leave applications can be deleted.');
        }

        $leaveApplication->delete();
        return redirect()->route('leave_applications.index')->with('success', 'Leave application deleted successfully.');
    }

    /**
     * Show form for HR to file leave for someone else.
     */
    public function createByHr()
    {
        $employees = Employee::orderBy('last_name')->get();
        $leaveTypes = LeaveType::orderBy('name')->get();
        
        $loggedInEmployee = null; 
        $remainingCredits = null; 
        $teachers = collect();
        $staffPersonnel = collect();
        $isHrRecordingMode = true;

        return view('leave_applications.create', compact(
            'employees', 'loggedInEmployee', 'teachers', 'staffPersonnel', 
            'leaveTypes', 'remainingCredits', 'isHrRecordingMode'
        ));
    }

    /**
     * Store logic specifically for HR filing.
     */
    public function storeByHr(Request $request)
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required',
            'reason' => 'required|string|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];

        $validatedData = $request->validate($rules);
        $validatedData['total_days'] = $this->calculateWorkDays($request->start_date, $request->end_date);
        $validatedData['date_filed'] = Carbon::now();
        
        $validatedData['hr_status'] = 'approved';
        $validatedData['hr_approved_at'] = now();
        $validatedData['hr_approved_by'] = Auth::user()->employee->id;
        $validatedData['approval_status'] = 'pending'; 
        
        $employee = Employee::find($request->employee_id);
        $validatedData['ah_status'] = ($employee->role === 'staff') ? 'approved' : 'pending';

        $leaveApplication = LeaveApplication::create($validatedData);

        return redirect()->route('leave_applications.all')
            ->with('success', 'Leave filed successfully for ' . $employee->full_name);
    }

    public function getEmployeeCredits($id)
    {
        $employee = Employee::findOrFail($id);
        return response()->json($employee->getRemainingLeaveCredits());
    }

    public function creditsSummary(Request $request)
    {
        $query = Employee::query();

        if ($request->has('search')) {
            $query->where('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%');
        }

        $employees = $query->orderBy('last_name')->paginate(15);

        $summary = $employees->getCollection()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => "{$employee->last_name}, {$employee->first_name}",
                'role' => $employee->role,
                'credits' => $employee->getRemainingLeaveCredits(), 
            ];
        });

        return view('admin.credits_summary', compact('summary', 'employees'));
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $fileName = 'Leave_Report';
        if ($start && $end) {
            $fileName .= "_{$start}_to_{$end}";
        }
        $fileName .= '.xlsx';

        return Excel::download(new LeaveApplicationsExport($start, $end), $fileName);
    }

    /**
     * Cancel the application and refund credits.
     * LOGIC: Checks if the leave application is approved, then updates status to 'cancelled'.
     * Refunds the leave credits back to the employee for sick leave, incentive leave, and vacation leave.
     */
    public function cancel(LeaveApplication $leaveApplication)
    {
        if ($leaveApplication->approval_status === 'cancelled') {
            return redirect()->back()->with('error', 'This application is already cancelled.');
        }

        if ($leaveApplication->approval_status !== 'approved_with_pay' && $leaveApplication->approval_status !== 'approved_without_pay') {
            return redirect()->back()->with('error', 'Only approved leave applications can be cancelled.');
        }

        $employee = $leaveApplication->employee;
        $leaveType = $leaveApplication->leaveType;
        $daysToRefund = $leaveApplication->total_days;

        $leaveCredit = $employee->leaveCredits()->first();

        if ($leaveCredit) {
            $creditField = strtolower(str_replace(' ', '_', $leaveType->name));
            $leaveCredit->increment($creditField, $daysToRefund);
        }

        $leaveApplication->update([
            'approval_status' => 'cancelled'
        ]);

        return redirect()->back()->with('success', 'Leave application cancelled and ' . $daysToRefund . ' day(s) refunded successfully.');
    }
}