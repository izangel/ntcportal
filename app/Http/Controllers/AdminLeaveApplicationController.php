<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Notifications\LeaveApplicationDecision; // We'll create this next
use Illuminate\Notifications\DatabaseNotification;



class AdminLeaveApplicationController extends Controller
{
   // Optional: Middleware to ensure only HR can access
    public function __construct()
    {
        // You'll need to define a gate or policy for 'is_hr'
        // For now, this is a placeholder. You might use Laravel Spatie Permissions.
        // $this->middleware('can:manage_leave_applications');
    }

    /**
     * Show all pending leave applications for Admin review.
     */
    public function index()
    {
        $pendingApplications = LeaveApplication::where('admin_status', 'pending')
                                                ->where('hr_status', 'approved')
                                                ->with(['employee', 'classesToMiss'])
                                                ->orderBy('created_at', 'asc')
                                                ->get();

        return view('admin.leave_applications.index', compact('pendingApplications'));
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
        /** @var \App\Models\Employee $employee */
       $employee = Auth::user()->employee;

      $remainingCredits = $employee->getRemainingLeaveCredits();
      $message = empty($remainingCredits)
      ? 'No leave credits found for this employee. Please contact HR.'
      : null;

return view('admin.leave_applications.review', compact('leaveApplication', 'remainingCredits', 'message'));
    }

    /**
     * Process HR decision (approve/reject).
     */
    public function decide(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate([
            'decision' => ['required', 'in:approved_with_pay,approved_without_pay,rejected'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $decision = $request->input('decision');
        $remarks = $request->input('remarks');
        $approvedBy = Auth::user()->employee->name;

        // Prevent re-deciding already processed applications
        if ($leaveApplication->admin_status !== 'pending') {
            return redirect()->back()->with('error', 'This leave application has already been processed by Admin.');
        }

        // Update application status
        $leaveApplication->admin_status = $decision;
        $leaveApplication->admin_approved_at = Carbon::now();
        $leaveApplication->admin_approved_by = Auth::user()->employee->id; // Assuming Admin is logged in
        $leaveApplication->admin_remarks = $remarks;
        $leaveApplication->approval_status = $decision;
        $leaveApplication->save();

       
       

        // ----------------------------------------------------------------------
        // NEW: Mark the Admin Manager's notification for this leave application as read
        $notification = Auth::user()->unreadNotifications()
                            ->where('type', 'App\Notifications\LeaveApplicationSubmittedForAdmin') // Admin notification type
                            ->whereJsonContains('data->leave_application_id', $leaveApplication->id)
                            ->first();

        if ($notification) {
            $notification->markAsRead();
        }
        // ----------------------------------------------------------------------


        // Mark Admin notification as read
        if (Auth::user()->unreadNotifications->where('data.leave_application_id', $leaveApplication->id)->first()) {
            Auth::user()->unreadNotifications->where('data.leave_application_id', $leaveApplication->id)->first()->markAsRead();
        }

        // --- Notify the original employee about the HR decision ---
        $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication, $decision, $approvedBy, $remarks));

        return redirect()->route('admin.leave_applications.index')->with('success', "Leave application {$decision} successfully.");
    }

}
