<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\User;
use App\Notifications\LeaveApplicationApprovedByAH; // Make sure this is imported
use App\Notifications\LeaveApplicationDecision; // Make sure this is imported
use Illuminate\Support\Facades\Auth;
use App\Models\Department; // Assuming Department model exists and is relevant for AH

class AcademicHeadController extends Controller
{
    /**
     * Display the Academic Head dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Ensure the user is an Academic Head and has an associated employee record
        if (!$user->hasRole('academic_head') || !$user->employee) {
            // Handle unauthorized access or missing employee data for AH
            abort(403, 'Unauthorized access or missing Academic Head profile.');
        }

        // Fetch unread notifications for the Academic Head
        $notifications = $user->unreadNotifications;

        // Fetch pending leave applications for AH review
        // Only fetch applications from employees in the AH's department
        $departmentId = $user->employee->department_id;

        $pendingApplications = LeaveApplication::where('ah_status', 'pending')
            ->whereHas('employee.department', function ($query) use ($departmentId) {
                $query->where('id', $departmentId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('academic_head.dashboard', compact('notifications', 'pendingApplications'));
    }

    /**
     * Show the form for reviewing a specific leave application.
     */
    public function reviewLeaveApplication(LeaveApplication $leaveApplication)
    {
        $user = Auth::user();

        // Authorization check: Ensure the AH can review this specific application
        // Check if the employee's department matches the AH's department
        if ($user->employee->department_id !== ($leaveApplication->employee->department_id ?? null)) {
            abort(403, 'Unauthorized action. You can only review applications from your department.');
        }

        // Ensure the application is actually pending AH review
        if ($leaveApplication->ah_status !== 'pending') {
            return redirect()->route('academic-head.dashboard')->with('error', 'This application is no longer pending your review.');
        }

        return view('academic_head.leave_applications.review', compact('leaveApplication'));
    }

    /**
     * Process the Academic Head's decision on a leave application.
     */
    public function decideLeaveApplication(Request $request, LeaveApplication $leaveApplication)
    {
        $user = Auth::user();

        // Authorization check (same as review method)
        if ($user->employee->department_id !== ($leaveApplication->employee->department_id ?? null)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'ah_status' => 'required|in:approved,rejected',
            'ah_remarks' => 'nullable|string|max:1000',
        ]);

        $leaveApplication->ah_status = $request->ah_status;
        $leaveApplication->ah_remarks = $request->ah_remarks;
        $leaveApplication->ah_approved_at = now();
        $leaveApplication->ah_approver_id = $user->id; // Assign current user's ID
        $leaveApplication->save();

        // Mark the specific notification as read if it exists
        $user->notifications()
            ->where('type', 'App\Notifications\LeaveApplicationSubmittedForAH')
            ->whereJsonContains('data->leave_application_id', $leaveApplication->id)
            ->update(['read_at' => now()]);

        // Notify HR if approved by AH
        if ($request->ah_status === 'approved') {
            $hrUsers = User::role('hr')->get();
            foreach ($hrUsers as $hrUser) {
                $hrUser->notify(new LeaveApplicationApprovedByAH($leaveApplication));
            }
        }
        // Notify employee if rejected by AH
        elseif ($request->ah_status === 'rejected') {
            $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication));
        }

        return redirect()->route('academic-head.dashboard')->with('success', 'Leave application decision recorded successfully.');
    }

    /**
     * Display all leave applications for the Academic Head's department.
     */
    public function allLeaveApplications()
    {
        $user = Auth::user();
        $departmentId = $user->employee->department_id ?? null;

        if (!$departmentId) {
            // If the AH is not associated with a department, they can't view applications.
            return redirect()->route('academic-head.dashboard')->with('error', 'You are not assigned to a department.');
        }

        $leaveApplications = LeaveApplication::whereHas('employee.department', function ($query) use ($departmentId) {
                                    $query->where('id', $departmentId);
                                })
                                ->orderBy('created_at', 'desc')
                                ->paginate(10); // Paginate for better performance on many applications

        return view('academic_head.all_leave_applications', compact('leaveApplications'));
    }
}