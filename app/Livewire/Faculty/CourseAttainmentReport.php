<?php

namespace App\Livewire\Faculty;

use App\Models\CourseAttainmentReport as CourseAttainmentReportModel;
use App\Models\CourseBlock;
use App\Services\CourseAttainmentReportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CourseAttainmentReport extends Component
{
    public $courseBlockId;

    public $report = [];

    public $actionPlans = [];

    public $status = 'draft';

    public $submittedAt = null;

    public function mount($courseBlock): void
    {
        $block = CourseBlock::with(['course', 'sections.program', 'academicYear', 'faculty'])
            ->findOrFail($courseBlock);

        if ($block->faculty_id !== Auth::user()?->employee?->id) {
            abort(403);
        }

        $this->courseBlockId = $block->id;
        $this->report = app(CourseAttainmentReportService::class)->build($block);

        $existing = CourseAttainmentReportModel::firstOrCreate(
            ['course_block_id' => $block->id],
            ['status' => 'draft']
        );

        $this->status = $existing->status;
        $this->submittedAt = $existing->submitted_at;
        $this->actionPlans = $existing->action_plans ?? [];
    }

    public function addActionPlan(): void
    {
        $this->actionPlans[] = [
            'issue' => '',
            'action' => '',
            'target_date' => '',
        ];
    }

    public function removeActionPlan(int $index): void
    {
        unset($this->actionPlans[$index]);
        $this->actionPlans = array_values($this->actionPlans);
    }

    public function saveDraft(): void
    {
        $this->persist('draft');
        session()->flash('message', 'Course Attainment Report saved as draft.');
    }

    public function submitReport(): void
    {
        $this->persist('submitted');
        session()->flash('message', 'Course Attainment Report submitted for review.');
    }

    private function persist(string $status): void
    {
        $this->validate([
            'actionPlans.*.issue' => 'nullable|string|max:1000',
            'actionPlans.*.action' => 'nullable|string|max:1000',
            'actionPlans.*.target_date' => 'nullable|date',
        ]);

        CourseAttainmentReportModel::updateOrCreate(
            ['course_block_id' => $this->courseBlockId],
            [
                'status' => $status,
                'action_plans' => $this->actionPlans,
                'submitted_at' => $status === 'submitted' ? now() : null,
            ]
        );

        $this->status = $status;
        $this->submittedAt = $status === 'submitted' ? now() : null;
    }

    public function render()
    {
        return view('livewire.faculty.course-attainment-report')->extends('layouts.admin')->section('content');
    }
}
