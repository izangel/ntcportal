<?php

namespace App\Notifications;

use App\Models\LeaveApplicationClass;
// use Illuminate\Bus\Queueable; 
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class SubstituteTeacherAssignment extends Notification // implements ShouldQueue
{
    //use Queueable;

    public $leaveApplicationClass;
    public $leavingTeacherName;

    /**
     * Create a new notification instance.
     */
    public function __construct(LeaveApplicationClass $leaveApplicationClass, string $leavingTeacherName)
    {
        $this->leaveApplicationClass = $leaveApplicationClass;
        $this->leavingTeacherName = $leavingTeacherName;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array // If this line still errors, your PHP environment is problematic.
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification (for database storage).
     */
    public function toArray(object $notifiable): array
    {
        $leave = $this->leaveApplicationClass->leaveApplication;
        $courseCode = $this->leaveApplicationClass->course_code;
        $title = $this->leaveApplicationClass->title;
        $dayTimeRoom = $this->leaveApplicationClass->day_time_room;
        $topics = $this->leaveApplicationClass->topics;

        // --- FIX FOR OLDER PHP SYNTAX HERE (if '?:' is truly causing issue) ---
        $topicsDisplay = '';
        if (!empty($topics)) { // Use !empty() instead of checking for null specifically if topics might be empty string
            $topicsDisplay = $topics;
        } else {
            $topicsDisplay = 'N/A';
        }
        // --- END FIX ---

        // $acknowledgementUrl = URL::signedRoute('substitute.acknowledgement.review', [
        //     'classId' => $this->leaveApplicationClass->id,
        // ]);

          // Generate a secure, signed URL for the acknowledgment link
        $acknowledgementUrl = URL::signedRoute('substitute.acknowledge', [
            'classId' => $this->leaveApplicationClass->id,
        ]);
      

        return [
            'type' => 'substitute_assignment',
            'title' => "New Substitute Assignment: {$courseCode}",
            'message' => "You've been assigned to substitute for {$this->leavingTeacherName} for: {$courseCode} - {$title} on {$dayTimeRoom}. Topics: {$topicsDisplay}.", // Use $topicsDisplay
            'leaving_teacher_name' => $this->leavingTeacherName,
            'leave_application_class_id' => $this->leaveApplicationClass->id,
            'course_code' => $courseCode,
            'title' => $title,
            'day_time_room' => $dayTimeRoom,
            'topics' => $topics, // Still store original topics
            'acknowledgement_url' => $acknowledgementUrl,
            'application_start_date' => $leave->start_date->format('Y-m-d'),
            'application_end_date' => $leave->end_date->format('Y-m-d'),
        ];
    }

   
}