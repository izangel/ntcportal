<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\StudentCourseBlock as StudentCourseBlockModel;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;

class StudentCourseBlock extends Component
{
    public $subjectLoad = [];
    public $selectedAcademicYear;
    public $selectedSemester;

    public function mount()
    {
        $activeAY = AcademicYear::where('is_active', true)->first();
        $this->selectedAcademicYear = $activeAY ? $activeAY->id : null;
        $this->selectedSemester = '';
    }

    public function render()
    {
        $student = Auth::user()->student;
        $this->subjectLoad = [];

        if ($student && $this->selectedAcademicYear && $this->selectedSemester) {

            $dbSemester = $this->selectedSemester;
            if ($this->selectedSemester === 'First Semester') $dbSemester = '1st';
            if ($this->selectedSemester === 'Second Semester') $dbSemester = '2nd';

            $studentBlocks = StudentCourseBlockModel::with(['courseBlock.course', 'courseBlock.faculty'])
                ->where('student_id', $student->id)
                ->whereHas('courseBlock', function($query) use ($dbSemester) {
                    $query->where('academic_year_id', $this->selectedAcademicYear)
                        ->where('semester', $dbSemester);
                })
                ->get();

            $this->subjectLoad = $studentBlocks->map(function ($sb) {
                $courseBlock = $sb->courseBlock;
                return (object) [
                    'course' => $courseBlock ? $courseBlock->course : null,
                    'faculty_name' => ($courseBlock && $courseBlock->faculty) ? $courseBlock->faculty->first_name . ' ' . $courseBlock->faculty->last_name : 'TBA',
                    'room_name' => $courseBlock ? $courseBlock->room_name : 'TBA',
                    'schedule_string' => $courseBlock ? $courseBlock->schedule_string : 'TBA',
                ];
            });
        }

        return view('livewire.student-course-block', [
            'academicYears' => AcademicYear::orderBy('start_year', 'desc')->get(),
        ])->layout('layouts.admin');
    }
}
