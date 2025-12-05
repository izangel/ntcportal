<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseBlock;
use App\Models\AcademicYear;
use Livewire\Attributes\On; 

class FacultyCourseBlockView extends Component
{
    // --- Selection State (Bound to View Filters) ---
    public $academicYearId;
    public $semester;
    
    // The ID of the block selected from the dropdown
    public $selectedBlockId; 
    
    // 🔑 NEW FLAG: Controls when the GradeInputForm is displayed/loaded.
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
    //  EVENT LISTENERS
    // ------------------------------------------------------------------

    protected $listeners = [
        'showEmitedFlashMessage',
        // Listen for the grade update signal from the child component
        'gradeUpdated' => 'refreshComponent',
    ];

    /**
     * Receives the event dispatched from child components (GradeInputForm, ResolveIncGrade)
     * and flashes the appropriate message to the session.
     */
    public function showEmitedFlashMessage($action, $updatedCount = 0)
    {
        // Attempt to fetch the selected block's details for use in the message context
        $block = $this->assignedBlocks->firstWhere('id', $this->selectedBlockId);
        $courseCode = $block->course->code ?? 'Course';
        
        switch ($action) {
            case 'save':
                session()->flash('message', "Grades for **{$courseCode}** saved as draft. **{$updatedCount}** record/s were changed.");
                break;

            case 'finalize':
                session()->flash('message', "🎉 Grades for **{$courseCode}** have been **FINALIZED** and locked.");
                $this->loadAssignedBlocks(); 
                // Force reload of the child form to show final locked grades/resolution mode
                $this->blockSelectedAndConfirmed = true; 
                break;
                
            case 'inc_resolved':
                session()->flash('message', "✅ INC grade successfully resolved to a numerical grade for **{$courseCode}**.");
                // Note: The refreshComponent will handle the actual reloading
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

        // If an update occurred from a child, force a full refresh.
        if ($action === 'finalize' || $action === 'inc_resolved') {
            $this->refreshComponent();
        }
    }

    /**
     * Catches the 'gradeUpdated' event and forces a component refresh 
     * by incrementing the syncKey.
     */
    public function refreshComponent()
    {
        // 1. Update the parent's data (if needed for the dropdown status/list)
        $this->loadAssignedBlocks(); 
        
        // 2. Increment the key to force the child component to destroy and re-mount.
        $this->syncKey++;
        
        // 3. Ensure the child is visible and ready to load the fresh data
        $this->blockSelectedAndConfirmed = true;
    }

    // ------------------------------------------------------------------
    //  NEW ACTION
    // ------------------------------------------------------------------

    /**
     * Called when the user clicks the "Load Grades" button.
     * This confirms the selection and sets the flag to display the child component.
     */
    public function loadSelectedBlockGrades()
    {
        // 1. Check if a block is actually selected.
        if (!$this->selectedBlockId) {
            session()->flash('error', '❌ Please select a course block first.');
            $this->blockSelectedAndConfirmed = false;
            return;
        }
        
        // 2. Set the confirmation flag to TRUE to display the child component.
        $this->blockSelectedAndConfirmed = true;
        
        // 3. Reset mode to 'grading' 
        $this->activeMode = 'grading';
        
        // 4. Reset syncKey to ensure the child reloads if needed
        $this->syncKey = 0;
    }
    
    // ------------------------------------------------------------------
    //  MOUNT
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
    
    // ------------------------------------------------------------------
    //  DATA LOADING
    // ------------------------------------------------------------------

    public function loadAssignedBlocks()
    {
        // Reset blocks and selection if filters are incomplete
        if (!$this->academicYearId || !$this->semester) {
            $this->assignedBlocks = collect();
            $this->selectedBlockId = null;
            $this->blockSelectedAndConfirmed = false; // Reset confirmation
            return;
        }

        // Fetch all assigned blocks for the dropdown list based on filters
        $this->assignedBlocks = CourseBlock::where('faculty_id', $this->facultyId)
                                            ->where('academic_year_id', $this->academicYearId)
                                            ->where('semester', $this->semester)
                                            ->with('course')
                                            ->get();

        // Check if the currently selected block still exists in the new list
        if (!$this->assignedBlocks->contains('id', $this->selectedBlockId)) {
             $this->selectedBlockId = null;
             // If selection is invalid, reset confirmation
             $this->blockSelectedAndConfirmed = false; 
        }

        $this->render();
    }
    
    // ------------------------------------------------------------------
    //  LIFECYCLE HOOKS
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
        // When the user changes the dropdown, invalidate the grades view
        $this->blockSelectedAndConfirmed = false;
        $this->activeMode = 'grading';
    }

   
    public function render()
    {
        return view('livewire.faculty-course-block-view')->extends('layouts.admin')
            ->section('content');
    }
}