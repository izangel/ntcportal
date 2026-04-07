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

        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }
        if ($status) {
            $query->where('approval_status', $status);
        }
        // Start the base query
        $leaveApplications = LeaveApplication::with('employee')
                                            ->orderBy('date_filed', 'desc')
                                            ->where('employee_id', $id);

        // 1. GET FILTER VALUES from the URL query string
        $leaveTypeId = $request->input('leave_type_id');
        $status = $request->input('status');

        // 2. APPLY FILTERS using conditional 'when' clauses

        // Filter by Leave Type ID
        $leaveApplications->when($leaveTypeId, function ($query, $leaveTypeId) {
            return $query->where('leave_type_id', $leaveTypeId);
        });

        // Filter by Status
        $leaveApplications->when($status, function ($query, $status) {
            return $query->where('approval_status', $status);
        });

        // Execute the final query and paginate
        $leaveApplications = $leaveApplications->paginate(10);

        $leaveApplications = $query->paginate(10);
        $employee = Auth::user()->employee;
        $remainingCredits = $employee->getRemainingLeaveCredits();
        
        $employees = Employee::orderBy('name')->get();
        $leaveTypes = LeaveType::orderBy('name')->get(); 
        $approvalStatuses = ['pending', 'approved_with_pay', 'approved_without_pay', 'rejected']; 
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
        
        // For filter dropdowns (optional)
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

    public function create()
    {
        $leaveTypes = LeaveType::orderBy('name')->get(); 
        $employees = Employee::orderBy('name')->get();
        $teachers = Employee::where('role', '!=', 'staff')
                     ->where('user_id', '!=', Auth::id())
                     ->orderBy('name')->get();
        $staffPersonnel = Employee::where('user_id', '!=', Auth::id())->orderBy('name')->get();

    /**
     * Show the form for creating a new leave application.
     */
    public function create()
    {
        $leaveTypes = LeaveType::orderBy('name')->get(); // Get all programs for the dropdown


        // Fetch all employees (if needed for other dropdowns, otherwise not strictly necessary)
        $employees = Employee::orderBy('last_name')->get();

        

        // Fetch employees with 'teacher' role for the substitute dropdown
        $teachers = Employee::where('role', '!=', 'staff')
                     ->where('user_id', '!=', Auth::id())
                     ->orderBy('last_name')->get();

        // Fetch employees with 'staff' role for the personnel to take over dropdown
        $staffPersonnel = Employee::where('user_id',  '!=', Auth::id())->orderBy('last_name')->get();

        $loggedInEmployee = null;
        $remainingCredits = null;
        if (Auth::check() && Auth::user()->employee) {
            $loggedInEmployee = Auth::user()->employee;
            $remainingCredits = $loggedInEmployee->getRemainingLeaveCredits();
        }

        return view('leave_applications.create', compact('employees', 'loggedInEmployee', 'teachers', 'staffPersonnel', 'leaveTypes', 'remainingCredits'));
    }

    public function store(Request $request)
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required',
            'reason' => 'required|string|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'tasks_endorsed' => 'nullable|string|max:1000',
            'personnel_to_take_over_id' => 'nullable|exists:employees,id',
            'acknowledgement_personnel_take_over_signature' => 'nullable|string|max:255',
        ];

        $rules['classes_data'] = 'nullable|array';
        $rules['classes_data.*.course_code'] = 'nullable|string|max:255';
        $rules['classes_data.*.title'] = 'nullable|string|max:255';
        $rules['classes_data.*.day_time_room'] = 'nullable|string|max:255';
        $rules['classes_data.*.topics'] = 'nullable|string|max:1000';
        $rules['classes_data.*.substitute_teacher_id'] = 'nullable|exists:employees,id';
        $rules['classes_data.*.acknowledgement_signature'] = 'nullable|string|max:255';

        $validatedData = $request->validate($rules);

        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);
        $validatedData['total_days'] = $startDate->diffInDays($endDate) + 1;
        $validatedData['date_filed'] = Carbon::now();
        $validatedData['approval_status'] = 'pending';

        $classesToSave = $validatedData['classes_data'] ?? [];
        unset($validatedData['classes_data']);

        $leaveApplication = LeaveApplication::create($validatedData);

        foreach ($classesToSave as $classData) {
            if (array_filter($classData)) { 
                $leaveApplicationClass = $leaveApplication->classesToMiss()->create($classData);
                if ($leaveApplicationClass->substitute_teacher_id) {
                    $substituteTeacher = $leaveApplicationClass->substituteTeacher;
                    if ($substituteTeacher && $substituteTeacher->user) {
                        $substituteTeacher->user->notify(new SubstituteTeacherAssignment(
                            $leaveApplicationClass,
                            $leaveApplication->employee->name 
                        ));
                    }
                }
            }
        }
        
        $user = Auth::user();
        if ($user->hasRole('staff')){
            $hrHeads = User::whereHas('employee', function ($query) {
                $query->where('role', 'hr');
            })->get();
            foreach ($hrHeads as $hrUser) {
                $hrUser->notify(new LeaveApplicationSubmittedForHR($leaveApplication));
            }
        } else {
            $academicHeads = User::whereHas('employee', function ($query) {
                $query->where('role', 'academic_head');
            })->get();
            foreach ($academicHeads as $ahUser) {
                $ahUser->notify(new LeaveApplicationSubmittedForAH($leaveApplication));
            }
        }

        return redirect()->route('leave_applications.index')->with('success', 'Leave application submitted successfully.');
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
        // Remaining 2025
        '2025-12-30', // Rizal Day
        '2025-12-31', // Last Day of the Year
        
        // 2026
        '2026-01-01', // New Year's Day
        '2026-01-02', // Special Non-Working Day
        '2026-02-25', // EDSA Revolution Anniversary
        '2026-04-02', // Maundy Thursday
        '2026-04-03', // Good Friday
        '2026-04-04', // Black Saturday
        '2026-04-09', // Araw ng Kagitingan
        '2026-05-01', // Labor Day
        '2026-06-12', // Independence Day
        '2026-08-21', // Ninoy Aquino Day
        '2026-08-31', // National Heroes Day
        '2026-11-01', // All Saints' Day
        '2026-11-02', // All Souls' Day
        '2026-11-30', // Bonifacio Day
        '2026-12-08', // Feast of the Immaculate Conception
        '2026-12-24', // Christmas Eve
        '2026-12-25', // Christmas Day
        '2026-12-30', // Rizal Day
        '2026-12-31', // Last Day of the Year
    ];

    // Count holidays within the date range (inclusive)
    $holidaysInRange = array_filter($holidays, function ($holiday) use ($start, $end) {
        $h = Carbon::parse($holiday);
        return $h->between($start, $end, true);
    });

    // Subtract holidays from total days
    return $totalDays - count($holidaysInRange);
}

    public function store(Request $request)
{
    $rules = [
        'employee_id' => 'required|exists:employees,id',
        'leave_type_id' => 'required',
        'reason' => 'required|string|max:1000',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'tasks_endorsed' => 'nullable|string|max:1000',
        'personnel_to_take_over_id' => 'nullable|exists:employees,id',
        'acknowledgement_personnel_take_over_signature' => 'nullable|string|max:255',
    ];

    $rules['classes_data'] = 'nullable|array';
    $rules['classes_data.*.course_code'] = 'nullable|string|max:255';
    $rules['classes_data.*.title'] = 'nullable|string|max:255';
    $rules['classes_data.*.day_time_room'] = 'nullable|string|max:255';
    $rules['classes_data.*.topics'] = 'nullable|string|max:1000';
    $rules['classes_data.*.substitute_teacher_id'] = 'nullable|exists:employees,id';
    $rules['classes_data.*.acknowledgement_signature'] = 'nullable|string|max:255';

    $validatedData = $request->validate($rules);

    // Use the helper method
    $validatedData['total_days'] = $this->calculateWorkDays($validatedData['start_date'], $validatedData['end_date']);
    
    // Check leave credits availability
    $employee = Employee::find($validatedData['employee_id']);
    $leaveType = LeaveType::find($validatedData['leave_type_id']);
    
    if ($employee && $leaveType) {
        $remainingCredits = $employee->getRemainingLeaveCredits();
        $creditColumn = strtolower(str_replace(' ', '_', $leaveType->name));
        $availableCredits = $remainingCredits[$creditColumn] ?? 0;
        
        // Block if NO credits available
        if ($availableCredits <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Insufficient leave credits for {$leaveType->name}. You have 0 days available. Cannot file leave application.");
        }
        
        // Block if requested days exceed available credits
        if ($availableCredits < $validatedData['total_days']) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Insufficient leave credits for {$leaveType->name}. Available: {$availableCredits} days, Requested: {$validatedData['total_days']} days. Please adjust your dates.");
        }
    } else {
        return redirect()->back()
            ->withInput()
            ->with('error', "Invalid employee or leave type.");
    }
    
    $validatedData['date_filed'] = Carbon::now();
    $validatedData['approval_status'] = 'pending';

    if($employee->role ==='staff')
        $validatedData['ah_status'] = 'approved';  // Initialize Academic Head status as pending
    else
        $validatedData['ah_status'] = 'pending';  // Initialize Academic Head status as pending

    $validatedData['hr_status'] = 'pending';  // Initialize HR status as pending
    $validatedData['admin_status'] = 'pending';  // Initialize Admin status as pending

    $classesToSave = $validatedData['classes_data'] ?? [];
    unset($validatedData['classes_data']);

    $leaveApplication = LeaveApplication::create($validatedData);

    foreach ($classesToSave as $classData) {
        if (array_filter($classData)) {
            $leaveApplicationClass = $leaveApplication->classesToMiss()->create($classData);
            if ($leaveApplicationClass->substitute_teacher_id) {
                $substituteTeacher = $leaveApplicationClass->substituteTeacher;
                if ($substituteTeacher && $substituteTeacher->user) {
                    $substituteTeacher->user->notify(new SubstituteTeacherAssignment($leaveApplicationClass, $leaveApplication->employee->name));
                }
            }
        }
    }

    $user = Auth::user();
    if ($user->hasRole('staff')){
        $hrHeads = User::whereHas('employee', function ($query) { $query->where('role', 'hr'); })->get();
        foreach ($hrHeads as $hrUser) { $hrUser->notify(new LeaveApplicationSubmittedForHR($leaveApplication)); }
    } else {
        $academicHeads = User::whereHas('employee', function ($query) { $query->where('role', 'academic_head'); })->get();
        foreach ($academicHeads as $ahUser) { $ahUser->notify(new LeaveApplicationSubmittedForAH($leaveApplication)); }
    }

    return redirect()->route('leave_applications.index')->with('success', 'Leave application submitted successfully.');
}

    public function leaveSummary(Request $request)
{
    $selectedMonth = $request->input('month', date('Y-m'));
    $date = Carbon::parse($selectedMonth);
    
    $startOfMonth = $date->copy()->startOfMonth();
    $endOfMonth = $date->copy()->endOfMonth();
    
    // Fetch applications where approval_status is 'approved' (adjust based on your enum)
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
        'startOfWeek' => $startOfMonth->dayOfWeek, // 0 (Sun) to 6 (Sat)
    ]);
}
   

    public function edit(LeaveApplication $leaveApplication)
    {
        $leaveTypes = LeaveType::orderBy('name')->get(); 
        $leaveApplication->load(['employee', 'classesToMiss.acknowledgedBy']);
        $employees = Employee::orderBy('last_name')->get();
        $teachers = Employee::where('role', '!=', 'staff')
                     ->where('user_id', '!=', Auth::id())
                     ->orderBy('last_name')->get();
        $staffPersonnel = Employee::where('role', 'staff')->orderBy('last_name')->get();
        $loggedInEmployee = null;
        if (Auth::check() && Auth::user()->employee) {
            $loggedInEmployee = Auth::user()->employee;
        }

        $existingClasses = $leaveApplication->classesToMiss->map(function ($class) {
            $data = $class->toArray();
            $data['acknowledged_by_name'] = $class->acknowledgedBy ? $class->acknowledgedBy->name : null;
            return $data;
        })->toArray();

        return view('leave_applications.edit', compact('leaveApplication', 'employees', 'loggedInEmployee', 'teachers', 'staffPersonnel', 'existingClasses', 'leaveTypes'));
    }

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
        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);
        $validatedData['total_days'] = $startDate->diffInDays($endDate) + 1;

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
            } else if (isset($classData['id']) && !$isDataPresent) {
                LeaveApplicationClass::destroy($classData['id']);
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

    // Use the helper method
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
                $shouldNotify = false;
                if ($currentClass->wasRecentlyCreated) {
                    $shouldNotify = true;
                } elseif ($currentClass->isDirty('substitute_teacher_id') && !$currentClass->sub_ack_at) {
                    $shouldNotify = true; 
                }

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

        if ($currentClass && $currentClass->substitute_teacher_id) {
            $shouldNotify = $currentClass->wasRecentlyCreated || ($currentClass->isDirty('substitute_teacher_id') && !$currentClass->sub_ack_at);
            if ($shouldNotify) {
                $substituteTeacher = $currentClass->substituteTeacher;
                if ($substituteTeacher && $substituteTeacher->user) {
                     $substituteTeacher->user->notify(new SubstituteTeacherAssignment($currentClass, $leaveApplication->employee->name));
                }
            }
        }
    }

    return redirect()->route('leave_applications.index')->with('success', 'Leave application updated successfully.');
} // end of update

   

    /**
     * Display the specified leave application.
     */
    public function show(LeaveApplication $leaveApplication)
    {
        $leaveApplication->load(['employee', 'classesToMiss.substituteTeacher']);
        return view('leave_applications.show', compact('leaveApplication'));
    }

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
     * Cancel the application and refund credits.
     * LOGIC: Checks if the leave application is approved, then updates status to 'cancelled'.
     * Refunds the leave credits back to the employee for sick leave, incentive leave, and vacation leave.
     */
    public function cancel(LeaveApplication $leaveApplication)
    {
        // Check if already cancelled
        if ($leaveApplication->approval_status === 'cancelled') {
            return redirect()->back()->with('error', 'This application is already cancelled.');
        }

        // Only allow cancellation of approved applications
        if ($leaveApplication->approval_status !== 'approved_with_pay' && $leaveApplication->approval_status !== 'approved_without_pay') {
            return redirect()->back()->with('error', 'Only approved leave applications can be cancelled.');
        }

        $employee = $leaveApplication->employee;
        $leaveType = $leaveApplication->leaveType;
        $daysToRefund = $leaveApplication->total_days;

        // Get the current academic year leave credit
        $leaveCredit = $employee->leaveCredits()->first();

        if ($leaveCredit) {
            // Map leave type to credit field (sick_leave, vacation_leave, service_incentive_leave)
            $creditField = strtolower(str_replace(' ', '_', $leaveType->name));

            // Refund the leave credits
            $leaveCredit->increment($creditField, $daysToRefund);
        }

        // Update the application status to cancelled
        $leaveApplication->update([
            'approval_status' => 'cancelled'
        ]);

        return redirect()->back()->with('success', 'Leave application cancelled and ' . $daysToRefund . ' day(s) refunded successfully.');
    }
}