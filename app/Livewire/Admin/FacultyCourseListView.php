<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\CourseBlock;
use App\Models\Employee; // Assuming this represents your faculty
use Livewire\WithPagination;

class FacultyCourseListView extends Component
{
    use WithPagination;
    
    // --- Selection State (Bound to View Filters) ---
    public $academicYearId;
    public $semester;
    
    // --- Internal State/Data ---
    public $academicYears = [];
    public $semesters = ['1st', '2nd', 'Summer'];
    
    // --- Search/Filter State ---
    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        // 1. Load all Academic Years for the dropdown filter
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        // 2. Set the current/most recent AY as the default filter
        if ($this->academicYears->isNotEmpty()) {
            $this->academicYearId = $this->academicYears->first()->id;
        }
        
        // 3. Set a default semester 
        $this->semester = '1st'; 
    }
    
    // Reset pagination when a filter changes
    public function updated($property)
    {
        if (in_array($property, ['academicYearId', 'semester', 'search', 'perPage'])) {
            $this->resetPage();
        }
    }
    
    public function getFacultyList()
    {
        // Guard clause: Return empty if essential filters are not set
        if (!$this->academicYearId || !$this->semester) {
            return Employee::paginate($this->perPage)->setCollection(collect()); 
        }
        
        // 1. Start with all faculty (Employees)
        $query = Employee::query();
        
        // 2. CRITICAL: Only include faculty who HAVE assigned blocks
        //    for the selected AY and Semester.
        $query->whereHas('courseBlocks', function ($q) {
            $q->where('academic_year_id', $this->academicYearId)
              ->where('semester', $this->semester);
        });

        // 3. Apply search filter on user's name or employee ID
        if ($this->search) {
            $query->where(function ($q) {
                // Search by User Name
                $q->whereHas('user', function ($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%');
                })
                // Search by Employee ID
                ->orWhere('employee_id', 'like', '%' . $this->search . '%');
            });
        }
        
        // 4. Fetch the faculty and paginate
        $faculty = $query->with([
                             'user', 
                             // Eager load only the relevant blocks for the current period
                             // Requires CourseBlock model to have relationships: course and section
                             'courseBlocks' => function ($q) {
                                 $q->where('academic_year_id', $this->academicYearId)
                                   ->where('semester', $this->semester)
                                   ->with(['course', 'section']); 
                             }
                         ])
                         ->orderBy('last_name')
                         ->paginate($this->perPage);

        return $faculty;
    }
    
    public function render()
    {
        return view('livewire.admin.faculty-course-list-view', [
            'facultyList' => $this->getFacultyList(),
        ])->extends('layouts.admin')
          ->section('content');
    }
}