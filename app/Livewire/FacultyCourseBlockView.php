<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseBlock;
use App\Models\AcademicYear;
use App\Models\Enrollment; // 🔑 ADDED: Need to use the Enrollment model directly
use Livewire\Attributes\On; 

class FacultyCourseBlockView extends Component
{
    // --- Selection State (Bound to View Filters) ---
    public $academicYearId;
    public $semester;
    
    // The ID of the block selected from the dropdown
    public $selectedBlockId; 
    
    // 🔑 FLAG: Controls when the GradeInputForm is displayed/loaded.
    public $blockSelectedAndConfirmed = false;
    
    // --- Internal State/Data ---
    public $facultyId; 
    public $academicYears = [];
    public $semesters = ['1st', '2nd', 'Summer'];
    public $assignedBlocks = []; 

    public $activeMode = 'grading'; // 'grading' or 'resolution'
    
    // 🔑 SYNC KEY: Used to force child component reload after actions (e.g., INC resolution).
    public $syncKey = 0;
    
    // ------------------------------------------------------------------
    //  EVENT LISTENERS (Existing Methods Remain)
    // ------------------------------------------------------------------

    protected $listeners = [
        'showEmitedFlashMessage',
        'gradeUpdated' => 'refreshComponent',
    ];

    public function showEmitedFlashMessage($action, $updatedCount = 0)
    {
        // ... (Existing implementation for flash messages)
        $block = $this->assignedBlocks->firstWhere('id', $this->selectedBlockId);
        $courseCode = $block->course->code ?? 'Course';
        
        switch ($action) {
            case 'save':
                session()->flash('message', "Grades for **{$courseCode}** saved as draft. **{$updatedCount}** record/s were changed.");
                break;

            case 'finalize':
                session()->flash('message', "🎉 Grades for **{$courseCode}** have been **FINALIZED** and locked.");
                $this->loadAssignedBlocks(); 
                $this->blockSelectedAndConfirmed = true; 
                break;
                
            case 'inc_resolved':
                session()->flash('message', "✅ INC grade successfully resolved to a numerical grade for **{$courseCode}**.");
                break;
                
            case 'error_finalized':
                session()->flash('error', '⚠️ Cannot save or finalize. Grades are already finalized and locked.');
                break;

            case 'error_confirm':
                session()->flash('error', '❌ You must confirm the grades before final submission.');
                break;

            case 'error_block':
                session()->flash('error', '❌ Error: Course block model not found for action.');
                break;

            case 'error_inc_resolve_fail':
                session()->flash('error', "❌ Error resolving INC grade for **{$courseCode}**. The grade may have already been changed or the record was not found.");
                break;
                
            default:
                session()->flash('message', 'Action completed successfully.');
                break;
        }

        if ($action === 'finalize' || $action === 'inc_resolved') {
            $this->refreshComponent();
        }
    }

    public function refreshComponent()
    {
        $this->loadAssignedBlocks(); 
        $this->syncKey++;
        $this->blockSelectedAndConfirmed = true;
    }

    // ------------------------------------------------------------------
    //  ACTION: Load Grades (Existing Method Remains)
    // ------------------------------------------------------------------

    public function loadSelectedBlockGrades()
    {
        if (!$this->selectedBlockId) {
            session()->flash('error', '❌ Please select a course block first.');
            $this->blockSelectedAndConfirmed = false;
            return;
        }
        
        $this->blockSelectedAndConfirmed = true;
        $this->activeMode = 'grading';
        $this->syncKey = 0;
    }
    
    // ------------------------------------------------------------------
    //  ACTION: Print Finalized Grades (UPDATED LOGIC)
    // ------------------------------------------------------------------

    /**
     * Fetches the finalized grades for the selected block using the same 
     * contextual query logic as the GradeInputForm child component.
     */
    public function printFinalizedGrades()
    {
        if (!$this->selectedBlockId) {
            session()->flash('error', '❌ Please select a course block first.');
            return;
        }

        // 1. Get the context block (requires course, academic year, and section info)
        $contextBlock = CourseBlock::where('id', $this->selectedBlockId)
                                   ->where('faculty_id', $this->facultyId) // Security check
                                   ->with(['course', 'academicYear'])
                                   ->first();

        if (!$contextBlock) {
            session()->flash('error', '❌ Error: Course block not found or you are not authorized.');
            return;
        }

        // 2. Ensure the grades are finalized before allowing print
        if (!$contextBlock->finalized) {
            session()->flash('error', '⚠️ Cannot print: Grades for this block have not been finalized yet.');
            return;
        }
        
        // 3. 🔑 CRITICAL: Use the exact query fields from GradeInputForm to fetch enrollments 🔑
        $enrollments = Enrollment::where('section_id', $contextBlock->section_id)
                                 ->where('academic_year_id', $contextBlock->academic_year_id)
                                 ->where('semester', $contextBlock->semester)
                                 ->where('course_id', $contextBlock->course_id)
                                 ->with('student') 
                                 ->get();


        // 4. Prepare the data structure for the print view
        $gradeData = [
            'courseCode' => $contextBlock->course->code ?? 'N/A',
            'courseName' => $contextBlock->course->name ?? 'N/A',
            'blockDetails' => "{$contextBlock->schedule_string} (Room: {$contextBlock->room_name})",
            'academicPeriod' => "{$contextBlock->academicYear->start_year}-{$contextBlock->academicYear->end_year}", 
            'semester' => $contextBlock->semester,
            'teacherName' => Auth::user()->name,
            'students' => $enrollments->map(function ($enrollment) {
                return [
                    'studentId' => $enrollment->student->student_id ?? 'N/A',
                    'studentName' => $enrollment->student->full_name ?? $enrollment->student->last_name . ', ' . $enrollment->student->first_name, // Use the concatenated name if available
                    'finalGrade' => $enrollment->grade ?? 'INC', // 🔑 Use 'grade' column 🔑
                ];
            })->sortBy('studentName')->values()->all(), 
        ];

        // 5. Emit a browser event to trigger the print action in the view.
        $this->dispatch('triggerPrint', $gradeData);
    }
    
    // ------------------------------------------------------------------
    //  MOUNT & DATA LOADING (Existing Methods Remain)
    // ------------------------------------------------------------------

    public function mount()
    {
        if (!Auth::check() || !Auth::user()->employee) {
             return;
        }

        $this->facultyId = Auth::user()->employee->id;
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        if ($this->academicYears->isNotEmpty()) {
            $this->academicYearId = $this->academicYears->first()->id;
        }

        $this->loadAssignedBlocks();
    }
    
    public function loadAssignedBlocks()
    {
        if (!$this->academicYearId || !$this->semester) {
            $this->assignedBlocks = collect();
            $this->selectedBlockId = null;
            $this->blockSelectedAndConfirmed = false;
            return;
        }

        $this->assignedBlocks = CourseBlock::where('faculty_id', $this->facultyId)
                                            ->where('academic_year_id', $this->academicYearId)
                                            ->where('semester', $this->semester)
                                            ->with('course')
                                            ->get();

        if (!$this->assignedBlocks->contains('id', $this->selectedBlockId)) {
             $this->selectedBlockId = null;
             $this->blockSelectedAndConfirmed = false; 
        }
    }
    
    // ------------------------------------------------------------------
    //  LIFECYCLE HOOKS (Existing Methods Remain)
    // ------------------------------------------------------------------

    public function updatedAcademicYearId() 
    { 
        $this->loadAssignedBlocks(); 
        $this->blockSelectedAndConfirmed = false;
        $this->selectedBlockId = null;
    }
    
    public function updatedSemester() 
    { 
        $this->loadAssignedBlocks(); 
        $this->blockSelectedAndConfirmed = false;
        $this->selectedBlockId = null;
    }

    public function updatedSelectedBlockId()
    {
        $this->blockSelectedAndConfirmed = false;
        $this->activeMode = 'grading';
    }

    public function render()
    {
        return view('livewire.faculty-course-block-view')->extends('layouts.admin')
            ->section('content');
    }
}