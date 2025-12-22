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

    public function store(Request $request)
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            //'leave_type' => 'required|string|in:service_incentive_leave,sick_leave,vacation_leave,other',
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

       
       // Loop through and save each class, and store notifications
        foreach ($classesToSave as $classData) {
            if (array_filter($classData)) { // Only save if at least one field is provided
                $leaveApplicationClass = $leaveApplication->classesToMiss()->create($classData);

                // Store notification for substitute teacher if assigned
                if ($leaveApplicationClass->substitute_teacher_id) {
                    $substituteTeacher = $leaveApplicationClass->substituteTeacher;
                   
                    // Ensure the substitute teacher has a related user model to receive notifications
                    if ($substituteTeacher && $substituteTeacher->user) {
                        $substituteTeacher->user->notify(new SubstituteTeacherAssignment(
                            $leaveApplicationClass,
                            $leaveApplication->employee->name // The name of the teacher taking leave
                        ));
                    }
                }
            }
        }

        //check if teacher
        
        $user = Auth::user();
        if ($user->hasRole('staff')){
            //notify hr

          

             // --- NEW: Notify HR Heads about the new leave application ---
            $hrHeads = User::whereHas('employee', function ($query) {
                $query->where('role', 'hr');
            })->get();

            
            foreach ($hrHeads as $hrUser) {
                $hrUser->notify(new LeaveApplicationSubmittedForHR($leaveApplication));
            }
            // --- END NEW ---

        } else {

           

            // --- NEW: Notify Academic Heads about the new leave application ---
            $academicHeads = User::whereHas('employee', function ($query) {
                $query->where('role', 'academic_head');
            })->get();

            
            foreach ($academicHeads as $ahUser) {
                $ahUser->notify(new LeaveApplicationSubmittedForAH($leaveApplication));
            }
            // --- END NEW ---
        }
        


        return redirect()->route('leave_applications.index')->with('success', 'Leave application submitted successfully. Substitute teachers received in-app assignments.');
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
           // 'leave_type' => 'required|string|in:service_incentive_leave,sick_leave,vacation_leave,other',
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
            // Check if any significant data is present to consider it a "valid" row
            $isDataPresent = array_filter($classData, function($value, $key) {
                // Exclude 'id' from the check, and check for non-empty/non-null values
                return $key !== 'id' && ($value !== null && $value !== '');
            }, ARRAY_FILTER_USE_BOTH);

            if (isset($classData['id']) && in_array($classData['id'], $existingClassIds)) {
                // This is an existing class.
                $currentClass = LeaveApplicationClass::find($classData['id']);
                if ($currentClass) {
                    // If already acknowledged, prevent changes to substitute or signature
                    if ($currentClass->sub_ack_at) { // Using new shorter column name
                        unset($classData['substitute_teacher_id']);
                        unset($classData['acknowledgement_signature']);
                        // You might want to unset other fields too if they should be immutable after ack
                    }
                    $currentClass->update($classData);
                }
            } else if ($isDataPresent) { // This is a new class row
                $currentClass = $leaveApplication->classesToMiss()->create($classData);
            } else if (isset($classData['id']) && !$isDataPresent) {
                // If an existing row is now completely empty, delete it
                LeaveApplicationClass::destroy($classData['id']);
            }

            // Store notification for NEW or CHANGED (unacknowledged) substitute assignments
            // Only if a substitute_teacher_id is set
            if ($currentClass && $currentClass->substitute_teacher_id) {
                $shouldNotify = false;
                if ($currentClass->wasRecentlyCreated) {
                    $shouldNotify = true; // New class created with a substitute
                } elseif ($currentClass->isDirty('substitute_teacher_id') && !$currentClass->sub_ack_at) {
                    $shouldNotify = true; // Substitute changed and not yet acknowledged
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

        return redirect()->route('leave_applications.index')->with('success', 'Leave application updated successfully. Substitute teachers received in-app assignments.');
    }

   

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