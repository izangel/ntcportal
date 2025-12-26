<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CourseBlock;
use App\Models\Enrollment; 
use Livewire\Attributes\Locked; 
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On; // CRITICAL: Added for listening to the refresh event

class GradeInputForm extends Component
{
    #[Locked]
    public $blockId; 
    
    // --- Grade Data ---
    public $selectedBlock; 
    public $enrolledStudents = []; 
    public $grades = []; 
    
    // --- UI/Status Properties ---
    public $gradesFinalized = false; 
    public $showConfirmationModal = false; 
    public $confirmationChecked = false;
    
    public $statusMessage = ''; 

  

    // ------------------------------------------------------------------
    //  GRADE OPTIONS COMPUTED PROPERTY
    // ------------------------------------------------------------------
    
    #[Computed]
    /**
     * Generates all possible final grade options in descending order (5.0 down to 1.0), 
     * followed by administrative codes: INC and DRP.
     * @return array
     */
    public function gradeOptions(): array
    {
        $options = [];
        
        // 1. Generate numerical grades in DESCENDING order (5.0 down to 1.0)
        
        $fixedGrades = ['5.0', '3.5'];
        
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
    
    public function mount($blockId)
    {
        $this->blockId = $blockId;
        // The mount method calls loadGrades() once during initialization
        $this->loadGrades();
    }
    
    public function loadGrades()
{
    $this->selectedBlock = CourseBlock::find($this->blockId);
    if (!$this->selectedBlock) return;

    $this->gradesFinalized = $this->selectedBlock->finalized;

    // 🔑 STEP 1: Find all sections that share this EXACT schedule/teacher/course
    // This handles the "Merge" (BSIS1 and DIT1) automatically.
    $relatedSectionIds = CourseBlock::where('faculty_id', $this->selectedBlock->faculty_id)
        ->where('course_id', $this->selectedBlock->course_id)
        ->where('academic_year_id', $this->selectedBlock->academic_year_id)
        ->where('semester', $this->selectedBlock->semester)
        ->where('schedule_string', $this->selectedBlock->schedule_string) // Match by time
        ->where('room_name', $this->selectedBlock->room_name)           // Match by room
        ->pluck('section_id');

    // 🔑 STEP 2: Fetch students from ALL those sections for this specific course
    $enrollments = Enrollment::whereIn('section_id', $relatedSectionIds)
                             ->where('academic_year_id', $this->selectedBlock->academic_year_id)
                             ->where('semester', $this->selectedBlock->semester)
                             ->where('course_id', $this->selectedBlock->course_id)
                             ->with(['student', 'section']) 
                             ->get();
                                 
    $this->enrolledStudents = $enrollments->map(function ($enrollment) {
        $studentId = $enrollment->student_id;
        $this->grades[$studentId] = $enrollment->grade;
        
        return [
            'enrollment_id' => $enrollment->id,
            'student_id' => $studentId,
            'student_name' => $enrollment->student->last_name.', '. $enrollment->student->first_name, 
            'section_name' => $enrollment->section->name ?? 'N/A', // 👈 Display BSIS1 or DIT1
            'grade' => $enrollment->grade,
        ];
    })->sortBy(['section_name', 'student_name'])->toArray();
}

    // ------------------------------------------------------------------
    //  ACTIONS
    // ------------------------------------------------------------------

    public function saveGrades()
    {
        // 1. Check for immediate exit
        if (!$this->selectedBlock || $this->gradesFinalized) {
            $this->dispatch('showEmitedFlashMessage', action: 'error_finalized');
            return; 
        }

        // 2. Validation 
        $validGrades = $this->gradeOptions;
        
        $rules = [];
        foreach ($this->enrolledStudents as $student) {
            $rules['grades.' . $student['student_id']] = ['nullable', 'string', Rule::in($validGrades)];
        }
        $this->validate($rules);

        $updatedCount = 0;
        foreach ($this->enrolledStudents as $studentData) {
            $studentId = $studentData['student_id'];
            $newGrade = trim($this->grades[$studentId] ?? ''); 
            
            $currentGrade = Enrollment::where('student_id', $studentId)
                                      ->where('course_id', $this->selectedBlock->course_id)
                                      ->value('grade'); 

            if ($newGrade !== $currentGrade) {
                $updated = Enrollment::where('student_id', $studentId)
                          ->where('course_id', $this->selectedBlock->course_id)
                          ->update(['grade' => empty($newGrade) ? null : $newGrade]);
                          
                if ($updated) {
                    $updatedCount++;
                }
            }
        }

        // 3. Success feedback and refresh
        $this->loadGrades(); 
        
        $this->dispatch('showEmitedFlashMessage', action: 'save', updatedCount: $updatedCount);
    }
    
    public function showSubmitConfirmation()
    {
        $this->confirmationChecked = false;
        $this->showConfirmationModal = true;
    }

    public function submitFinalGrades()
    {
        // 1. Confirmation check
        if (!$this->confirmationChecked) {
            $this->dispatch('showEmitedFlashMessage', action: 'error_confirm');
            return;
        }
        
        // 2. Finalized check 
        if (!$this->selectedBlock || $this->gradesFinalized) {
            $this->showConfirmationModal = false;
            $this->dispatch('showEmitedFlashMessage', action: 'error_finalized');
            return;
        }

        // --- STEP 3: PERSIST DRAFT GRADES TO DATABASE (FINAL SAVE) ---
        
        // a. Run Validation 
        $validGrades = $this->gradeOptions;
        $rules = [];
        foreach ($this->enrolledStudents as $student) {
             $rules['grades.' . $student['student_id']] = ['nullable', 'string', Rule::in($validGrades)];
        }
        $this->validate($rules); 

        // b. Update Enrollment Grades 
        foreach ($this->enrolledStudents as $studentData) {
            $studentId = $studentData['student_id'];
            $newGrade = trim($this->grades[$studentId] ?? ''); 
            
            Enrollment::where('student_id', $studentId)
                      ->where('course_id', $this->selectedBlock->course_id)
                      ->update(['grade' => empty($newGrade) ? null : $newGrade]);
        }
        
        // --- STEP 4: FINALIZE THE COURSE BLOCK STATUS ---

        $block = CourseBlock::find($this->blockId);
        
        if ($block) {
            $block->update(['finalized' => true]);
        } else {
            $this->showConfirmationModal = false;
            $this->dispatch('showEmitedFlashMessage', action: 'error_block');
            return;
        }

        // 5. Success feedback and UI cleanup
        $this->loadGrades(); 
        $this->showConfirmationModal = false;

        $this->reset('confirmationChecked'); 
        
        // Dispatch final success event
        $this->dispatch('showEmitedFlashMessage', action: 'finalize');
    }

    public function render()
    {
        return view('livewire.grade-input-form')
        ->extends('layouts.admin')
        ->section('content');
    }
}