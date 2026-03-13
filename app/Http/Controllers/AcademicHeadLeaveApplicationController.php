<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\User;
use App\Notifications\LeaveApplicationSubmittedForHR;
use App\Notifications\LeaveApplicationDecision;
use App\Notifications\LeaveApplicationSubmittedForAH; // Make sure this is imported if used
use Illuminate\Support\Facades\Auth;
use App\Models\Department; // Important: Ensure you have a Department model and relationship setup

class AcademicHeadLeaveApplicationController extends Controller
{
    /**
     * Display the Academic Head leave applications list.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Authorization Check: Ensure the logged-in user has the 'academic_head' role.
        if (!$user->hasRole('academic_head')) {
            // If not an Academic Head, redirect or show an error.
            return redirect()->route('dashboard')->with('error', 'Access Denied: You do not have Academic Head privileges.');
        }

        // 2. Fetch ONLY PENDING leave applications for the AH's department.
        $pendingApplications = LeaveApplication::where('ah_status', 'pending')
                                ->orderBy('created_at', 'desc')
                                ->get();

        return view('academic_head.leave_applications.index', compact('pendingApplications'));
    }

    /**
     * Show the detailed view for a specific leave application (for review or viewing past decisions).
     * This method is accessed via signed URLs (e.g., from notifications).
     */
    public function review(LeaveApplication $leaveApplication)
    {
        $user = Auth::user();

        // 1. Authorization Check: Ensure AH has rights to view this specific application.
        if (!$user->hasRole('academic_head') || $user->employee->department_id !== ($leaveApplication->employee->department_id ?? null)) {
            abort(403, 'Unauthorized action. You can only review applications from your department.');
        }

        // You might add logic here if you only want 'pending' applications to be reviewable,
        // otherwise, this view serves for both review and general viewing.
        // Example: if ($leaveApplication->ah_status !== 'pending') { /* disable form fields */ }

        // 2. Return the review view with the leave application data.
        return view('academic_head.leave_applications.review', compact('leaveApplication'));
    }

    /**
     * Process the Academic Head's decision (approve/reject) on a leave application.
     */
    public function decide(Request $request, LeaveApplication $leaveApplication)
    {

       
        $user = Auth::user();

        // 1. Authorization Check: Same as the 'review' method.
        if (!$user->hasRole('academic_head') || $user->employee->department_id !== ($leaveApplication->employee->department_id ?? null)) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Validate the request data.
        $request->validate([
            'ah_status' => 'required|in:approved,rejected',
            'ah_remarks' => 'nullable|string|max:1000',
        ]);

        // 3. Update the leave application's status and remarks.
        $leaveApplication->ah_status = $request->ah_status;
        $leaveApplication->ah_remarks = $request->ah_remarks;
        $leaveApplication->ah_approved_at = now();
        $leaveApplication->ah_approved_by = Auth::user()->employee->id; // Record who made the decision
        $leaveApplication->save();

        // 4. Mark the relevant notification as read.
        $user->notifications()
            ->where('type', LeaveApplicationSubmittedForAH::class) // Use the Notification class path
            ->whereJsonContains('data->leave_application_id', $leaveApplication->id)
            ->update(['read_at' => now()]);

        // 5. Send follow-up notifications based on the decision.
        if ($request->ah_status === 'approved') {
            // Notify HR that the application is now approved by AH and ready for their review
            $hrUsers = User::whereHas('employee', function ($query) {
                $query->where('role', 'hr');
            })->get();

            foreach ($hrUsers as $hrUser) {
                $hrUser->notify(new LeaveApplicationSubmittedForHR($leaveApplication));
            }
        } elseif ($request->ah_status === 'rejected') {
            // Notify the employee directly about the rejection by the Academic Head
            $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication, 'rejected', 'academic_head', $request->ah_remarks));
        }

        // 6. Redirect back to the AH dashboard with a success message.
        return redirect()->route('ah.leave_applications.index')->with('success', 'Leave application decision recorded successfully.');
    }

    /**
     * NEW METHOD: Display all leave applications for the Academic Head's department.
     * This method will show all applications, regardless of their status.
     */
    public function allLeaveApplications() // New method name as per our route definition
    {
        $user = Auth::user();

        // 1. Authorization Check: Ensure the logged-in user has the 'academic_head' role.
        if (!$user->hasRole('academic_head')) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Department Check: Ensure the Academic Head is associated with a department.
        $departmentId = $user->employee->department_id ?? null;

        if (!$departmentId) {
            return redirect()->route('ah.leave_applications.index')->with('error', 'Your Academic Head profile is incomplete (missing department association).');
        }

        // 3. Fetch ALL leave applications for the AH's department.
        $leaveApplications = LeaveApplication::whereHas('employee.department', function ($query) use ($departmentId) {
                                    $query->where('id', $departmentId);
                                })
                                ->orderBy('created_at', 'desc') // Order by newest first
                                ->paginate(10); // Paginate for better performance and UI

        // 4. Return the new view with the paginated leave applications.
        return view('academic_head.all_leave_applications', compact('leaveApplications'));
    }
}