<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\SectionStudent;
use App\Models\CourseBlock;

class StudentCourseBlock extends Component
{
    public $academicYears = [];
    public $selectedAcademicYearId;
    public $selectedSemester;
    public $semesters = ['1st', '2nd', 'Summer'];

    public $studentBlocks = [];

    public function mount()
    {
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

        $user = Auth::user();

        // Ensure the user is logged in and linked to a student record
        if (!$user || !$user->student) {
            return;
        }

        $studentId = $user->student->id;

        // Find the Section the student is enrolled in for the selected Academic Year and Semester
        $sectionStudent = SectionStudent::where('student_id', $studentId)
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
        // Note: You may need to change 'layouts.admin' to 'layouts.app' or 'layouts.student' depending on your student view structure.
        return view('livewire.student-course-block')
            ->extends('layouts.admin')
            ->section('content');
    }
}
