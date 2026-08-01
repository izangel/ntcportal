<?php

namespace App\Livewire\ClassManagement;

use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\CourseBlock;

trait InteractsWithClassSelection
{
    public $academicYearId;
    public $semester = '1st';
    public $selectedBlockId;

    public $facultyId;
    public $academicYears = [];
    public $semesterOptions = ['1st', '2nd Semester', 'Summer'];
    public $assignedBlocks = [];

    public function mountInteractsWithClassSelection()
    {
        $user = Auth::user();
        $this->facultyId = $user->employee?->id;

        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        $latestWithBlocks = $this->academicYears
            ->filter(fn ($year) => CourseBlock::where('faculty_id', $this->facultyId)
                ->where('academic_year_id', $year->id)
                ->exists())
            ->first();

        $this->academicYearId = ($latestWithBlocks ?: $this->academicYears->first())->id;

        $this->loadAssignedBlocks();
    }

    public function updatedAcademicYearId()
    {
        $this->loadAssignedBlocks();
        $this->resetSelection();
    }

    public function updatedSemester()
    {
        $this->loadAssignedBlocks();
        $this->resetSelection();
    }

    public function updatedSelectedBlockId()
    {
        $this->loadBlockData();
    }

    public function resetSelection()
    {
        $this->selectedBlockId = null;
        $this->resetBlockData();
    }

    public function resetBlockData()
    {
    }

    public function loadBlockData()
    {
    }

    public function loadAssignedBlocks()
    {
        $this->assignedBlocks = [];

        if (!$this->facultyId || !$this->academicYearId || !$this->semester) {
            return;
        }

        $blocks = CourseBlock::where('faculty_id', $this->facultyId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester', $this->semester)
            ->with(['course', 'section.program', 'academicYear'])
            ->get();

        $this->assignedBlocks = $blocks
            ->groupBy(fn ($block) => $block->course_id . '-' . $block->schedule_string)
            ->map(function ($group) {
                $first = $group->first();
                $sections = $group->map(fn ($b) => ($b->section->program->name ?? 'N/A') . '-' . ($b->section->name ?? 'N/A'))
                    ->unique()
                    ->sort()
                    ->implode(', ');

                return [
                    'id' => $first->id,
                    'course_code' => $first->course->code,
                    'course_name' => $first->course->name,
                    'schedule_string' => $first->schedule_string,
                    'room_name' => $first->room_name,
                    'sections' => $sections,
                    'student_count' => $first->students()->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function verifyOwnership(): bool
    {
        return (bool) CourseBlock::whereKey((int) $this->selectedBlockId)
            ->where('faculty_id', $this->facultyId)
            ->exists();
    }

    protected function currentBlock(): ?CourseBlock
    {
        if (!$this->selectedBlockId || !$this->verifyOwnership()) {
            return null;
        }

        return CourseBlock::with(['course', 'section', 'section.program', 'academicYear', 'faculty'])
            ->find((int) $this->selectedBlockId);
    }
}
