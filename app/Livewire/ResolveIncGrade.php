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

protected function loadIncStudents()
{
    $block = CourseBlock::find($this->blockId);
    
    // Safety check: only load if finalized
    if (!$block || !$block->finalized) {
        $this->incStudents = [];
        $this->studentList = [];
        return;
    }

    // 1. Get all section IDs sharing this specific merged schedule
    $sharedSectionIds = CourseBlock::where('faculty_id', $block->faculty_id)
        ->where('academic_year_id', $block->academic_year_id)
        ->where('semester', $block->semester)
        ->where('course_id', $block->course_id)
        ->where('schedule_string', $block->schedule_string)
        // Note: room_name removed for better matching across merged blocks
        ->pluck('section_id');

    // 2. Fetch enrollments that have 'INC'
    $enrollments = Enrollment::whereIn('section_id', $sharedSectionIds)
        ->where('course_id', $block->course_id)
        ->where(function($query) {
            $query->where('grade', 'INC')
                  ->orWhere('grade', 'inc'); // Case sensitivity safety
        })
        ->with(['student', 'section.program'])
        ->get();

    // 3. Reset and Populate both arrays
    $this->incStudents = [];
    $this->studentList = [];

    foreach ($enrollments as $enrollment) {
        $sName = $enrollment->student->last_name . ', ' . $enrollment->student->first_name;
        $secName = ($enrollment->section->program->name ?? '') . '-' . ($enrollment->section->name ?? '');
        
        // Full data for the resolution panel
        $this->incStudents[$enrollment->student_id] = [
            'enrollment_id' => $enrollment->id,
            'student_id'    => $enrollment->student_id,
            'student_name'  => $sName,
            'section_name'  => $secName,
            'current_grade' => $enrollment->grade,
        ];

        // Label for the dropdown (including section for clarity)
        $this->studentList[$enrollment->student_id] = "{$sName} ({$secName})";
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