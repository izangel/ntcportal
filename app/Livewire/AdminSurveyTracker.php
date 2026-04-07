<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\CourseSurvey;
use App\Models\AcademicYear;

class AdminSurveyTracker extends Component
{
    public $search = '';
    public $selectedAY;
    public $selectedSem = 'First Semester';

    public function mount()
    {
        $activeAY = AcademicYear::where('is_active', true)->first();
        $this->selectedAY = $activeAY ? $activeAY->id : null;
    }

    public function render()
    {
        $student = null;
        $reportData = [];

        if (!empty($this->search)) {
            // Search by Student ID Number or Name
            $student = Student::where('student_id_number', $this->search)
                ->orWhere('last_name', 'like', '%' . $this->search . '%')
                ->first();

            if ($student) {
                // Get all subjects this student should have surveyed
                $enrollments = Enrollment::where('student_id', $student->id)
                    ->where('academic_year_id', $this->selectedAY)
                    ->where('semester', $this->selectedSem)
                    ->with('course')
                    ->get();

                foreach ($enrollments as $enrollment) {
                    // Find the survey record if it exists
                    $survey = CourseSurvey::where('student_id', $student->id)
                        ->where('course_id', $enrollment->course_id)
                        ->where('academic_year_id', $this->selectedAY)
                        ->where('semester', $this->selectedSem)
                        ->first();

                    $reportData[] = [
                        'course_code' => $enrollment->course->code,
                        'course_name' => $enrollment->course->name,
                        'is_completed' => !is_null($survey),
                        'rating' => $survey ? $survey->rating : null,
                        'submitted_at' => $survey ? $survey->created_at->format('M d, Y') : null,
                    ];
                }
            }
        }

        return view('livewire.admin-survey-tracker', [
            'student' => $student,
            'reportData' => $reportData,
            'academicYears' => AcademicYear::orderBy('year_start', 'desc')->get()
        ]);
    }
}