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
use Illuminate\Support\Facades\DB; // Required for bulk operations

class CourseBlockManager extends Component
{
    // --- Selection State ---
    public $academicYearId; 
    public $semester;
    public $sectionId;

    // --- Data for Dropdowns ---
    public $sections = [];
    public $academicYears = []; 
    public $semesters = ['1st', '2nd', 'Summer'];
    public $allCourses; 
    public $allFaculty; 

    // --- Data for Lists ---
    // $students now holds the list of all students in the selected section (for display and mass enrollment)
    public $students = []; 
    public $courseBlocks = [];
    public $selectedSection;

    // --- Form Data for New Block ---
    public $newCourseBlock = [
        'course_id' => null,
        'faculty_id' => null,
        'room_name' => null,
        'schedule_string' => null
    ];

    /**
     * Runs once when the component is initialized.
     */
    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get(); 
        $this->sections = Section::all()->map->only(['id', 'name'])->toArray();
        $this->allCourses = Course::all();
        $this->allFaculty = Employee::all();
    }

    // --- Lifecycle Hooks ---

    public function updatedSectionId($value)
    {
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
     * Loads the pool of students and existing course blocks for the selected criteria.
     */
    protected function loadSectionData()
    {
        $this->selectedSection = Section::find($this->sectionId);

        if (!$this->selectedSection) {
            $this->clearDynamicData();
            return; 
        }
        
        // 1. Load ALL students enrolled in the section (POOL for enrollment)
        $studentIds = DB::table('enrollments')
                        ->where('section_id', $this->sectionId)
                        ->where('academic_year_id', $this->academicYearId)
                        ->where('semester', $this->semester)
                        // Grouping and selecting distinct student IDs assuming initial enrollment is per section, not course
                        ->groupBy('student_id') 
                        ->pluck('student_id');

        $this->students = Student::whereIn('id', $studentIds)
            ->orderBy('last_name')
            ->get();

        // 2. Load Course Blocks for the selected context
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
        $this->students = collect(); 
        $this->courseBlocks = collect();
    }

    /**
     * Handles saving the new course block to the database.
     */
    public function saveCourseBlock()
    {
        // 1. Validation for standard fields
        $this->validate([
            'academicYearId' => 'required',
            'semester' => 'required',
            'sectionId' => 'required|exists:sections,id',
            'newCourseBlock.course_id' => 'required|exists:courses,id',
            'newCourseBlock.faculty_id' => 'required|exists:employees,id',
            'newCourseBlock.room_name' => 'required|string|max:100',
            'newCourseBlock.schedule_string' => 'required|string|max:150',
        ]);

        // --- 2. Conflict Check ---
        $existingBlock = CourseBlock::where('course_id', $this->newCourseBlock['course_id'])
                                            ->where('section_id', $this->sectionId)
                                            ->where('academic_year_id', $this->academicYearId)
                                            ->where('semester', $this->semester)
                                            ->first();

        if ($existingBlock) {
            $courseCode = Course::find($this->newCourseBlock['course_id'])->code;

            // Flash an error message and stop execution
            session()->flash('error', 
                "The course **{$courseCode}** is already assigned to this section for the selected academic period. Cannot create a duplicate block."
            );
            return; // Stop the method
        }

        // 3. Create the CourseBlock record (Only runs if no conflict found)
        CourseBlock::create([
            'section_id' => $this->sectionId,
            'course_id' => $this->newCourseBlock['course_id'],
            'faculty_id' => $this->newCourseBlock['faculty_id'],
            'academic_year_id' => $this->academicYearId,
            'semester' => $this->semester,
            'room_name' => $this->newCourseBlock['room_name'],
            'schedule_string' => $this->newCourseBlock['schedule_string'],
        ]);

        // 4. Reset form and refresh data
        $this->reset('newCourseBlock');
        $this->loadSectionData();
        session()->flash('message', 'Course block added successfully!');
    }

    /**
     * MASS ENROLLMENT: Enroll all students in the section into all course blocks assigned to that section,
     * ensuring no duplicates.
     */
    public function enrollAllSectionStudents()
    {
        // Basic check for context data and students/blocks
        if (!$this->sectionId || !$this->academicYearId || !$this->semester || $this->students->isEmpty() || $this->courseBlocks->isEmpty()) {
            session()->flash('error', 'Please select the context and ensure students and course blocks are loaded.');
            return;
        }

        $newEnrollments = [];
        $studentIds = $this->students->pluck('id')->toArray();
        $now = now();

        // Get all existing enrollments for the current section/AY/semester
        $existingEnrollments = Enrollment::where('section_id', $this->sectionId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester', $this->semester)
            ->get()
            ->map(function ($item) {
                return "{$item->student_id}-{$item->course_id}";
            })
            ->toArray();

        $enrolledCount = 0;

        // Loop through each course block
        foreach ($this->courseBlocks as $block) {
            $courseId = $block->course_id;

            // Loop through each student in the section
            foreach ($studentIds as $studentId) {
                $uniqueKey = "{$studentId}-{$courseId}";

                // Check if enrollment already exists (student + course combination)
                if (!in_array($uniqueKey, $existingEnrollments)) {
                    $newEnrollments[] = [
                        'student_id' => $studentId,
                        'course_id' => $courseId,
                        'section_id' => $this->sectionId,
                        'academic_year_id' => $this->academicYearId,
                        'semester' => $this->semester,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    // Add to existing list for subsequent course block checks within the same batch
                    $existingEnrollments[] = $uniqueKey; 
                    $enrolledCount++;
                }
            }
        }

        if (!empty($newEnrollments)) {
            // Insert all new enrollment records at once
            Enrollment::insert($newEnrollments);
            session()->flash('message', "Mass Enrollment successful! Added **{$enrolledCount}** new enrollments across {$this->courseBlocks->count()} course blocks.");
        } else {
            session()->flash('message', 'All students are already enrolled in all assigned course blocks for this section.');
        }

        $this->loadSectionData();
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

