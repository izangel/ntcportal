<?php

namespace App\Livewire\ClassManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Exports\ClassRosterExport;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\CourseBlock;

class ClassStudents extends Component
{
    public $academicYearId;
    public $semester = '1st';
    public $selectedBlockId;

    public $facultyId;
    public $academicYears = [];
    public $semesterOptions = ['1st', '2nd Semester', 'Summer'];
    public $assignedBlocks = [];

    public $searchTerm = '';
    public $students = [];
    public $blockInfo = null;

    public function mount()
    {
        $user = Auth::user();
        $this->facultyId = $user->employee?->id;

        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        $latestWithBlocks = $this->academicYears
            ->filter(fn ($year) => CourseBlock::where('faculty_id', $this->facultyId)
                ->where('academic_year_id', $year->id)
                ->exists())
            ->first();

        $this->academicYearId = ($latestWithBlocks ?: $this->academicYears->first())->id;

        $this->loadAssignedBlocks();
    }

    public function updatedAcademicYearId()
    {
        $this->loadAssignedBlocks();
        $this->resetSelection();
    }

    public function updatedSemester()
    {
        $this->loadAssignedBlocks();
        $this->resetSelection();
    }

    public function updatedSelectedBlockId()
    {
        $this->searchTerm = '';
        $this->loadStudents();
    }

    public function updatedSearchTerm()
    {
        $this->loadStudents();
    }

    public function resetSelection()
    {
        $this->selectedBlockId = null;
        $this->searchTerm = '';
        $this->students = [];
        $this->blockInfo = null;
    }

    public function loadAssignedBlocks()
    {
        $this->assignedBlocks = [];

        if (!$this->facultyId || !$this->academicYearId || !$this->semester) {
            return;
        }

        $blocks = CourseBlock::where('faculty_id', $this->facultyId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester', $this->semester)
            ->with(['course', 'sections.program', 'academicYear'])
            ->get();

        $this->assignedBlocks = $blocks
            ->groupBy(fn ($block) => $block->course_id . '-' . $block->schedule_string)
            ->map(function ($group) {
                $first = $group->first();
                $sections = $group->map(fn ($b) => ($b->section->program->name ?? 'N/A') . '-' . ($b->section->name ?? 'N/A'))
                    ->unique()
                    ->sort()
                    ->implode(', ');

                return [
                    'id' => $first->id,
                    'course_code' => $first->course->code,
                    'course_name' => $first->course->name,
                    'schedule_string' => $first->schedule_string,
                    'room_name' => $first->room_name,
                    'sections' => $sections,
                    'student_count' => $first->students()->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function loadStudents()
    {
        $this->students = [];
        $this->blockInfo = null;

        if (!$this->selectedBlockId || !$this->verifyOwnership()) {
            return;
        }

        $block = CourseBlock::with(['course', 'section', 'academicYear', 'faculty'])->find($this->selectedBlockId);

        if (!$block) {
            return;
        }

        $this->blockInfo = [
            'course_code' => $block->course?->code,
            'course_name' => $block->course?->name,
            'schedule_string' => $block->schedule_string,
            'room_name' => $block->room_name,
            'sections' => $block->section
                ? trim(($block->section->program?->name ?? '') . ' - ' . $block->section->name)
                : 'N/A',
        ];

        $records = AttendanceRecord::where('course_block_id', $block->id)
            ->get()
            ->groupBy('student_id');

        $students = $block->students()->get();
        $rosterIds = $students->pluck('id')->all();

        $extraRecords = AttendanceRecord::where('course_block_id', $block->id)
            ->whereNotIn('student_id', $rosterIds)
            ->with('student')
            ->get();

        foreach ($extraRecords as $record) {
            if ($record->student) {
                $students->push($record->student);
            }
        }

        $term = trim($this->searchTerm);

        $this->students = $students
            ->map(function ($student) use ($records, $block) {
                $studentRecords = $records->get($student->id, collect());

                $present = $studentRecords->where('status', 'present')->count();
                $late = $studentRecords->where('status', 'late')->count();
                $absent = $studentRecords->where('status', 'absent')->count();
                $excused = $studentRecords->where('status', 'excused')->count();
                $total = $present + $late + $absent + $excused;

                $section = $student->section;

                return [
                    'id' => $student->id,
                    'student_number' => $student->student_id,
                    'name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                    'section' => $section
                        ? trim(($section->program?->name ?? '') . ' - ' . $section->name)
                        : null,
                    'gender' => $student->gender,
                    'email' => $student->email,
                    'fully_enrolled' => $student->is_fully_enrolled,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent,
                    'excused' => $excused,
                    'total' => $total,
                    'rate' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : null,
                ];
            })
            ->filter(function ($student) use ($term) {
                if ($term === '') {
                    return true;
                }

                return str_contains(mb_strtolower($student['name']), mb_strtolower($term))
                    || str_contains($student['student_number'], $term);
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function verifyOwnership(): bool
    {
        return (bool) CourseBlock::whereKey((int) $this->selectedBlockId)
            ->where('faculty_id', $this->facultyId)
            ->exists();
    }

    public function exportExcel()
    {
        if (!$this->selectedBlockId || empty($this->students)) {
            session()->flash('error', 'Please select a class with students first.');
            return;
        }

        $block = CourseBlock::with(['course', 'academicYear', 'faculty'])->find($this->selectedBlockId);

        if (!$block) {
            session()->flash('error', 'Class not found.');
            return;
        }

        return (new ClassRosterExport($this->students, $block))->download();
    }

    public function render()
    {
        return view('livewire.class-management.class-students', [
            'academicYears' => $this->academicYears,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
