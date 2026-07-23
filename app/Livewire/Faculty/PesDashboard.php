<?php

namespace App\Livewire\Faculty;

use Livewire\Component;
use App\Models\Employee;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;



class PesDashboard extends Component
{
    use WithPagination;

    public $search = '';
    public $academic_year_id = '';
    public $semester = '1st';

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
        if (in_array($property, ['academic_year_id', 'semester', 'search'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        // 1. Fetch active faculty IDs using the same flexible string parsing rules
        $activeFacultyIds = DB::table('course_blocks')
            ->when($this->academic_year_id, function($q) {
                $q->where('academic_year_id', $this->academic_year_id);
            })
            ->when($this->semester, function($q) {
                $q->where('semester', 'like', $this->semester . '%');
            })
            ->distinct()
            ->pluck('faculty_id');

        // 2. Query all employee profiles matching those scheduled faculty IDs
        // Ordered alphabetically by last name for scanning
        $faculties = Employee::whereIn('id', $activeFacultyIds)
            ->where(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('last_name', 'asc')
            ->with(['pesSubmissions' => function ($query) {
                $query->where('academic_year_id', $this->academic_year_id)
                      ->where('semester', 'like', $this->semester . '%');
            }])
            ->paginate(15);

        // 3. Keep a separate query block to find the currently logged-in teacher's personal status card
        $currentFaculty = Employee::where('user_id', auth()->id())->first();
        $mySubmission = null;
        if ($currentFaculty && $this->academic_year_id && $this->semester) {
            $mySubmission = $currentFaculty->pesSubmissions()
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', 'like', $this->semester . '%')
                ->first();
        }

        return view('livewire.faculty.pes-dashboard', [
            'academicYears'  => $academicYears,
            'faculties'      => $faculties,
            'mySubmission'   => $mySubmission,
            'currentFaculty' => $currentFaculty
        ]) ->extends('layouts.admin')
            ->section('content');
    }
}