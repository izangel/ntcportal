<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AssignCourses extends Component
{
    // State Properties
    public $academicYears = [];
    public $sections = [];
    public $courses = [];
    
    public $selectedAcademicYearId = null;
    public $selectedSemester = null;
    public $selectedSectionId = null;
    
    // Lifecycle Method: Initial Load
    public function mount()
    {
        // Load academic years for the first dropdown
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
    }
    
    // Listener for when a selection is updated
    public function updated($property)
    {
        // Clear dependent data when parent selector changes
        if (in_array($property, ['selectedAcademicYearId', 'selectedSemester'])) {
            $this->sections = [];
            $this->selectedSectionId = null;
            $this->courses = [];
        }
        
        // Auto-load sections if Academic Year and Semester are selected
        if ($this->selectedAcademicYearId && $this->selectedSemester) {
            $this->loadSections();
        }
    }
    
    // Method to load available sections
    public function loadSections()
    {
        // Filter sections based on year/semester
        // We include sections with matching academic_year_id OR null (legacy/global sections)
        $this->sections = Section::where(function($query) {
                                    $query->where('academic_year_id', $this->selectedAcademicYearId)
                                          ->orWhereNull('academic_year_id');
                                 })
                                 ->get();
        $this->courses = [];
    }

    // Method to load courses for the selected section (Phase 2)
    public function viewCourses()
    {
        // 1. Validation remains correct
        $this->validate([
            'selectedAcademicYearId' => 'required|exists:academic_years,id', // Added exists rule
            'selectedSemester' => 'required|in:1st,2nd,Sum', // Assuming semesters are numbered
            'selectedSectionId' => 'required|exists:sections,id',
        ]);

        // 2. Find the Section
        $section = Section::findOrFail($this->selectedSectionId);

        // 3. Use the defined 'courses' relationship and apply filters
        $this->courses = $section->courses()
            // Filter courses by academic year ID on the pivot table
            // Assumes the pivot table (course_to_sections) has the column 'academic_year_id'
            ->wherePivot('academic_year_id', $this->selectedAcademicYearId)

            // Filter courses by semester on the pivot table
            // Assumes the pivot table (course_to_sections) has the column 'semester'
            ->wherePivot('semester', $this->selectedSemester)
            
            // Retrieve the results
            ->get();
    }
    
    // THE CORE LOGIC: Bulk Insert (Phase 3)
    public function assignStudentsToCourses()
    {
        // 1. Re-validate inputs and fetch courses one last time
        $this->viewCourses();

        if (empty($this->courses)) {
            session()->flash('error', 'No courses found to assign.');
            return;
        }

        // 2. Fetch all student IDs in the selected section
        $students = Student::where('section_id', $this->selectedSectionId)->pluck('id');
        
        if ($students->isEmpty()) {
            session()->flash('error', 'No students found in the selected section.');
            return;
        }

        $recordsToInsert = [];
        $now = Carbon::now();

        // 3. Prepare the bulk insert data array
        foreach ($students as $studentId) {
            foreach ($this->courses as $course) {
                $recordsToInsert[] = [
                    'student_id' => $studentId,
                    'course_id' => $course->id,
                    'section_id' => $this->selectedSectionId,
                    'academic_year_id' => $this->selectedAcademicYearId,
                    'semester' => $this->selectedSemester,
                    'validated' => false,
                    'validated_by' => null, // Blank by default when assigned
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // 4. Bulk Insert
        try {
            DB::beginTransaction();
            // insertOrIgnore prevents hitting the unique constraint on duplicate assignments
            $assignedCount = DB::table('student_course')->insertOrIgnore($recordsToInsert); 
            DB::commit();
            
            session()->flash('success', 
                "Successfully assigned $assignedCount records. Students in the section are now enrolled in the selected courses.");

            return redirect()->route('assigned_courses.index');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk Assignment Error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred during assignment. Check logs for details.');
        }
    }

    // RENDER Method (Handles Layout Integration)
    public function render()
    {
        return view('livewire.assign-courses')
            // This renders the component into the 'layouts.admin' file
            ->extends('layouts.admin')
            // This instructs Livewire to place the component's HTML into @yield('content')
           ->section('content');
    }
}