<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\Employee; // Assuming you have an Employee model

use Illuminate\Http\Request;
use Carbon\Carbon; // For date handling
use Illuminate\Support\Facades\Auth; // For authenticating user
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
    // Apply middleware if you want to restrict access to authenticated users
    // public function __construct()
    // {
    //     $this->middleware('auth'); // Ensures user is logged in
    //     // You might add specific role middleware here later, e.g., ->middleware('role:admin|hr_head')
    // }

    /**
     * Display a listing of leave applications.
     */
    public function index(Request $request)
    {

             $id = Auth::user()->employee->id;
        
            // Fetch all leave applications with their associated employee data
            $leaveApplications = LeaveApplication::with('employee')
                                                ->orderBy('date_filed', 'desc')
                                            ->where('employee_id',$id)
                                            ->paginate(10); // Paginate for large datasets
           

        $employee = Auth::user()->employee;
        $remainingCredits = $employee->getRemainingLeaveCredits();
        
        
        // For filter dropdowns (optional)
        $employees = Employee::orderBy('name')->get();
        $leaveTypes = ['service_incentive_leave', 'sick_leave', 'vacation_leave', 'other'];
        $approvalStatuses = ['pending', 'approved_with_pay', 'approved_without_pay', 'rejected']; // Basic statuses

        return view('leave_applications.index', compact('leaveApplications', 'employees', 'leaveTypes', 'approvalStatuses', 'remainingCredits'));
    }


        public function all(Request $request)
    {
        // 1. GET FILTER VALUES from the URL query string
        $employeeId = $request->input('employee_id'); // e.g., filter by employee
        // $leaveType = $request->input('type');         // e.g., filter by leave type
        $leaveTypeId = $request->input('leave_type_id'); // Now retrieving the ID
        $status = $request->input('status');          // e.g., filter by status

        // Start the base query
        $leaveApplications = LeaveApplication::with('employee')
                                            ->orderBy('date_filed', 'desc');

        // 2. APPLY FILTERS using conditional 'when' clauses

        // Filter by Employee ID (assuming your form input for Employee is 'employee_id')
        $leaveApplications->when($employeeId, function ($query, $employeeId) {
            return $query->where('employee_id', $employeeId);
        });

        // Filter by Leave Type (No Join needed!)
        $leaveApplications->when($leaveTypeId, function ($query, $leaveTypeId) {
            // Query the correct foreign key column
            return $query->where('leave_type_id', $leaveTypeId); 
        });

        // Filter by Status (assuming your form input for Status is 'status')
        $leaveApplications->when($status, function ($query, $status) {
            // Ensure status matches your database column name, e.g., 'status'
            return $query->where('approval_status', $status);
        });

        // Execute the final query and paginate
        $leaveApplications = $leaveApplications->paginate(10); 
        
        //-------------------------------------------------------------

        // For filter dropdowns (optional)
        $employees = Employee::orderBy('name')->get();
        $leaveTypes = LeaveType::all();
        $approvalStatuses = [
            'pending', 
            'approved_with_pay', 
            'approved_without_pay', 
            'rejected'
        ];

        // 3. PASS THE APPLIED FILTER VALUES to the view
        return view('leave_applications.all', compact(
            'leaveApplications', 
            'employees', 
            'leaveTypes', 
            'approvalStatuses',
            'employeeId', // Pass these back to pre-select the dropdowns
            'leaveTypeId',
            'status'
        ));
    }


       /**
     * Show the form for creating a new leave application.
     */
    public function create()
    {
        $leaveTypes = LeaveType::orderBy('name')->get(); // Get all programs for the dropdown


        // Fetch all employees (if needed for other dropdowns, otherwise not strictly necessary)
        $employees = Employee::orderBy('name')->get();

        

        // Fetch employees with 'teacher' role for the substitute dropdown
        $teachers = Employee::where('role', '!=', 'staff')
                     ->where('user_id', '!=', Auth::id())
                     ->orderBy('name')->get();

        // Fetch employees with 'staff' role for the personnel to take over dropdown
        $staffPersonnel = Employee::where('user_id',  '!=', Auth::id())->orderBy('name')->get();

        $loggedInEmployee = null;
        if (Auth::check() && Auth::user()->employee) {
            $loggedInEmployee = Auth::user()->employee;
        }

        return view('leave_applications.create', compact('employees', 'loggedInEmployee', 'teachers', 'staffPersonnel', 'leaveTypes'));
    }

    /**
 * Calculate total days excluding weekends and hardcoded holidays.
 */
private function calculateWorkDays($startDate, $endDate)
{
    $start = Carbon::parse($startDate);
    $end = Carbon::parse($endDate);

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

    return $start->diffInDaysFiltered(function (Carbon $date) use ($holidays) {
        return $date->isWeekday() && !in_array($date->toDateString(), $holidays);
    }, $end->addDay());
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
        $leaveTypes = LeaveType::orderBy('name')->get(); // Get all programs for the dropdown


        // Eager load relationships for the form, including the acknowledgedBy for display
        $leaveApplication->load(['employee', 'classesToMiss.acknowledgedBy']);

        $employees = Employee::orderBy('last_name')->get();

         // Fetch employees with 'teacher' role for the substitute dropdown
        $teachers = Employee::where('role', '!=', 'staff')
                     ->where('user_id', '!=', Auth::id())
                     ->orderBy('last_name')->get();

        $staffPersonnel = Employee::where('role', 'staff')->orderBy('last_name')->get();

        $loggedInEmployee = null;
        if (Auth::check() && Auth::user()->employee) {
            $loggedInEmployee = Auth::user()->employee;
        }

        // Map existing classes to include acknowledgedBy name for Blade display
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
         // Load the employee, the classes, and the substitute teacher for each class.
        $leaveApplication->load(['employee', 'classesToMiss.substituteTeacher']);


        
        // Pass the fully loaded leave application to the view
        return view('leave_applications.show', compact('leaveApplication'));
    }


    /**
     * Remove the specified leave application from storage.
     */
    public function destroy(LeaveApplication $leaveApplication)
    {
        // Only allow deletion if the status is 'pending'.
        if ($leaveApplication->approval_status !== 'pending') {
            return redirect()->route('leave_applications.index')
                             ->with('error', 'Only pending leave applications can be deleted.');
        }

        $leaveApplication->delete();
        return redirect()->route('leave_applications.index')->with('success', 'Leave application deleted successfully.');
    }
}