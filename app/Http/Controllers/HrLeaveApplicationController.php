<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Notifications\LeaveApplicationDecision; // We'll create this next
use Illuminate\Notifications\DatabaseNotification;
use App\Notifications\LeaveApplicationSubmittedForAdmin;


class HrLeaveApplicationController extends Controller
{
    // Optional: Middleware to ensure only HR can access
    public function __construct()
    {
        // You'll need to define a gate or policy for 'is_hr'
        // For now, this is a placeholder. You might use Laravel Spatie Permissions.
        // $this->middleware('can:manage_leave_applications');
    }

    /**
     * Show all pending leave applications for HR review.
     */
    public function index()
    {
        $pendingApplications = LeaveApplication::where('hr_status', 'pending')
                                                ->with(['employee', 'classesToMiss'])
                                                ->orderBy('created_at', 'asc')
                                                ->get();

        return view('hr.leave_applications.index', compact('pendingApplications'));
    }

    /**
     * Show details of a specific leave application for review.
     */
    public function review(Request $request, LeaveApplication $leaveApplication)
    {
        // Verify signed URL if coming from notification (though index might be direct)
        // if (!URL::hasValidSignature($request)) { // Only if accessed via signed URL
        //     abort(403, 'Invalid or expired review link.');
        // }

        // Load related data
        $leaveApplication->load(['employee', 'classesToMiss.substituteTeacher']);

        return view('hr.leave_applications.review', compact('leaveApplication'));
    }

    /**
     * Process HR decision (approve/reject).
     */
    public function decide(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $decision = $request->input('decision');
        $remarks = $request->input('remarks');
        $approvedBy = Auth::user()->employee->name;

        // Prevent re-deciding already processed applications
        if ($leaveApplication->hr_status !== 'pending') {
            return redirect()->back()->with('error', 'This leave application has already been processed by HR.');
        }

        // Update application status
        $leaveApplication->hr_status = $decision;
        $leaveApplication->hr_approved_at = Carbon::now();
        $leaveApplication->hr_approved_by = Auth::user()->employee->id; // Assuming HR is logged in
        $leaveApplication->hr_remarks = $remarks;
        $leaveApplication->save();

        // ----------------------------------------------------------------------
        // NEW: Mark the HR Manager's notification for this leave application as read
        $notification = Auth::user()->unreadNotifications()
                            ->where('type', 'App\Notifications\LeaveApplicationSubmittedForHR') // HR notification type
                            ->whereJsonContains('data->leave_application_id', $leaveApplication->id)
                            ->first();

        if ($notification) {
            $notification->markAsRead();
        }
        // ----------------------------------------------------------------------


        // Mark HR notification as read
        if (Auth::user()->unreadNotifications->where('data.leave_application_id', $leaveApplication->id)->first()) {
            Auth::user()->unreadNotifications->where('data.leave_application_id', $leaveApplication->id)->first()->markAsRead();
        }

        
        //Notify Admin
        if ($decision === 'approved') {
           
            // Notify Admin that the application is now approved by HR and ready for their review
            $adminUsers = User::whereHas('employee', function ($query) {
                $query->where('role', 'admin');
            })->get();
            
         
            foreach ($adminUsers as $adminUser) {
                $adminUser->notify(new LeaveApplicationSubmittedForAdmin($leaveApplication));
            }
        } 

        // --- Notify the original employee about the HR decision ---
        $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication, $decision, $approvedBy, $remarks));

        return redirect()->route('hr.leave_applications.index')->with('success', "Leave application {$decision} successfully.");
    }
}