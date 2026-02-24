<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{Student, Section, AcademicYear, SectionStudent};

class SectionAssignment extends Component
{
    // Filter properties
    public $academic_year_id;
    public $semester;
    public $section_id;

    // Selection properties
    public $selected_student_id;
    public $isAdding = false;
    public $confirmingDeletion = false;
    public $assignmentIdBeingDeleted;

public function render()
{
    return view('livewire.section-assignment', [
        'academicYears' => AcademicYear::all(),
        'sections'      => Section::all(),
        'semesters'     => ['1st Semester', '2nd Semester', 'Summer'],
        'assigned'      => $this->getAssignedStudents(),
        'allStudents'   => Student::orderBy('last_name')->get(),
    ])->layout('layouts.app'); // <--- Add this line here
}

    public function getAssignedStudents()
    {
        if (!$this->academic_year_id || !$this->semester || !$this->section_id) {
            return collect();
        }

        return SectionStudent::where('academic_year_id', $this->academic_year_id)
            ->where('semester', $this->semester)
            ->where('section_id', $this->section_id)
            ->with('student')
            ->get();
    }

    public function addStudent()
    {
        $this->validate(['selected_student_id' => 'required']);

        SectionStudent::firstOrCreate([
            'student_id'       => $this->selected_student_id,
            'section_id'       => $this->section_id,
            'academic_year_id' => $this->academic_year_id,
            'semester'         => $this->semester,
        ]);

        $this->reset(['selected_student_id', 'isAdding']);
        session()->flash('message', 'Student added successfully.');
    }

public function confirmDelete($id)
{
    $this->assignmentIdBeingDeleted = $id; // Added the $ here
    $this->confirmingDeletion = true;
}
public function deleteAssignment()
{
    if ($this->assignmentIdBeingDeleted) {
        SectionStudent::find($this->assignmentIdBeingDeleted)?->delete();
    }

    // RESET EVERYTHING
    $this->confirmingDeletion = false;
    $this->assignmentIdBeingDeleted = null; 

    session()->flash('message', 'Student removed.');
}
}