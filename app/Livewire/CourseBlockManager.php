<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Section;
use App\Models\Course;
use App\Models\Student; 
use App\Models\Employee; 
use App\Models\Enrollment; 
use App\Models\AcademicYear;
use App\Models\CourseBlock;

class CourseBlockManager extends Component
{
    // --- Selection State ---
    // These public properties drive the form inputs (Academic Year, Semester, Section).
    // Livewire automatically handles synchronization with wire:model.live.
    public $academicYearId; 
    public $semester;
    public $sectionId;

    // --- Data for Dropdowns ---
    // $sections is deliberately an array (using toArray() in mount) for simple dropdown options.
    public $sections = [];
    public $academicYears = []; 
    public $semesters = ['1st', '2nd', 'Summer'];
    public $allCourses; // Collection
    public $allFaculty; // Collection

    // --- Student & Block Data ---
    // These are loaded when a section is selected. They must be collections for model access.
    public $students = [];
    public $courseBlocks = [];
    public $selectedSection; // Single Section Model instance

    // --- Form Data for New Block ---
    public $newCourseBlock = [
        'course_id' => null,
        'faculty_id' => null,
        'room_name' => null,
        'schedule_string' => null
    ];

    /**
     * Runs once when the component is initialized. Used to populate static dropdown data.
     */
    public function mount()
    {
        // Load Academic Years as a Collection
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get(); 
        
        // Load Sections, converted to an array for simple dropdown display (this is fine)
        $this->sections = Section::all()->map->only(['id', 'name'])->toArray();
        
        // Load related data as Collections
        $this->allCourses = Course::all();
        $this->allFaculty = Employee::all();
    }

    public function updatedSectionId($value)
    {
        // Simply trigger the main check. No need for complex IF statements here.
        $this->handleDropdownChange();
    }

    public function updatedAcademicYearId($value)
    {
        $this->handleDropdownChange();
    }

    public function updatedSemester($value)
    {
        $this->handleDropdownChange();
    }

    /**
     * Handles all dropdown value changes and determines if data should be loaded.
     */
    protected function handleDropdownChange()
    {
        // Check if ALL three required properties are set.
        if ($this->sectionId && $this->academicYearId && $this->semester) {
            $this->loadSectionData();
        } else {
            // If any one is missing, clear the data list but keep the current selections.
            $this->clearDynamicData();
            $this->selectedSection = Section::find($this->sectionId); // Still load the section name if possible
        }
    }

    /**
     * Loads the students and existing course blocks for the selected criteria.
     */
    protected function loadSectionData()
    {
        // Find the selected section
        // Make sure to find the Section Model, not the Enrollment Model!
        $this->selectedSection = Section::find($this->sectionId);

        if (!$this->selectedSection) {
            // If section ID is invalid, clear lists and return.
            $this->clearDynamicData();
            return; 
        }
        
        // ... student loading logic ...
        $studentIds = \DB::table('enrollments')
                        ->where('section_id', $this->sectionId)
                        ->where('academic_year_id', $this->academicYearId)
                        ->where('semester', $this->semester)
                        ->pluck('student_id');

        $this->students = Student::whereIn('id', $studentIds)->get();

        // ... course block loading logic ...
        $this->courseBlocks = CourseBlock::where('section_id', $this->sectionId)
                                        ->where('academic_year_id', $this->academicYearId)
                                        ->where('semester', $this->semester)
                                        ->with(['course', 'faculty'])
                                        ->get();
    }

    /**
     * Resets student and course block data lists.
     */
    protected function clearDynamicData()
    {
        $this->students = collect(); // Initialize as an empty Collection
        $this->courseBlocks = collect(); // Initialize as an empty Collection
    }

    /**
     * Handles saving the new course block to the database.
     */
    public function saveCourseBlock()
    {
        // 1. Validation
        $this->validate([
            'academicYearId' => 'required',
            'semester' => 'required',
            'sectionId' => 'required|exists:sections,id',
            'newCourseBlock.course_id' => 'required|exists:courses,id',
            'newCourseBlock.faculty_id' => 'required|exists:employees,id',
            'newCourseBlock.room_name' => 'required|string|max:100',
            'newCourseBlock.schedule_string' => 'required|string|max:150',
        ]);

        // 2. Create the CourseBlock record
        CourseBlock::create([
            'section_id' => $this->sectionId,
            'course_id' => $this->newCourseBlock['course_id'],
            'faculty_id' => $this->newCourseBlock['faculty_id'],
            'academic_year_id' => $this->academicYearId,
            'semester' => $this->semester,
            'room_name' => $this->newCourseBlock['room_name'],
            'schedule_string' => $this->newCourseBlock['schedule_string'],
        ]);

        // 3. Reset form and refresh data
        $this->reset('newCourseBlock');
        $this->loadSectionData(); // Refresh list to show new block
        session()->flash('message', 'Course block added successfully!');
    }

    /**
     * Renders the corresponding Livewire view.
     */
    public function render()
    {
        return view('livewire.course-block-manager')
          ->extends('layouts.admin')
            ->section('content');
    }
}