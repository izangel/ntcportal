<?php

namespace App\Notifications;

use App\Models\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon; // For date formatting

class LeaveApplicationDecision extends Notification implements ShouldQueue
{
    use Queueable;

    public $leaveApplication;
    public $decision; // 'approved' or 'rejected'
    public $remarks;
    public $approverRole; // Property declared

    /**
     * Create a new notification instance.
     * Add $approverRole as a parameter here!
     */
    public function __construct(LeaveApplication $leaveApplication, string $decision, string $approverRole, ?string $remarks = null) // <--- CRITICAL CHANGE HERE: ADDED $approverRole
    {
        $this->leaveApplication = $leaveApplication;
        $this->decision = $decision;
        $this->remarks = $remarks;
        $this->approverRole = $approverRole; // Now $approverRole is defined from the constructor parameter
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $statusText = ($this->decision === 'approved') ? 'Approved' : 'Rejected';
        $approverName = 'N/A';
        $decisionDate = 'N/A';
        $prefix = ''; // For the title

        // Use $this->approverRole as it's now set from the constructor
        if ($this->approverRole === 'academic_head') {
            $approverName = $this->leaveApplication->ahApprover ? $this->leaveApplication->ahApprover->name : 'Academic Head';
            $decisionDate = $this->leaveApplication->ah_approved_at ? $this->leaveApplication->ah_approved_at->format('M d, Y') : 'N/A';
            $prefix = 'Academic Head Review: ';
        } elseif ($this->approverRole === 'hr') {
            $approverName = $this->leaveApplication->hrApprover ? $this->leaveApplication->hrApprover->name : 'HR';
            $decisionDate = $this->leaveApplication->hr_approved_at ? $this->leaveApplication->hr_approved_at->format('M d, Y') : 'N/A';
            $prefix = 'HR Final Decision: ';
        } elseif ($this->approverRole === 'admin') {
            $approverName = $this->leaveApplication->adminApprover ? $this->leaveApplication->adminApprover->name : 'Admin';
            $decisionDate = $this->leaveApplication->admin_approved_at ? $this->leaveApplication->admin_approved_at->format('M d, Y') : 'N/A';
            $prefix = 'Admin Final Decision: ';
        }

        $message = "Your leave application ({$this->leaveApplication->leaveType->name}) from {$this->leaveApplication->start_date->format('M d, Y')} to {$this->leaveApplication->end_date->format('M d, Y')} has been {$statusText} by {$approverName} on {$decisionDate}.";

        if ($this->remarks) {
            $message .= " Remarks: \"{$this->remarks}\"";
        }

        $viewApplicationUrl = URL::route('dashboard'); // Default to dashboard for simplicity

        return [
            'type' => 'leave_decision',
            'title' => "{$prefix}Leave Application {$statusText}",
            'message' => $message,
            'leave_application_id' => $this->leaveApplication->id,
            'decision' => $this->decision,
            'decision_date' => $decisionDate,
            'remarks' => $this->remarks,
            'approver_role' => $this->approverRole, // Include approver role in data
            'view_application_url' => $viewApplicationUrl,
        ];
    }
}