<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Employee;
use App\Models\AcademicYear;
use App\Models\PesSubmission;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class PesTracker extends Component
{
    use WithPagination;

    public $search = '';
    
    // Exact property matches for your layout bindings
    public $academic_year_id = ''; 
    public $semester = '1st'; // Default to first semester option

    public function mount()
    {
        // 1. Pull the explicit admin-selected values from the custom setting table
        $savedYearId = DB::table('system_settings')->where('key', 'pes_dashboard_year_id')->value('value');
        $savedSemester = DB::table('system_settings')->where('key', 'pes_dashboard_semester')->value('value');

        // 2. Fallback to active semester logic ONLY if settings are completely empty
        if ($savedYearId && $savedSemester) {
            $this->academic_year_id = $savedYearId;
            $this->semester = $savedSemester;
        } else {
            $activeSemester = \App\Models\Semester::where('is_active', 1)->first();
            if ($activeSemester) {
                $this->academic_year_id = $activeSemester->academic_year_id;
                $this->semester = str_contains($activeSemester->name, 'First') ? '1st' : (str_contains($activeSemester->name, 'Second') ? '2nd' : 'Summer');
            }
        }
    }

    public function updating($property)
    {
        // Reset pagination grids automatically when filters update
        if (in_array($property, ['academic_year_id', 'semester', 'search'])) {
            $this->resetPage();
        }
    }

   public function toggleSubmission($employeeId)
{
    if (!$this->academic_year_id || !$this->semester) {
        session()->flash('error', 'Please make sure both Year and Semester options are selected.');
        return;
    }

    // Pass ALL identifying fields into the first array (the lookup criteria)
    $submission = PesSubmission::firstOrCreate([
        'employee_id'      => $employeeId,
        'academic_year_id' => $this->academic_year_id,
        'semester'         => $this->semester, 
    ]);

    // Toggle the status
    $submission->is_submitted = !$submission->is_submitted;
    $submission->submitted_at = $submission->is_submitted ? now() : null;
    $submission->actioned_by_user_id = auth()->id();
    $submission->save();
}

    public function render()
{
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

    // 1. Fetch active faculty IDs using flexible string parsing
    $activeFacultyIds = DB::table('course_blocks')
        ->when($this->academic_year_id, function($q) {
            $q->where('academic_year_id', $this->academic_year_id);
        })
        ->when($this->semester, function($q) {
            // Flexible binding: '1st' matches '1st', '1st Sem', '1st Semester'
            // '2nd' matches '2nd', '2nd Sem', '2nd Semester'
            $q->where('semester', 'like', $this->semester . '%');
        })
        ->distinct()
        ->pluck('faculty_id');

    // 2. Query employee profiles matching those scheduled faculty IDs
    $faculties = Employee::whereIn('id', $activeFacultyIds)
        ->where(function ($query) {
            $query->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%');
        })
        ->orderBy('last_name', 'asc') // <-- Adds alphabetical sorting by last name
        ->with(['pesSubmissions' => function ($query) {
            $query->where('academic_year_id', $this->academic_year_id)
                  ->where('semester', 'like', $this->semester . '%');
        }])
        ->paginate(10);

    return view('livewire.admin.pes-tracker', [
        'academicYears' => $academicYears,
        'faculties' => $faculties
    ])->extends('layouts.admin')
            ->section('content');
    }
       
}