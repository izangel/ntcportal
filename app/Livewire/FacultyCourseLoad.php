<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseBlock;
use App\Models\AcademicYear;
use App\Models\Semester;

class FacultyCourseLoad extends Component
{
    public $academicYearId;
    public $semester = '1st Semester';
    public $facultyId;
    public $academicYears = [];
    public $semesters = ['1st Semester', '2nd Semester', 'Summer'];
    public $assignedBlocks = [];

    public function mount()
    {
        if (!Auth::check() || !Auth::user()->employee) return;

        $this->facultyId = Auth::user()->employee->id;
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        // Try to get active academic year
        $activeAY = AcademicYear::getActiveAcademicYear();
        if ($activeAY) {
            $this->academicYearId = $activeAY->id;
        } elseif ($this->academicYears->isNotEmpty()) {
            $this->academicYearId = $this->academicYears->first()->id;
        }

        // Try to get active semester
        $activeSemester = Semester::getActiveSemester();
        if ($activeSemester) {
            $map = [
                'First Semester' => '1st Semester',
                'Second Semester' => '2nd Semester',
                'Summer' => 'Summer',
            ];
            $this->semester = $map[$activeSemester->name] ?? $activeSemester->name;
        }

        $this->loadAssignedBlocks();
    }

    public function loadAssignedBlocks()
    {
        if (!$this->academicYearId || !$this->semester) {
            $this->assignedBlocks = [];
            return;
        }

        $allBlocks = CourseBlock::where('faculty_id', $this->facultyId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester', $this->semester)
            ->with(['course', 'section.program'])
            ->get();

        $this->assignedBlocks = $allBlocks->groupBy(function($item) {
            return $item->course_id . '-' . $item->schedule_string;
        })->map(function($group) {
            $firstBlock = $group->first();
            $sections = $group->map(function($item) {
                return ($item->section->program->name ?? 'N/A') . '-' . ($item->section->name ?? 'N/A');
            })->unique()->sort()->implode(', ');

            return [
                'id'              => $firstBlock->id,
                'course_code'     => $firstBlock->course->code,
                'course_name'     => $firstBlock->course->name,
                'schedule_string' => $firstBlock->schedule_string,
                'sections'        => $sections,
                'finalized'       => $firstBlock->finalized,
            ];
        })->values()->toArray();
    }

    public function updatedAcademicYearId() { $this->loadAssignedBlocks(); }
    public function updatedSemester() { $this->loadAssignedBlocks(); }

    public function render()
    {
        return view('livewire.faculty-course-load')->extends('layouts.admin')->section('content');
    }
}
