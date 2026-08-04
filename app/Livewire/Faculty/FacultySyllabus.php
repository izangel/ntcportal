<?php

namespace App\Livewire\Faculty;

use App\Models\AcademicYear;
use App\Models\CourseBlock;
use App\Models\CourseSyllabus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FacultySyllabus extends Component
{
    public $academicYearId = null;
    public $semester = '1st';

    public $assignedBlocks = [];

    public function mount(): void
    {
        $this->academicYearId = AcademicYear::orderByDesc('start_year')->value('id');
        $this->loadBlocks();
    }

    public function updatedAcademicYearId(): void
    {
        $this->loadBlocks();
    }

    public function updatedSemester(): void
    {
        $this->loadBlocks();
    }

    private function facultyId(): ?int
    {
        return Auth::user()?->employee?->id;
    }

    private function loadBlocks(): void
    {
        if (!$this->facultyId() || !$this->academicYearId) {
            $this->assignedBlocks = [];
            return;
        }

        $allBlocks = CourseBlock::where('faculty_id', $this->facultyId())
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester', $this->semester)
            ->with(['course', 'section.program', 'academicYear', 'syllabus'])
            ->get();

        $this->assignedBlocks = $allBlocks
            ->groupBy(fn ($block) => $block->course_id . '-' . $block->schedule_string)
            ->map(function ($group) {
                $firstBlock = $group->first();

                $sections = $group->map(function ($block) {
                    $program = $block->section->program->name ?? 'N/A';
                    $section = $block->section->name ?? 'N/A';
                    return "{$program}-{$section}";
                })->unique()->sort()->implode(', ');

                $syllabus = CourseSyllabus::where('course_block_id', $firstBlock->id)->first();

                return [
                    'id' => $firstBlock->id,
                    'course_code' => $firstBlock->course->code,
                    'course_name' => $firstBlock->course->name,
                    'course_units' => $firstBlock->course->units,
                    'schedule_string' => $firstBlock->schedule_string,
                    'sections' => $sections,
                    'has_syllabus' => $syllabus !== null,
                    'has_learning_plan' => $syllabus ? $syllabus->learningPlanItems()->exists() : false,
                ];
            })
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.faculty.faculty-syllabus', [
            'academicYears' => AcademicYear::orderByDesc('start_year')->get(),
            'assignedBlocks' => $this->assignedBlocks,
        ])->extends('layouts.admin')->section('content');
    }
}
