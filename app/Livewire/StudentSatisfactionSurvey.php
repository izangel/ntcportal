<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\CourseSurvey;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;

class StudentSatisfactionSurvey extends Component
{
    public $selectedAY;
    public $selectedSem;
    
    // Survey Form State
    public $showModal = false;
    public $targetCourseId;
    public $targetCourseName;
    public $rating;
    public $feedback;

    public function mount()
    {
        // Default to active Academic Year
        $activeAY = AcademicYear::where('is_active', true)->first();
        $this->selectedAY = $activeAY ? $activeAY->id : null;
        $this->selectedSem = 'First Semester';
    }

    public function openSurvey($courseId, $courseName)
    {
        $this->reset(['rating', 'feedback']);
        $this->targetCourseId = $courseId;
        $this->targetCourseName = $courseName;
        $this->showModal = true;
    }

    public function submitSurvey()
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500',
        ]);

        CourseSurvey::create([
            'student_id' => Auth::user()->student->id,
            'course_id' => $this->targetCourseId,
            'academic_year_id' => $this->selectedAY,
            'semester' => $this->selectedSem,
            'rating' => $this->rating,
            'feedback' => $this->feedback,
        ]);

        $this->showModal = false;
        session()->flash('message', 'Thank you! Your feedback has been recorded.');
    }

    public function render()
    {
        $studentId = Auth::user()->student->id;

        // Fetch subjects the student is enrolled in for the selected period
        $subjects = Enrollment::where('student_id', $studentId)
            ->where('academic_year_id', $this->selectedAY)
            ->where('semester', $this->selectedSem)
            ->with(['course'])
            ->get()
            ->map(function ($enrollment) use ($studentId) {
                // Check if this specific course has already been surveyed
                $enrollment->has_answered = CourseSurvey::where('student_id', $studentId)
                    ->where('course_id', $enrollment->course_id)
                    ->where('academic_year_id', $this->selectedAY)
                    ->where('semester', $this->selectedSem)
                    ->exists();
                return $enrollment;
            });

        return view('livewire.student-satisfaction-survey', [
            'subjects' => $subjects,
            'academicYears' => AcademicYear::orderBy('year_start', 'desc')->get()
        ]);
    }
}