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

    private function semesterVariants(): array
    {
        return match ($this->semester) {
            '1st' => ['1st', 'first', '1st semester', 'first semester', 'semester 1', 'sem 1', '1st sem', '1'],
            '2nd' => ['2nd', 'second', '2nd semester', 'second semester', 'semester 2', 'sem 2', '2nd sem', '2'],
            default => ['summer', 'summer term', '3rd', 'third', '3rd semester', 'third semester', 'semester 3', 'sem 3', '3'],
        };
    }

    private function loadBlocks(): void
    {
        if (!$this->facultyId() || !$this->academicYearId) {
            $this->assignedBlocks = [];
            return;
        }

        $allBlocks = CourseBlock::where('faculty_id', $this->facultyId())
            ->where('academic_year_id', $this->academicYearId)
            ->whereIn(\DB::raw('LOWER(TRIM(semester))'), $this->semesterVariants())
            ->with(['course', 'sections.program', 'academicYear', 'syllabus'])
            ->get();

        $this->assignedBlocks = $allBlocks
            ->groupBy(fn ($block) => $block->course_id . '-' . $block->schedule_string)
            ->map(function ($group) {
                $firstBlock = $group->first();

                $sections = $group->map(function ($block) {
                    $blockSections = $block->sections()->get();

                    if ($blockSections->isEmpty() && $block->section_id) {
                        $blockSections = collect([$block->section]);
                    }

                    return $blockSections->map(function ($section) {
                        $program = $section->program->name ?? '';
                        return $program ? "{$program}-{$section->name}" : ($section->name ?? '');
                    });
                })->flatten()->unique()->sort()->implode(', ');

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
