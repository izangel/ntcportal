<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AssignCoursesIndividual extends Component
{
    // --- Data Properties ---
    public $academicYears = [];
    public $allStudents = []; 
    public $studentCourses = [];
    public $availableCourses = [];
    public $semesters = [];

    // --- Selection Properties ---
    public $selectedAcademicYearId = null;
    public $selectedSemesterId = null; 
    public $selectedStudentId = null; 
    
    // --- Temporary Property for the Dropdown ---
    public $courseToAdd = ''; 

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $this->allStudents = Student::orderBy('last_name')->get();
        
        $this->studentCourses = collect();
        $this->availableCourses = collect();
    }
    
    // --- Listener for when a selection is updated ---
    public function updated($property)
    {
        if (in_array($property, ['selectedAcademicYearId', 'selectedSemesterId', 'selectedStudentId'])) {
            $this->courseToAdd = '';
        }

        // 1. Academic Year Changed
        if ($property === 'selectedAcademicYearId') {
            $this->selectedSemesterId = null; $this->semesters = [];
            $this->selectedStudentId = null; $this->studentCourses = collect();
            $this->availableCourses = collect();
            if ($this->selectedAcademicYearId) { $this->loadSemesters(); }
        }
        
        // 2. Semester Changed
        if ($property === 'selectedSemesterId') {
            $this->selectedStudentId = null; $this->studentCourses = collect();
            $this->availableCourses = collect();
        }

        // 3. Student Changed OR Context is complete (Triggers course loading)
        if (in_array($property, ['selectedStudentId', 'selectedSemesterId', 'selectedAcademicYearId'])) {
            if ($this->selectedAcademicYearId && $this->selectedSemesterId && $this->selectedStudentId) {
                $this->loadStudentCourses();
            }
        }
    }

    // --- Loading Methods ---

    public function loadSemesters()
    {
        $this->semesters = Semester::where('academic_year_id', $this->selectedAcademicYearId)->get();
        if ($this->semesters->isEmpty()) { $this->selectedSemesterId = null; }
    }

    public function loadStudentCourses()
    {
        $this->validate([
            'selectedAcademicYearId' => 'required',
            'selectedSemesterId' => 'required',
            'selectedStudentId' => 'required',
        ]);

        $selectedSemesterText = $this->getSemesterTextFromId($this->selectedSemesterId);

        // 1. Fetch currently assigned course IDs
        $assignedCourseIds = DB::table('student_course')
            ->where('student_id', $this->selectedStudentId)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->where('semester', $selectedSemesterText)
            ->pluck('course_id')
            ->toArray();
        
        // 2. Fetch course details for the assigned list
        $this->studentCourses = Course::whereIn('id', $assignedCourseIds)->get();
        
        // 3. Determine available courses
        $this->availableCourses = Course::whereNotIn('id', $assignedCourseIds)->get(); 
        
        // Reset dropdown selection after loading the lists
        $this->courseToAdd = '';
    }

    // --- Action Methods ---

    public function removeCourse($courseId)
    {
        $removedCourse = $this->studentCourses->firstWhere('id', $courseId);
        
        if (!$removedCourse) {
            session()->flash('warning', 'Course not found in the assigned list.');
            return;
        }

        $this->studentCourses = $this->studentCourses->filter(fn ($course) => $course->id != $courseId);
        
        $this->availableCourses->push($removedCourse);
        $this->availableCourses = $this->availableCourses->sortBy('name');
        
        $this->courseToAdd = ''; 
        
        session()->flash('info', $removedCourse->name . ' removed from assigned list.');
    }

    public function addCourse($courseId) // 🚨 FIX: Accept courseId as an argument
    {
        // 🚨 FIX: No need to use $this->courseToAdd, use the passed $courseId directly
        // $courseId = $this->courseToAdd;

        if (empty($courseId)) { // Use empty() to check for '' or null
             session()->flash('error', 'Please select a course to add.');
            return;
        }

        $addedCourse = $this->availableCourses->firstWhere('id', $courseId);
        
        if ($addedCourse) {
            $this->studentCourses->push($addedCourse);
            $this->studentCourses = $this->studentCourses->sortBy('name');

            $this->availableCourses = $this->availableCourses->reject(fn ($course) => $course->id == $courseId);
            
            //$this->courseToAdd = ''; // You can remove this as the dropdown is now Alpine-controlled
            
            // Dispatch the browser event to force visual reset
            $this->dispatch('reset-field'); 
            
            session()->flash('success', $addedCourse->name . ' added successfully.');
        } else {
             session()->flash('error', 'The selected course is not available.');
        }
    }

    public function saveStudentCourses()
    {
        $this->validate([
            'selectedAcademicYearId' => 'required', 'selectedSemesterId' => 'required',
            'selectedStudentId' => 'required',
        ]);

        $selectedSemesterText = $this->getSemesterTextFromId($this->selectedSemesterId);
        $now = Carbon::now();

        DB::beginTransaction();
        try {
            DB::table('student_course')
                ->where('student_id', $this->selectedStudentId)
                ->where('academic_year_id', $this->selectedAcademicYearId)
                ->where('semester', $selectedSemesterText)
                ->delete();

            $recordsToInsert = [];
            foreach ($this->studentCourses as $course) {
                $recordsToInsert[] = [
                    'student_id' => $this->selectedStudentId, 'course_id' => $course->id,
                    'academic_year_id' => $this->selectedAcademicYearId, 
                    'semester' => $selectedSemesterText, 
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }

            if (!empty($recordsToInsert)) {
                DB::table('student_course')->insert($recordsToInsert);
            }
            
            DB::commit();
            session()->flash('success', 'Irregular student courses successfully updated.');
            
            $this->loadStudentCourses(); 

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Irregular course assignment failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to save student courses due to a database error.');
        }
    }
    
    // --- Helper ---
    protected function getSemesterTextFromId(int $semesterId): string
    {
        $semester = DB::table('semesters')->where('id', $semesterId)->first();
        if ($semester && $semester->name) {
            $name = strtolower($semester->name);
            if (str_contains($name, 'first')) { return '1st'; } 
            if (str_contains($name, 'second')) { return '2nd'; } 
            if (str_contains($name, 'summer')) { return 'Summer'; } 
        }
        throw new \Exception("Could not map unique Semester ID $semesterId to pivot table text.");
    }
    
    public function render()
    {
        return view('livewire.assign-courses-individual')
            ->extends('layouts.admin')
            ->section('content');
    }
}