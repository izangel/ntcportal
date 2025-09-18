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

        
            
        
            // Fetch all leave applications with their associated employee data
            $leaveApplications = LeaveApplication::with('employee')
                                                ->orderBy('date_filed', 'desc')
                                            
                                            ->paginate(10); // Paginate for large datasets
           

        

        
        // For filter dropdowns (optional)
        $employees = Employee::orderBy('name')->get();
        $leaveTypes = ['service_incentive_leave', 'sick_leave', 'vacation_leave', 'other'];
        $approvalStatuses = ['pending', 'approved_with_pay', 'approved_without_pay', 'rejected']; // Basic statuses

        return view('leave_applications.all', compact('leaveApplications', 'employees','leaveTypes', 'approvalStatuses'));
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
                        $tname = $leaveApplication->employee->last_name.' '.$leaveApplication->employee->first_name.' '.$leaveApplication->employee->mid_name;
                        $substituteTeacher->user->notify(new SubstituteTeacherAssignment(
                            $leaveApplicationClass,
                            $tname // The name of the teacher taking leave
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
                         $tname = $leaveApplication->employee->last_name.' '.$leaveApplication->employee->first_name.' '.$leaveApplication->employee->mid_name;
                         $substituteTeacher->user->notify(new SubstituteTeacherAssignment(
                            $currentClass,
                            $tname
                        ));
                    }
                }
            }
        }

        return redirect()->route('leave_applications.index')->with('success', 'Leave application updated successfully. Substitute teachers received in-app assignments.');
    }

    // 2
    // public function update(Request $request, LeaveApplication $leaveApplication)
    // {
    //     $rules = [
    //         'employee_id' => 'required|exists:employees,id',
    //         'leave_type' => 'required|string|in:service_incentive_leave,sick_leave,vacation_leave,other',
    //         'reason' => 'required|string|max:1000',
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //         'tasks_endorsed' => 'nullable|string|max:1000',
    //         'personnel_to_take_over_id' => 'nullable|exists:employees,id',
    //         'acknowledgement_personnel_take_over_signature' => 'nullable|string|max:255',
    //     ];

    //     // Validation for individual class rows
    //     $rules['classes_data'] = 'nullable|array';
    //     $rules['classes_data.*.id'] = 'nullable|exists:leave_application_classes,id'; // For existing rows to update
    //     $rules['classes_data.*.course_code'] = 'nullable|string|max:255';
    //     $rules['classes_data.*.title'] = 'nullable|string|max:255';
    //     $rules['classes_data.*.day_time_room'] = 'nullable|string|max:255';
    //     $rules['classes_data.*.topics'] = 'nullable|string|max:1000';
    //     $rules['classes_data.*.substitute_teacher_id'] = 'nullable|exists:employees,id';
    //     $rules['classes_data.*.acknowledgement_signature'] = 'nullable|string|max:255';

    //     $validatedData = $request->validate($rules);

    //     $startDate = Carbon::parse($validatedData['start_date']);
    //     $endDate = Carbon::parse($validatedData['end_date']);
    //     $validatedData['total_days'] = $startDate->diffInDays($endDate) + 1;

    //     // Separate classes_data from main validatedData
    //     $classesToProcess = $validatedData['classes_data'] ?? [];
    //     unset($validatedData['classes_data']);

    //     $leaveApplication->update($validatedData);

    //     // --- Syncing Classes ---
    //     // This is a common pattern for "hasMany" relationships:
    //     // 1. Get IDs of existing classes for this leave application.
    //     // 2. Determine which existing classes are NOT in the new submission (to delete).
    //     // 3. Loop through submitted classes: update existing, create new.

    //     $existingClassIds = $leaveApplication->classesToMiss->pluck('id')->toArray();
    //     $submittedClassIds = collect($classesToProcess)->pluck('id')->filter()->toArray(); // Filter out null/empty IDs

    //     // Classes to delete (existing IDs not in submitted IDs)
    //     $classesToDelete = array_diff($existingClassIds, $submittedClassIds);
    //     LeaveApplicationClass::destroy($classesToDelete); // Delete all at once

    //     foreach ($classesToProcess as $classData) {
    //         // Remove empty rows on update as well
    //         if (array_filter($classData, function($value) { return $value !== null && $value !== ''; })) { // Check for non-empty values
    //             if (isset($classData['id']) && in_array($classData['id'], $existingClassIds)) {
    //                 // This is an existing class, update it
    //                 $class = LeaveApplicationClass::find($classData['id']);
    //                 if ($class) {
    //                     $class->update($classData);
    //                 }
    //             } else {
    //                 // This is a new class row (no ID or not an existing one), create it
    //                 $leaveApplication->classesToMiss()->create($classData);
    //             }
    //         } else if (isset($classData['id']) && in_array($classData['id'], $existingClassIds)) {
    //             // If a row was previously filled but now all fields are empty, delete it
    //             LeaveApplicationClass::destroy($classData['id']);
    //         }
    //     }

    //     return redirect()->route('leave_applications.index')->with('success', 'Leave application updated successfully.');
    // }

//    public function store(Request $request)
//     {
//         $rules = [
//             'employee_id' => 'required|exists:employees,id',
//             'leave_type' => 'required|string|in:service_incentive_leave,sick_leave,vacation_leave,other',
//             'reason' => 'required|string|max:1000',
//             'start_date' => 'required|date',
//             'end_date' => 'required|date|after_or_equal:start_date',
//             'tasks_endorsed' => 'nullable|string|max:1000',
//             'personnel_to_take_over_id' => 'nullable|exists:employees,id',
//             'acknowledgement_personnel_take_over_signature' => 'nullable|string|max:255',
//         ];

//         if ($request->has('classes_to_miss')) {
//             $rules['classes_to_miss'] = 'array';
//             $rules['classes_to_miss.*.course_code'] = 'nullable|string|max:255';
//             $rules['classes_to_miss.*.title'] = 'nullable|string|max:255';
//             $rules['classes_to_miss.*.day_time_room'] = 'nullable|string|max:255';
//             $rules['classes_to_miss.*.topics'] = 'nullable|string|max:1000';
//             $rules['classes_to_miss.*.substitute_teacher_id'] = 'nullable|exists:employees,id';
//             $rules['classes_to_miss.*.acknowledgement_signature'] = 'nullable|string|max:255';
//         }

//         $validatedData = $request->validate($rules);

//         // --- NEW: Calculate total_days ---
//         $startDate = Carbon::parse($validatedData['start_date']);
//         $endDate = Carbon::parse($validatedData['end_date']);
//         // +1 because Carbon's diffInDays counts the number of 24-hour periods.
//         // If start and end are the same day, diffInDays is 0, but it's 1 day of leave.
//         $validatedData['total_days'] = $startDate->diffInDays($endDate) + 1;
//         // --- END NEW ---

//         if (isset($validatedData['classes_to_miss'])) {
//             $validatedData['classes_to_miss'] = json_encode($validatedData['classes_to_miss']);
//         } else {
//             $validatedData['classes_to_miss'] = null;
//         }

//         // Set date_filed to now if it's not handled elsewhere
//         $validatedData['date_filed'] = Carbon::now();

//         // Set initial status if not handled elsewhere (e.g., 'pending')
//         if (!isset($validatedData['approval_status'])) {
//             $validatedData['approval_status'] = 'pending';
//         }

//         $leaveApplication = LeaveApplication::create($validatedData);

//         return redirect()->route('leave_applications.index')->with('success', 'Leave application submitted successfully.');
//     }


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

//    public function edit(LeaveApplication $leaveApplication)
//     {
//         $leaveApplication->load('employee'); // Eager load employee for the form

//         $employees = Employee::orderBy('name')->get();
//         $teachers = Employee::where('role', 'teacher')->orderBy('name')->get();
//         $staffPersonnel = Employee::where('role', 'staff')->orderBy('name')->get();

//         $loggedInEmployee = null; // For edit, this might not be strictly needed, but keep for consistency
//         if (Auth::check() && Auth::user()->employee) {
//             $loggedInEmployee = Auth::user()->employee;
//         }

//         return view('leave_applications.edit', compact('leaveApplication', 'employees', 'loggedInEmployee', 'teachers', 'staffPersonnel'));
//     }

    // public function update(Request $request, LeaveApplication $leaveApplication)
    // {
    //     $rules = [
    //         'employee_id' => 'required|exists:employees,id',
    //         'leave_type' => 'required|string|in:service_incentive_leave,sick_leave,vacation_leave,other',
    //         'reason' => 'required|string|max:1000',
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //         // 'status' => 'required|string|in:pending,approved_with_pay,approved_without_pay,rejected', // Assuming status is handled by admin
    //         'tasks_endorsed' => 'nullable|string|max:1000',
    //         'personnel_to_take_over_id' => 'nullable|exists:employees,id',
    //         'acknowledgement_personnel_take_over_signature' => 'nullable|string|max:255',
    //         // approval_status, academic_head_noted_at, etc. are likely managed by a separate admin process,
    //         // so they typically wouldn't be in the update rules for the user submission.
    //     ];

    //     if ($request->has('classes_to_miss')) {
    //         $rules['classes_to_miss'] = 'array';
    //         $rules['classes_to_miss.*.course_code'] = 'nullable|string|max:255';
    //         $rules['classes_to_miss.*.title'] = 'nullable|string|max:255';
    //         $rules['classes_to_miss.*.day_time_room'] = 'nullable|string|max:255';
    //         $rules['classes_to_miss.*.topics'] = 'nullable|string|max:1000';
    //         $rules['classes_to_miss.*.substitute_teacher_id'] = 'nullable|exists:employees,id';
    //         $rules['classes_to_miss.*.acknowledgement_signature'] = 'nullable|string|max:255';
    //     }

    //     $validatedData = $request->validate($rules);

    //     // --- NEW: Calculate total_days for update ---
    //     $startDate = Carbon::parse($validatedData['start_date']);
    //     $endDate = Carbon::parse($validatedData['end_date']);
    //     $validatedData['total_days'] = $startDate->diffInDays($endDate) + 1;
    //     // --- END NEW ---

    //     if (isset($validatedData['classes_to_miss'])) {
    //         $validatedData['classes_to_miss'] = json_encode($validatedData['classes_to_miss']);
    //     } else {
    //         $validatedData['classes_to_miss'] = null;
    //     }

    //     $leaveApplication->update($validatedData);

    //     return redirect()->route('leave_applications.index')->with('success', 'Leave application updated successfully.');
    // }
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