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

        $leaveApplications = $query->paginate(10);
        $employee = Auth::user()->employee;
        $remainingCredits = $employee->getRemainingLeaveCredits();
        
        $employees = Employee::orderBy('name')->get();
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

        $loggedInEmployee = null;
        if (Auth::check() && Auth::user()->employee) {
            $loggedInEmployee = Auth::user()->employee;
        }

        return view('leave_applications.create', compact('employees', 'loggedInEmployee', 'teachers', 'staffPersonnel', 'leaveTypes'));
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