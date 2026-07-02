<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{Student, Section, AcademicYear};
use Illuminate\Support\Facades\DB;

class GlobalStudentSearch extends Component
{
    public $query = '';
    public $results = [];
    public $target_section_id;

    public function updatedQuery()
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $this->results = Student::where('last_name', 'like', '%' . $this->query . '%')
            ->orWhere('first_name', 'like', '%' . $this->query . '%')
            ->orWhere('student_id', 'like', '%' . $this->query . '%')
            ->limit(5)
            ->get();
    }

    public function enrollStudent($studentId)
    {
        if (!$this->target_section_id) {
            session()->flash('error', 'Select a target section first.');
            return;
        }

        // 1. Get the current Academic Context manually
        $activeAY = AcademicYear::where('is_active', true)->first();
        
        // This part mimics your Registry's current session context logic
        // If your context comes from a Session, use session('semester')
        $rawSemester = session('semester', 'First Semester'); 
        
        $sem = ($rawSemester == 'Second Semester') ? '2nd Semester' : '1st Semester';

        if (!$activeAY) {
            session()->flash('error', 'No active academic year found.');
            return;
        }

        $student = Student::findOrFail($studentId);

        DB::transaction(function () use ($student, $activeAY, $sem) {
            // 2. Link student to the current term
            DB::table('section_student')->updateOrInsert(
                [
                    'student_id' => $student->id, 
                    'academic_year_id' => $activeAY->id, 
                    'semester' => $sem
                ],
                [
                    'section_id' => $this->target_section_id, 
                    'status' => 'Returnee', 
                    'updated_at' => now()
                ]
            );

            // 3. Sync Course Blocks (Subjects) from the Section Template
            $templateBlocks = DB::table('course_block_section')
                ->where('section_id', $this->target_section_id)
                ->where('academic_year_id', $activeAY->id)
                ->where('semester', $sem)
                ->pluck('course_block_id');

            foreach ($templateBlocks as $blockId) {
                DB::table('student_courseblock')->updateOrInsert([
                    'student_id' => $student->id,
                    'course_block_id' => $blockId,
                ], ['updated_at' => now()]);
            }
        });

        session()->flash('success', "{$student->last_name} enrolled successfully for the current term.");
        return redirect()->route('students.index');
    }

    public function render()
    {
        return view('livewire.global-student-search', [
            'sections' => Section::with('program')->get()->sortBy('name')
        ]);
    }
}