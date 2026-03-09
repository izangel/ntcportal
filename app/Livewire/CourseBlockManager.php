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
use App\Models\SectionStudent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

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

    // --- Data for Lists (Collections must be initialized correctly) ---
    public Collection $students;
    public Collection $courseBlocks;
    public $selectedSection;

    // --- Student Enrollment Form Data (REVISED) ---
    // Holds all students who ARE NOT CURRENTLY in the selected section/context
    public Collection $availableStudentsForAdd;
    public $selectedStudentId = null; // Used by the new dropdown

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
        $this->sections = Section::with('program')
            ->get()
            // Map the collection to an array of just the ID, Name, and Program Name
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'program_name' => $section->program ? $section->program->name : 'N/A', // Assuming 'name' is the column for the program name
                ];
            })
            ->toArray();
        $this->allCourses = Course::orderBy('code')->get();
        $this->allFaculty = Employee::orderBy('last_name')->get();

        // Initialize Collections
        $this->students = collect();
        $this->courseBlocks = collect();
        $this->availableStudentsForAdd = collect(); // Initialize the new property
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
        if ($this->sectionId && $this->academicYearId && $this->semester) {
            $this->loadSectionData();
        } else {
            $this->clearDynamicData();
            $this->selectedSection = Section::find($this->sectionId);
        }
    }

    /**
     * Loads the pool of students currently in the section and students available to be added.
     * Updated to use the SectionStudent membership table.
     */
    protected function loadSectionData()
    {
        // 1. Validate context and find the section
        $this->selectedSection = Section::find($this->sectionId);

        if (!$this->selectedSection || !$this->academicYearId || !$this->semester) {
            $this->clearDynamicData();
            return;
        }

        // 1. Get IDs of students who are ALREADY in THIS specific section
    $studentIdsInThisSection = \App\Models\SectionStudent::where('section_id', $this->sectionId)
                    ->where('academic_year_id', $this->academicYearId)
                    ->where('semester', $this->semester)
                    ->pluck('student_id');

    // 2. Get IDs of students who are in ANY section for this AY/Semester
    // This is the key to preventing that Duplicate Entry error!
    $allTakenStudentIds = \App\Models\SectionStudent::where('academic_year_id', $this->academicYearId)
                    ->where('semester', $this->semester)
                    ->pluck('student_id');

    // 3. Load students currently in the list
    $this->students = Student::whereIn('id', $studentIdsInThisSection)
        ->orderBy('last_name')
        ->get();

    // 4. Update the "Available" list:
    // Only show students who are NOT in the 'allTakenStudentIds' list
    $this->availableStudentsForAdd = Student::whereNotIn('id', $allTakenStudentIds)
        ->orderBy('last_name')
        ->get();
        // 5. Load Course Blocks for the selected context
        // This shows which courses the student will be auto-enrolled into
        $this->courseBlocks = CourseBlock::where('section_id', $this->sectionId)
                                        ->where('academic_year_id', $this->academicYearId)
                                        ->where('semester', $this->semester)
                                        ->with(['course', 'faculty'])
                                        ->get();

        // 6. Reset the selection dropdown state
        $this->selectedStudentId = null;
    }

    /**
     * Resets student and course block data lists.
     */
    protected function clearDynamicData()
    {
        $this->students = collect();
        $this->courseBlocks = collect();
        $this->availableStudentsForAdd = collect();

        $this->reset(['selectedStudentId']);
    }

    // ------------------------------------------
    // --- STUDENT MANAGEMENT METHODS (REVISED) ---
    // ------------------------------------------

    public function addStudentToSection()
{
    $this->validate([
        'selectedStudentId' => 'required|exists:students,id',
        'sectionId'         => 'required',
        'academicYearId'    => 'required',
        'semester'          => 'required',
    ]);

    // CHECK: Is this student already in a section for this term?
    $alreadyEnrolled = \App\Models\SectionStudent::where('student_id', $this->selectedStudentId)
        ->where('academic_year_id', $this->academicYearId)
        ->where('semester', $this->semester)
        ->exists();

    if ($alreadyEnrolled) {
        session()->flash('error', 'This student is already assigned to a section for this semester.');
        return;
    }

    // If they aren't enrolled anywhere, proceed
    \App\Models\SectionStudent::create([
        'student_id'       => $this->selectedStudentId,
        'section_id'       => $this->sectionId,
        'academic_year_id' => $this->academicYearId,
        'semester'         => $this->semester,
    ]);

    $this->reset('selectedStudentId');
    $this->loadSectionData();
    session()->flash('message', "Student successfully added.");
}

    // --- Existing Methods ---

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

            session()->flash('error',
                "The course **{$courseCode}** is already assigned to this section for the selected academic period. Cannot create a duplicate block."
            );
            return;
        }

        // 3. Create the CourseBlock record
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
    // 1. Get all students currently in the section (from the membership table)
    $registrations = \App\Models\SectionStudent::where('section_id', $this->sectionId)
        ->where('academic_year_id', $this->academicYearId)
        ->where('semester', $this->semester)
        ->get();

    if ($registrations->isEmpty() || $this->courseBlocks->isEmpty()) {
        session()->flash('error', 'No students or course blocks found to sync.');
        return;
    }

    $count = 0;
    foreach ($this->courseBlocks as $block) {
        foreach ($registrations as $reg) {
            // firstOrCreate prevents duplicates if they are already half-enrolled
            $enrollment = \App\Models\Enrollment::firstOrCreate([
                'student_id'       => $reg->student_id,
                'course_id'        => $block->course_id,
                'section_id'       => $this->sectionId,
                'academic_year_id' => $this->academicYearId,
                'semester'         => $this->semester,
            ]);

            if ($enrollment->wasRecentlyCreated) {
                $count++;
            }
        }
    }

    session()->flash('message', "Sync complete! Added **{$count}** missing enrollments.");
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
