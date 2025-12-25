<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\StudentCourse;
use App\Models\AcademicYear;
use App\Models\Section;

class AssignedCoursesIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedAcademicYear = '';
    public $selectedSemester = '';
    public $selectedSection = '';
    
    public $selectedAssignments = [];
    public $selectAll = false;
    public $showIrregularForm = false; // Add this property

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedAcademicYear' => ['except' => ''],
        'selectedSemester' => ['except' => ''],
        'selectedSection' => ['except' => ''],
    ];

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedAssignments = $this->getQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedAssignments = [];
        }
    }

    public function toggleIrregularForm()
    {
        $this->showIrregularForm = !$this->showIrregularForm;
    }

    #[On('assignment-created')]
    public function refreshList()
    {
        // This method handles the event and triggers a re-render
    }

    public function validateSelected()
    {
        if (empty($this->selectedAssignments)) {
            return;
        }

        StudentCourse::whereIn('id', $this->selectedAssignments)->update([
            'validated' => true,
            'validated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        $this->selectedAssignments = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected assignments have been validated.');
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selectedAssignments = [];
        $this->selectAll = false;
    }

    public function updatingSelectedAcademicYear()
    {
        $this->resetPage();
    }

    public function updatingSelectedSemester()
    {
        $this->resetPage();
    }

    public function updatingSelectedSection()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $record = StudentCourse::find($id);
        if ($record) {
            $record->delete();
            session()->flash('success', 'Assignment deleted successfully.');
        }
    }

    public function getQuery()
    {
        return StudentCourse::with(['student', 'course', 'section', 'acadYear', 'validatedby'])
            ->when($this->search, function($q) {
                $q->whereHas('student', function($sq) {
                    $sq->where('first_name', 'like', '%'.$this->search.'%')
                       ->orWhere('last_name', 'like', '%'.$this->search.'%')
                       ->orWhere('student_id', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedAcademicYear, function($q) {
                $q->where('academic_year_id', $this->selectedAcademicYear);
            })
            ->when($this->selectedSemester, function($q) {
                $q->where('semester', $this->selectedSemester);
            })
            ->when($this->selectedSection, function($q) {
                $q->where('section_id', $this->selectedSection);
            });
    }

    public function render()
    {
        return view('livewire.assigned-courses-index', [
            'assignedCourses' => $this->getQuery()->orderBy('created_at', 'desc')->paginate(10),
            'academicYears' => AcademicYear::orderBy('start_year', 'desc')->get(),
            'sections' => Section::all(),
        ])->extends('layouts.admin')->section('content');
    }
}
