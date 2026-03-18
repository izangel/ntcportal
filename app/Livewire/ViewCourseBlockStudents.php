<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CourseBlock;
use Illuminate\Support\Facades\DB;

class ViewCourseBlockStudents extends Component
{
    public $course_block_id;

    public function mount($id)
    {
        $this->course_block_id = $id;
    }

    public function removeStudent($studentId)
    {
        // Option to drop a student from this specific block
        DB::table('student_courseblock')
            ->where('course_block_id', $this->course_block_id)
            ->where('student_id', $studentId)
            ->delete();

        session()->flash('message', 'Student removed from this block.');
    }

    public function render()
    {
        $block = CourseBlock::with(['course', 'faculty', 'academicYear'])
            ->findOrFail($this->course_block_id);

        // Get students specifically assigned to this block
        $students = \App\Models\Student::whereHas('courseBlocks', function($query) {
            $query->where('course_block_id', $this->course_block_id);
        })->orderBy('last_name')->get();

        return view('livewire.view-course-block-students', [
            'block' => $block,
            'students' => $students
        ])->extends('layouts.admin')->section('content');
    }
}