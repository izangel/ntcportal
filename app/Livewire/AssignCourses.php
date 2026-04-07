<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\Semester; 
use App\Models\Course;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AssignCourses extends Component
{
    // --- Data Properties ---
    public $academicYears = [];
    public $sections = [];
    public $courses = [];
    public $semesters = []; 
    public $students = []; 
    
    // --- Selection Properties (Bound to Dropdowns) ---
    public $selectedAcademicYearId = null;
    public $selectedSemesterId = null; 
    public $selectedSectionId = null;
    
    // --- Lifecycle Method: Initial Load ---
    public function mount()
    {
        // Load academic years on initial page load
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
    }
    
    // --- Listener for when a selection is updated ---
    public function updated($property)
    {
        // 1. Academic Year Changed (Top of the chain)
        if ($property === 'selectedAcademicYearId') {
            // Clear all dependent fields and data
            $this->selectedSemesterId = null;
            $this->semesters = []; 
            $this->selectedSectionId = null;
            $this->sections = [];
            $this->courses = [];
            $this->students = [];
            
            // Load Semesters (enables Semester dropdown in Blade)
            if ($this->selectedAcademicYearId) {
                $this->loadSemesters();
            }
        }
        
        // 2. Semester Changed (Middle of the chain)
        if ($property === 'selectedSemesterId') {
            // Clear dependent fields below it
            $this->selectedSectionId = null;
            $this->sections = []; 
            $this->courses = [];
            $this->students = [];
            
            // Load Sections and Students (if all set)
            if ($this->selectedAcademicYearId && $this->selectedSemesterId) {
                // loadSections enables the Section dropdown in Blade
                $this->loadSections(); 
                $this->loadStudents(); 
            }
        }
        
        // 3. Section Changed (End of the chain: only re-load students if context is complete)
        if ($property === 'selectedSectionId' && $this->selectedAcademicYearId && $this->selectedSemesterId) {
            $this->loadStudents();
        }
    }

    // --- Method to load available semesters (Filtered by Academic Year) ---
    public function loadSemesters()
    {
        $this->semesters = Semester::where('academic_year_id', $this->selectedAcademicYearId)
                                 ->get();
        if ($this->semesters->isEmpty()) {
             $this->selectedSemesterId = null;
        }
    }
    
    // --- Method to load available sections (Filtered by Academic Year) ---
    public function loadSections()
    {
        $this->sections = Section::where('academic_year_id', $this->selectedAcademicYearId)
                                 ->get();
        $this->courses = []; 
    }
    
    // --------------------------------------------------------------------
    // METHOD: LOADS STUDENTS FOR DISPLAY TABLE 
    // --------------------------------------------------------------------
    public function loadStudents()
    {
        // Require all three selections to be present
        if (!$this->selectedSectionId || !$this->selectedAcademicYearId || !$this->selectedSemesterId) {
            $this->students = collect();
            return;
        }
        
        // 1. Fetch student IDs from the enrollments table using ALL three filters
        $studentIds = Enrollment::where('section_id', $this->selectedSectionId)
            // Enrollment table uses the numeric Semester ID
            ->where('semester_id', $this->selectedSemesterId) 
            // Enrollment table uses the numeric Academic Year ID
           // ->where('academic_year_id', $this->selectedAcademicYearId) 
            ->pluck('student_id')
            ->unique();
        
        if ($studentIds->isEmpty()) {
            $this->students = collect();
            session()->flash('warning', 'No students found for this selection.');
            return;
        }

        // 2. Fetch the actual Student models
        $this->students = Student::whereIn('id', $studentIds)
            
            ->get();
    }

    // --- Method to load courses (Pre-assignment step) ---
    public function viewCourses()
    {
        $this->validate([
            'selectedAcademicYearId' => 'required|exists:academic_years,id',
            'selectedSemesterId' => 'required|exists:semesters,id', 
            'selectedSectionId' => 'required|exists:sections,id',
        ]);

        // Get the text ('1st') needed for the course_to_sections pivot table
        $selectedSemesterText = $this->getSemesterTextFromId($this->selectedSemesterId); 
        $section = Section::findOrFail($this->selectedSectionId);

        // Filter courses by pivot table fields (uses TEXT for semester)
        $this->courses = $section->courses()
            ->wherePivot('academic_year_id', $this->selectedAcademicYearId)
            ->wherePivot('semester', $selectedSemesterText) 
            ->get();
            
        if ($this->courses->isEmpty()) {
            session()->flash('warning', 'No courses found assigned to this section/term.');
        }
    }
   
    // --- Method to perform the final assignment ---
    public function assignStudentsToCourses()
    {
        $this->loadStudents();
        $this->viewCourses();

        if (empty($this->courses) || $this->students->isEmpty()) {
            session()->flash('error', 'Assignment failed: Check if courses or students are loaded.');
            return;
        }
        
        $selectedSemesterText = $this->getSemesterTextFromId($this->selectedSemesterId);
        $recordsToInsert = [];
        $now = Carbon::now();

        // Prepare the bulk insert data array
        foreach ($this->students as $student) {
            foreach ($this->courses as $course) {
                $recordsToInsert[] = [
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                  //  'section_id' => $this->selectedSectionId,
                    'academic_year_id' => $this->selectedAcademicYearId, 
                    'semester' => $selectedSemesterText, // Insert the TEXT value
                    'validated' => false,
                    'validated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        
        // Perform the bulk insert 
        try {
            DB::table('student_course')->insert($recordsToInsert);
            session()->flash('success', 'Successfully assigned ' . $this->students->count() . ' students to ' . count($this->courses) . ' courses.');
        } catch (\Exception $e) {
            \Log::error('Course assignment failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to assign courses due to a database error.');
        }
    }

    // --- RENDER Method ---
    public function render()
    {
        return view('livewire.assign-courses')
            ->extends('layouts.admin')
            ->section('content');
    }

    // --- HELPER FUNCTION: Maps Semester ID (e.g., 4) to Pivot Text ('1st') ---
    protected function getSemesterTextFromId(int $semesterId): string
    {
        // Fetches the semester record using the unique ID
        $semester = DB::table('semesters')->where('id', $semesterId)->first();

        if ($semester && $semester->name) {
            // Converts the full name ('First Semester') to the pivot text ('1st')
            $name = strtolower($semester->name);

            if (str_contains($name, 'first')) {
                return '1st';
            } elseif (str_contains($name, 'second')) {
                return '2nd';
            } elseif (str_contains($name, 'summer')) {
                return 'Summer'; 
            }
        }
        throw new \Exception("Could not map unique Semester ID $semesterId to pivot table text.");
    }
}