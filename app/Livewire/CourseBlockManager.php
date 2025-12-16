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
     */
    protected function loadSectionData()
    {
        $this->selectedSection = Section::find($this->sectionId);

        if (!$this->selectedSection) {
            $this->clearDynamicData();
            return; 
        }
        
        // 1. Get IDs of students CURRENTLY in the section (Source: Enrollments)
        $studentIdsInSection = Enrollment::select('student_id')
                        ->where('section_id', $this->sectionId)
                        ->where('academic_year_id', $this->academicYearId)
                        ->where('semester', $this->semester)
                        ->distinct() 
                        ->pluck('student_id');

        // 2. Load Student objects CURRENTLY in the section (for display)
        $this->students = Student::whereIn('id', $studentIdsInSection)
            ->orderBy('last_name')
            ->get();

        // 3. Load Student objects AVAILABLE to be added (NOT in the section)
        $this->availableStudentsForAdd = Student::whereNotIn('id', $studentIdsInSection)
            ->orderBy('last_name')
            ->get();

        // 4. Load Course Blocks for the selected context
        $this->courseBlocks = CourseBlock::where('section_id', $this->sectionId)
                                        ->where('academic_year_id', $this->academicYearId)
                                        ->where('semester', $this->semester)
                                        ->with(['course', 'faculty'])
                                        ->get();
        
        // Reset selection ID
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
    
    // The search/select methods are REMOVED as they are replaced by the dropdown

    /**
     * Adds the selected student to the section by creating an initial Enrollment record.
     */
    public function addStudentToSection()
    {
        // 1. Validation: Ensure a student is selected and context is set.
        $this->validate([
            'selectedStudentId' => 'required|exists:students,id',
            'sectionId' => 'required',
            'academicYearId' => 'required',
            'semester' => 'required',
        ]);
        
        // 2. Find the course ID to use for the initial enrollment
        $firstCourseBlock = $this->courseBlocks->first();

        if (!$firstCourseBlock) {
             session()->flash('error', 'Cannot add student: There must be at least one course block assigned to the section to create the initial enrollment record.');
             return;
        }
        
        // 3. Conflict Check (Ensure student doesn't have an enrollment for ANY course in this context already)
        // This check is slightly redundant due to `loadSectionData` logic, but good practice.
        $exists = Enrollment::where('student_id', $this->selectedStudentId)
                            ->where('section_id', $this->sectionId) 
                            ->where('academic_year_id', $this->academicYearId)
                            ->where('semester', $this->semester)
                            ->exists();

        if ($exists) {
            session()->flash('error', 'Student is already associated with this section for the selected academic period.');
            return;
        }

        // 4. Create the initial Enrollment record
        Enrollment::create([
            'student_id' => $this->selectedStudentId,
            'course_id' => $firstCourseBlock->course_id, 
            'section_id' => $this->sectionId,
            'academic_year_id' => $this->academicYearId,
            'semester' => $this->semester,
        ]);
        
        // 5. Reset form and refresh data
        $student = Student::find($this->selectedStudentId);
        $studentName = $student ? ($student->last_name . ', ' . $student->first_name) : 'Student';
        
        $this->reset('selectedStudentId');
        $this->loadSectionData();
        
        session()->flash('message', "Student **{$studentName}** successfully added to the section. Remember to run 'Enroll All' to assign them to all other course blocks.");
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
        if (!$this->sectionId || !$this->academicYearId || !$this->semester || $this->students->isEmpty() || $this->courseBlocks->isEmpty()) {
            session()->flash('error', 'Please select the context and ensure students and course blocks are loaded.');
            return;
        }

        $newEnrollments = [];
        $studentIds = $this->students->pluck('id')->toArray();
        $now = now();

        $existingEnrollments = Enrollment::where('section_id', $this->sectionId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester', $this->semester)
            ->get()
            ->map(function ($item) {
                return "{$item->student_id}-{$item->course_id}";
            })
            ->toArray();

        $enrolledCount = 0;

        foreach ($this->courseBlocks as $block) {
            $courseId = $block->course_id;

            foreach ($studentIds as $studentId) {
                $uniqueKey = "{$studentId}-{$courseId}";

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
                    $existingEnrollments[] = $uniqueKey; 
                    $enrolledCount++;
                }
            }
        }

        if (!empty($newEnrollments)) {
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