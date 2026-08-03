<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\CourseBlock;
use App\Services\ObeDataCompleteness;

class ObeDataReminder extends Notification
{
    public $block;
    public $missing;

    public function __construct(CourseBlock $block, array $missing)
    {
        $this->block = $block;
        $this->missing = $missing;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $course = $this->block->course;
        $academicYear = $this->block->academicYear;

        $labels = collect($this->missing)
            ->map(fn ($key) => ObeDataCompleteness::labels()[$key] ?? $key)
            ->implode(', ');

        $term = $academicYear
            ? "{$academicYear->start_year}-{$academicYear->end_year} / {$this->block->semester}"
            : $this->block->semester;

        $actionUrl = route('faculty.obe.submissions');

        return [
            'type' => 'obe_data_reminder',
            'title' => "Complete OBE data for {$course->code}",
            'message' => "Your course block {$course->code} - {$course->name} ({$term}) is missing: {$labels}. Please complete the required data.",
            'course_block_id' => $this->block->id,
            'course_code' => $course->code,
            'course_name' => $course->name,
            'academic_year' => $academicYear ? "{$academicYear->start_year}-{$academicYear->end_year}" : null,
            'semester' => $this->block->semester,
            'missing' => $this->missing,
            'action_url' => $actionUrl,
        ];
    }
}
