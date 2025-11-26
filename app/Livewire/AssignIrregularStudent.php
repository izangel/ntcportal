<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Student;
use App\Models\Section;
use App\Models\StudentCourse;
use Illuminate\Support\Facades\DB;

class AssignIrregularStudent extends Component
{
    public $academicYears = [];
    public $semesters = ['1st', '2nd', 'Sum'];
    public $students = [];
    public $courses = [];
    public $sections = [];

    public $selectedAcademicYearId;
    public $selectedSemester;
    public $selectedStudentId;
    public $selectedCourseId;
    public $selectedSectionId;

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        // Set default academic year if available
        if ($this->academicYears->isNotEmpty()) {
            $this->selectedAcademicYearId = $this->academicYears->first()->id;
        }
        $this->selectedSemester = '1st';
        
        $this->loadStudents();
        $this->loadCourses();
        $this->loadSections();
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->loadSections();
    }

    public function updatedSelectedSemester()
    {
        // Semester might affect available sections if sections are semester-bound, 
        // but currently Section model only has academic_year_id. 
        // If sections are reused across semesters, this might not change much, 
        // but usually they are.
    }

    public function loadStudents()
    {
        $this->students = Student::orderBy('last_name')->get();
    }

    public function loadCourses()
    {
        $this->courses = Course::orderBy('code')->get();
    }

    public function loadSections()
    {
        if ($this->selectedAcademicYearId) {
            $this->sections = Section::with('program')
                ->where(function ($query) {
                    $query->where('academic_year_id', $this->selectedAcademicYearId)
                          ->orWhereNull('academic_year_id')
                          ->orWhere('academic_year_id', '');
                })
                ->orderBy('name')
                ->get();
        } else {
            $this->sections = [];
        }
    }

    public function save()
    {
        $this->validate([
            'selectedAcademicYearId' => 'required|exists:academic_years,id',
            'selectedSemester' => 'required|in:1st,2nd,Sum',
            'selectedStudentId' => 'required|exists:students,id',
            'selectedCourseId' => 'required|exists:courses,id',
            'selectedSectionId' => 'required|exists:sections,id',
        ]);

        // Check for duplicates
        $exists = StudentCourse::where('student_id', $this->selectedStudentId)
            ->where('course_id', $this->selectedCourseId)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->where('semester', $this->selectedSemester)
            ->exists();

        if ($exists) {
            session()->flash('error', 'This student is already assigned to this course in the selected term.');
            return;
        }

        StudentCourse::create([
            'student_id' => $this->selectedStudentId,
            'course_id' => $this->selectedCourseId,
            'section_id' => $this->selectedSectionId,
            'academic_year_id' => $this->selectedAcademicYearId,
            'semester' => $this->selectedSemester,
            'validated' => false, // Default to false as per schema
        ]);

        session()->flash('success', 'Student assigned to course successfully.');
        
        return redirect()->route('assigned_courses.index');

        // Reset form fields except context
        $this->selectedStudentId = null;
        $this->selectedCourseId = null;
        $this->selectedSectionId = null;
    }

    public function render()
    {
        return view('livewire.assign-irregular-student');
    }
}
