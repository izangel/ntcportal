<?php

namespace App\Livewire\ClassManagement;

use Livewire\Component;
use App\Exports\StudentContactSheetExport;

class StudentContactSheet extends Component
{
    use InteractsWithClassSelection;

    public $blockInfo = null;
    public $students = [];

    public function mount()
    {
        $this->mountInteractsWithClassSelection();
    }

    public function resetBlockData()
    {
        $this->blockInfo = null;
        $this->students = [];
    }

    public function loadBlockData()
    {
        $this->resetBlockData();

        $block = $this->currentBlock();

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

        $roster = $block->students()->with('section.program')->orderBy('last_name')->get();

        $this->students = $roster
            ->map(function ($student) {
                $section = $student->section;

                return [
                    'id' => $student->id,
                    'student_number' => $student->student_id,
                    'name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                    'gender' => $student->gender,
                    'section' => $section
                        ? trim(($section->program?->name ?? '') . ' - ' . $section->name)
                        : null,
                    'email' => $student->email,
                    'birthday' => $student->birthday?->format('Y-m-d'),
                    'fully_enrolled' => $student->is_fully_enrolled,
                ];
            })
            ->values()
            ->toArray();
    }

    public function exportExcel()
    {
        $block = $this->currentBlock();

        if (!$block) {
            session()->flash('error', 'Please select a class first.');
            return;
        }

        return (new StudentContactSheetExport($this->students, $block))->download();
    }

    public function render()
    {
        return view('livewire.class-management.student-contact-sheet', [
            'academicYears' => $this->academicYears,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
