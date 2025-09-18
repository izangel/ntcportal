<?php

namespace App\Notifications;

use App\Models\LeaveApplication;
// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class LeaveApplicationSubmittedForAH extends Notification //implements ShouldQueue
{
    //use Queueable;

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
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        // This must match the route name 'ah.leave_applications.review'
        // and the parameter name 'leaveApplication'
        $reviewUrl = URL::signedRoute('ah.leave_applications.review', [
            'leaveApplication' => $this->leaveApplication->id,
        ]);

        return [
            'type' => 'ah_leave_review', // This type must match what's in dashboard.blade.php
            'title' => 'New Leave Application for Academic Head Review',
            'message' => "{$this->leaveApplication->employee->first_name} {$this->leaveApplication->employee->last_name} has submitted a leave application ({$this->leaveApplication->leaveType->name}) from {$this->leaveApplication->start_date->format('M d, Y')} to {$this->leaveApplication->end_date->format('M d, Y')}.",
            'leave_application_id' => $this->leaveApplication->id,
            'applicant_name' => $this->leaveApplication->employee->last_name,
            'leave_type_id' => $this->leaveApplication->leave_type_id,
            'start_date' => $this->leaveApplication->start_date->format('Y-m-d'),
            'end_date' => $this->leaveApplication->end_date->format('Y-m-d'),
            'review_url' => $reviewUrl, // This is the key used in dashboard.blade.php
        ];
    }
}