<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{Student, Section, AcademicYear, Semester};
use Illuminate\Support\Facades\DB;

class EnrollOldStudentModal extends Component
{
    public $isOpen = false; // Consolidated into one variable
    public $query = '';
    public $results = [];
    public $target_section_id;
    public $forcedSemester;

    // Listen for the event dispatched from the button
    protected $listeners = ['openEnrollModal' => 'open'];

    public function mount($forcedSemester = null)
    {
        $this->forcedSemester = $forcedSemester;
    }

    public function open()
    {
        $this->reset(['query', 'results', 'target_section_id']);
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    // Handles the live search as you type
    public function updatedQuery()
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $this->results = Student::where('first_name', 'like', '%' . $this->query . '%')
            ->orWhere('last_name', 'like', '%' . $this->query . '%')
            ->orWhere('student_id', 'like', '%' . $this->query . '%')
            ->limit(5)
            ->get();
    }

    public function enroll($studentId)
    {
        $this->validate([
            'target_section_id' => 'required'
        ], [
            'target_section_id.required' => 'Please select a section first.'
        ]);

        $activeAY = AcademicYear::where('is_active', true)->first();
        
        // Use forcedSemester or fallback to active semester name
        $sem = $this->forcedSemester;
        if (!$sem) {
            $activeSemester = Semester::where('is_active', true)->first();
            // Translate "Second Semester" to "2nd Semester" to match pivot table
            $sem = str_replace(['First', 'Second'], ['1st', '2nd'], $activeSemester->name ?? '1st Semester');
        }

        DB::transaction(function () use ($studentId, $activeAY, $sem) {
            // 1. Enrollment
            DB::table('section_student')->updateOrInsert(
                [
                    'student_id' => $studentId, 
                    'academic_year_id' => $activeAY->id, 
                    'semester' => $sem
                ],
                [
                    'section_id' => $this->target_section_id, 
                    'status' => 'Returnee', 
                    'updated_at' => now()
                ]
            );

            // 2. Subject Sync
            $blocks = DB::table('course_block_section')
                ->where('section_id', $this->target_section_id)
                ->where('academic_year_id', $activeAY->id)
                ->where('semester', $sem)
                ->pluck('course_block_id');

            foreach ($blocks as $id) {
                DB::table('student_courseblock')->updateOrInsert([
                    'student_id' => $studentId, 
                    'course_block_id' => $id
                ], ['updated_at' => now()]);
            }
        });

        session()->flash('success', 'Student enrolled successfully.');
        return redirect()->route('students.index');
    }

    public function render()
    {
        return view('livewire.enroll-old-student-modal', [
            'sections' => Section::with('program')->get()
        ]);
    }
}