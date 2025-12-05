<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CourseBlock;
use App\Models\Enrollment;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;

class ResolveIncGrade extends Component
{
    #[Locked]
    public $blockId;

    #[Locked]
    public $courseId; // Holds the course ID associated with the block

    public $incStudents = [];      // Full data of INC students (indexed by ID)
    public $resolvedGrades = [];   // Holds the new numerical grade input for each student
    
    public $selectedStudentId = null; 
    public $studentList = []; 

    // --- Computed Property for Grade Options ---
    
    #[Computed]
    /**
     * Generates all possible final grade options in descending order (5.0 down to 1.0), 
     * followed by administrative codes: INC and DRP.
     * @return array
     */
    public function numericalGradeOptions(): array
    {
        $options = [];
        
        // 1. Generate numerical grades in DESCENDING order (5.0 down to 1.0)
        
        $fixedGrades = ['5.0', '4.0', '3.5'];
        
        // Generate grades from 3.0 down to 1.0 in 0.1 increments
        $descendingGrades = [];
        for ($i = 30; $i >= 10; $i--) {
            $descendingGrades[] = number_format($i / 10, 1);
        }
        
        $allNumerical = array_merge($fixedGrades, $descendingGrades);
        
        // Use usort for clean descending order based on float value
        usort($allNumerical, function($a, $b) {
            return (float)$b <=> (float)$a;
        });
        
        $allNumerical = array_unique($allNumerical);

        // 2. Append administrative codes
        $options = array_merge($allNumerical, ['INC', 'DRP']);
        
        // 3. Final cleanup and re-indexing
        return array_values(array_filter($options));
    }

    // --- Mount ---
    
    public function mount($blockId)
    {
        $this->blockId = $blockId;
        
        $block = CourseBlock::find($this->blockId);

        if ($block) {
            $this->courseId = $block->course_id; 
        } else {
            return;
        }
        
        $this->loadIncStudents();
    }

    // --- Data Loading ---
    
    protected function loadIncStudents()
    {
        $block = CourseBlock::find($this->blockId);

        if (!$block || !$block->finalized) {
            $this->incStudents = [];
            $this->studentList = [];
            $this->selectedStudentId = null;
            return;
        }

        $enrollments = Enrollment::where('section_id', $block->section_id)
                                 ->where('academic_year_id', $block->academic_year_id)
                                 ->where('semester', $block->semester)
                                 ->where('course_id', $block->course_id)
                                 ->where('grade', 'INC')
                                 ->with('student')
                                 ->get();

        $this->incStudents = [];
        $this->studentList = []; 
        
        foreach ($enrollments as $enrollment) {
            $studentId = $enrollment->student_id;
            $studentName = $enrollment->student->last_name.', '. $enrollment->student->first_name;
            
            $this->incStudents[$studentId] = [
                'enrollment_id' => $enrollment->id,
                'student_id' => $studentId,
                'student_name' => $studentName,
                'current_grade' => $enrollment->grade,
            ];
            
            $this->studentList[$studentId] = $studentName;
            $this->resolvedGrades[$studentId] = ''; 
        }

        // If the current student is no longer in the list, select the next available, or null
        if (!isset($this->incStudents[$this->selectedStudentId]) && !empty($this->studentList)) {
            $this->selectedStudentId = array_key_first($this->studentList);
        } elseif (empty($this->studentList)) {
            $this->selectedStudentId = null;
        }
    }
    
    // --- INC Resolution Action ---

    public function resolveGrade()
    {
        $studentId = $this->selectedStudentId;
        
        // 1. Pre-Check
        if (!$studentId || !isset($this->incStudents[$studentId])) {
            $this->dispatch('showEmitedFlashMessage', action: 'error_inc_resolve_fail');
            return;
        }

        $newGrade = trim($this->resolvedGrades[$studentId] ?? '');

        // 2. Validation
        $validNumericalGrades = $this->numericalGradeOptions;
        
        $this->validate([
            "resolvedGrades.{$studentId}" => ['required', 'string', Rule::in($validNumericalGrades)],
        ], [
            "resolvedGrades.{$studentId}.required" => 'A resolution grade must be selected.',
            "resolvedGrades.{$studentId}.in" => 'Invalid grade selected for resolution.',
        ]);

        // 3. Update Enrollment Grade, including audit fields
        $updated = Enrollment::where('student_id', $studentId)
                             ->where('course_id', $this->courseId) 
                             ->where('grade', 'INC') 
                             ->update([
                                 'grade' => $newGrade,
                                 
                                 // AUDIT FIELDS
                                 'original_grade' => 'INC', 
                                 'resolution_date' => now(), 
                                 'resolved_by_user_id' => auth()->id(), 
                             ]);
        
        if ($updated) {
            // 4. Dispatch Success Message (for the Toast)
            $this->dispatch('showEmitedFlashMessage', action: 'inc_resolved');
            
            // 🔑 CRITICAL FIX: Dispatch event to force GradeInputForm to refresh its data
            $this->dispatch('gradeUpdated'); 

            // 5. Refresh data to remove the resolved student from the resolution list
            $this->loadIncStudents();
        } else {
             $this->dispatch('showEmitedFlashMessage', action: 'error_inc_resolve_fail');
        }
    }

    public function render()
    {
        return view('livewire.resolve-inc-grade')
        ->extends('layouts.admin')
        ->section('content');
    }
}