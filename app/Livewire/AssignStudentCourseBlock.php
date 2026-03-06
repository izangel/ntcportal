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
    ])->layout('layouts.admin');
}

public function getStudents()
{
    if (!$this->course_block_id) return collect();

    // Use the relationship directly. This is cleaner and avoids manual column naming issues.
return Student::whereHas('courseBlocks', function($query) {
        $query->where('student_courseblock.courseblock_id', $this->course_block_id);
    })
    ->whereDoesntHave('sections', function($query) {
        // Exclude students already in the target section for this term
        $query->where('section_id', $this->target_section_id)
              ->where('academic_year_id', $this->academic_year_id);
    })
    ->get();
}

public function addAllStudents()
{
    $this->validate([
        'target_section_id' => 'required',
        'academic_year_id'  => 'required',
        'semester'          => 'required',
    ]);

    $students = $this->getStudents();

    if ($students->isEmpty()) {
        session()->flash('error', 'No students found in this block to assign.');
        return;
    }

    foreach ($students as $student) {
        DB::table('section_student')->updateOrInsert(
            [
                'student_id' => $student->id,
                'academic_year_id' => $this->academic_year_id,
                'semester' => $this->semester
            ],
            ['section_id' => $this->target_section_id]
        );
    }

    // SUCCESS ACTION: Reset the selections so the list "refreshes" and clears
    $this->reset(['course_block_id', 'target_section_id']);

    session()->flash('message', 'All students successfully assigned to the section!');
}
}