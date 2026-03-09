<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\SectionStudent;
use App\Models\CourseBlock;

class StudentScheduleView extends Component
{
    public $student;
    public $academicYears = [];
    public $selectedAcademicYearId;
    public $selectedSemester;
    public $semesters = ['1st', '2nd', 'Summer'];

    public $studentBlocks = [];

    public function mount(Student $student)
    {
        $this->student = $student;
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        // Default to the latest Academic Year and 1st Semester
        $this->selectedAcademicYearId = $this->academicYears->first()->id ?? null;
        $this->selectedSemester = '1st';

        $this->loadBlocks();
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->loadBlocks();
    }

    public function updatedSelectedSemester()
    {
        $this->loadBlocks();
    }

    public function loadBlocks()
    {
        $this->studentBlocks = [];

        // Find the Section the student is enrolled in for the selected Academic Year and Semester
        $sectionStudent = SectionStudent::where('student_id', $this->student->id)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->where('semester', $this->selectedSemester)
            ->first();

        if ($sectionStudent) {
            // Fetch Course Blocks assigned to that Section
            $this->studentBlocks = CourseBlock::where('section_id', $sectionStudent->section_id)
                ->where('academic_year_id', $this->selectedAcademicYearId)
                ->where('semester', $this->selectedSemester)
                ->with(['course', 'faculty'])
                ->orderBy('schedule_string')
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.admin.student-schedule-view')
            ->extends('layouts.admin')
            ->section('content');
    }
}
