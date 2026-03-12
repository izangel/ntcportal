<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\{Student, CourseBlock, AcademicYear, Section, SectionStudent};
use Illuminate\Support\Facades\DB;

class AssignStudentCourseBlock extends Component
{
    public $academic_year_id;
    public $semester;
    public $course_block_id;
    public $target_section_id;

public function render()
{
    return view('livewire.assign-student-course-block', [
        'academicYears' => \App\Models\AcademicYear::all(),
        'semesters'     => ['1st Semester', '2nd Semester', 'Summer'],
        
        // Eager load course and faculty to match your "Course Blocks" table
        'courseBlocks'  => \App\Models\CourseBlock::with(['course', 'faculty'])->get(),
        
        'sections'      => \App\Models\Section::all(),
        'students'      => $this->getStudents(),
    ])->extends('layouts.admin')
            ->section('content');
}

public function getStudents()
{
    // The list only displays AFTER a section is selected
    if (!$this->target_section_id || !$this->academic_year_id || !$this->semester) {
        return collect();
    }

    // Fetch only students belonging to the selected section for this term
    return Student::whereHas('sections', function($query) {
        $query->where('section_student.section_id', $this->target_section_id)
              ->where('section_student.academic_year_id', $this->academic_year_id)
              ->where('section_student.semester', $this->semester);
    })->get();
}

public function addAllStudents()
{
    $this->validate([
        'target_section_id' => 'required',
        'course_block_id'   => 'required',
        'academic_year_id'  => 'required',
        'semester'          => 'required',
    ]);

    $students = $this->getStudents();

    if ($students->isEmpty()) {
        session()->flash('message', 'No students found in this section.');
        return;
    }

    // 1. Check if any student in this list is already assigned to this block
    $existingAssignments = DB::table('student_courseblock')
        ->where('courseblock_id', $this->course_block_id)
        ->whereIn('student_id', $students->pluck('id'))
        ->exists();

    if ($existingAssignments) {
        // Use flash('error') to trigger the red alert style
        session()->flash('error', 'Error: Some or all students in this section are already assigned to this Course Block.');
        return;
    }

    // 2. If no duplicates, proceed with the assignment
    foreach ($students as $student) {
        DB::table('student_courseblock')->insert([
            'student_id'     => $student->id,
            'courseblock_id' => $this->course_block_id,
        ]);
    }

    session()->flash('message', 'All students successfully assigned to the Course Block!');
}
}