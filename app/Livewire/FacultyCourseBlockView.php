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
        // 🔑 Fix: Wrap the array in collect() and use array access ['course_code']
        $block = collect($this->assignedBlocks)->firstWhere('id', $this->selectedBlockId);
        $courseCode = $block['course_code'] ?? 'Course';
        
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
                
            // ... (rest of your switch cases remain the same)
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

    $contextBlock = CourseBlock::where('id', $this->selectedBlockId)
                               ->with(['course', 'academicYear'])
                               ->first();

    if (!$contextBlock || !$contextBlock->finalized) {
        session()->flash('error', '⚠️ Grades are not yet finalized.');
        return;
    }

    // 1. Get Name from Employees Table
    $user = auth()->user();
    $employee = $user->employee; // Access the related employee record

    if ($employee) {
        $mInitial = !empty($employee->mid_name) 
            ? ' ' . strtoupper(substr($employee->mid_name, 0, 1)) . '.' 
            : '';
        $fullTeacherName = "{$employee->first_name}{$mInitial} {$employee->last_name}";
    } else {
        // Fallback to user table if employee record is missing
        $fullTeacherName = $user->name; 
    }

    // 2. Fetch Merged Sections Data
    $relatedBlocks = CourseBlock::where('faculty_id', $contextBlock->faculty_id)
        ->where('academic_year_id', $contextBlock->academic_year_id)
        ->where('semester', $contextBlock->semester)
        ->where('course_id', $contextBlock->course_id)
        ->where('schedule_string', $contextBlock->schedule_string)
        ->with(['section.program'])
        ->get();

    $sectionIds = $relatedBlocks->pluck('section_id');
    $mergedSectionNames = $relatedBlocks->map(function($b) {
        return ($b->section->program->name ?? '') . '-' . ($b->section->name ?? '');
    })->unique()->implode(', ');

    // 3. Prepare Student List
    $enrollments = Enrollment::whereIn('section_id', $sectionIds)
                             ->where('course_id', $contextBlock->course_id)
                             ->with(['student', 'section.program'])
                             ->get();

    $students = $enrollments->map(function ($enrollment) {
        return [
            'studentName' => $enrollment->student->last_name . ', ' . $enrollment->student->first_name,
            'section'     => ($enrollment->section->program->name ?? 'N/A') . '-' . ($enrollment->section->name ?? 'N/A'),
            'finalGrade'  => $enrollment->grade ?? 'INC',
            'last_name'   => $enrollment->student->last_name,
        ];
    })->sortBy('last_name')->values()->all();

    $gradeData = [
        'courseCode'     => $contextBlock->course->code,
        'courseName'     => $contextBlock->course->name,
        'scheduleString' => $contextBlock->schedule_string,
        'blockDetails'   => $mergedSectionNames,
        'academicPeriod' => "{$contextBlock->academicYear->start_year}-{$contextBlock->academicYear->end_year} ({$contextBlock->semester} SEM)",
        'teacherName'    => $fullTeacherName,
        'students'       => $students,
    ];

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
        $this->assignedBlocks = [];
        return;
    }

    $allBlocks = CourseBlock::where('faculty_id', $this->facultyId)
                            ->where('academic_year_id', $this->academicYearId)
                            ->where('semester', $this->semester)
                            ->with(['course', 'section.program'])
                            ->get();

    // 🔑 Group by Course + Schedule to handle merged/combined classes
    $this->assignedBlocks = $allBlocks->groupBy(function($item) {
        return $item->course_id . '-' . $item->schedule_string;
    })->map(function($group) {
        $firstBlock = $group->first();

        // 🔑 Build the Section string (e.g., "BSIS-1A, DIT-1B")
        $sections = $group->map(function($item) {
            $program = $item->section->program->name ?? 'N/A';
            $section = $item->section->name ?? 'N/A';
            return "{$program}-{$section}";
        })->unique()->sort()->implode(', ');

        // 🔑 Return as an array to ensure Livewire remembers the data after selection
        return [
            'id'              => $firstBlock->id,
            'course_code'     => $firstBlock->course->code,
            'course_name'     => $firstBlock->course->name,
            'schedule_string' => $firstBlock->schedule_string,
            'sections'        => $sections,
            'finalized'       => $firstBlock->finalized, // 🔑 Add this line!
        ];
    })->values()->toArray(); // resets keys and converts to persistent array
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