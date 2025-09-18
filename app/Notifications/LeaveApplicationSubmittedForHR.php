<?php

namespace App\Notifications;

use App\Models\LeaveApplication;
// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class LeaveApplicationSubmittedForHR extends Notification //implements ShouldQueue
{
   // use Queueable;

    public $leaveApplication;

    /**
     * Create a new notification instance.
     */
    public function __construct(LeaveApplication $leaveApplication)
    {
        $this->leaveApplication = $leaveApplication;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // For in-app notifications
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        // The URL for HR to review this specific application
        // You'll need to create this route/controller for HR later
        $reviewUrl = URL::signedRoute('hr.leave_applications.review', [
            'leaveApplication' => $this->leaveApplication->id,
        ]);

        return [
            'type' => 'hr_leave_review',
            'title' => 'New Leave Application Submitted',
            'message' => "{$this->leaveApplication->employee->first_name} {$this->leaveApplication->employee->last_name} has submitted a leave application ({$this->leaveApplication->leaveType->name}) from {$this->leaveApplication->start_date->format('M d, Y')} to {$this->leaveApplication->end_date->format('M d, Y')}.",
            'leave_application_id' => $this->leaveApplication->id,
            'applicant_name' => $this->leaveApplication->employee->last_name,
            'leave_type_id' => $this->leaveApplication->leave_type_id,
            'start_date' => $this->leaveApplication->start_date->format('Y-m-d'),
            'end_date' => $this->leaveApplication->end_date->format('Y-m-d'),
            'review_url' => $reviewUrl,
        ];
    }
}