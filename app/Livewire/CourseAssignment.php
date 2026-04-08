<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Section;
use App\Models\Student;
use App\Models\CourseBlock;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class CourseAssignment extends Component
{
    public $selectedYear = null;
    public $selectedSemester = null;
    public $selectedSection = null;
    public $selectedBlocks = [];

    #[Computed]
    public function academicYears()
    {
        return AcademicYear::all();
    }

    #[Computed]
    public function semesters()
    {
        return Semester::where('academic_year_id', $this->selectedYear)->get();
    }

    #[Computed]
    public function sections()
    {
        return Section::where('academic_year_id', $this->selectedYear)->get();
    }

    #[Computed]
    public function students()
    {
        if (!$this->selectedSection) return collect();

        // Fetches students based on your section_student pivot table
        return Student::whereHas('sections', function($query) {
            $query->where('sections.id', $this->selectedSection);
        })->get();
    }

    #[Computed]
    public function courseBlocks()
    {
        if (!$this->selectedYear || !$this->selectedSemester) return collect();

        return CourseBlock::where('academic_year_id', $this->selectedYear)
            ->where('semester', $this->selectedSemester)
            ->get();
    }

    public function render()
    {
        return view('livewire.course-assignment');
    }

    public function getStudents()
    {
        if (!$this->selectedSection) return [];
        
        return Student::whereHas('sections', function($query) {
            $query->where('sections.id', $this->selectedSection);
        })->get();
    }

    public function assignCourses()
    {
        $students = $this->getStudents();
        
        DB::transaction(function () use ($students) {
            foreach ($students as $student) {
                // syncWithoutDetaching prevents duplicate entries if button is clicked twice
                $student->courseBlocks()->syncWithoutDetaching($this->selectedBlocks);
            }
        });

        session()->flash('message', 'Courses successfully assigned to section students.');
    }
}