<?php

namespace App\Livewire\ClassManagement;

use Livewire\Component;
use App\Exports\ClassRecordExport;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ClassRecord extends Component
{
    use InteractsWithClassSelection;

    public $blockInfo = null;
    public $students = [];
    public $grades = [];
    public $remarks = [];
    public $finalized = false;

    public $gradeOptions = [];

    public function mount()
    {
        $this->gradeOptions = $this->buildGradeOptions();
        $this->mountInteractsWithClassSelection();
    }

    public function resetBlockData()
    {
        $this->blockInfo = null;
        $this->students = [];
        $this->grades = [];
        $this->remarks = [];
        $this->finalized = false;
    }

    public function loadBlockData()
    {
        $this->resetBlockData();

        $block = $this->currentBlock();

        if (!$block) {
            return;
        }

        $this->finalized = (bool) $block->finalized;

        $this->blockInfo = [
            'course_code' => $block->course?->code,
            'course_name' => $block->course?->name,
            'schedule_string' => $block->schedule_string,
            'room_name' => $block->room_name,
            'sections' => $block->section
                ? trim(($block->section->program?->name ?? '') . ' - ' . $block->section->name)
                : 'N/A',
        ];

        $roster = $block->students()->orderBy('last_name')->get();

        $this->students = $roster
            ->map(function ($student) {
                $section = $student->section;

                return [
                    'id' => $student->id,
                    'student_number' => $student->student_id,
                    'name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                    'section' => $section
                        ? trim(($section->program?->name ?? '') . ' - ' . $section->name)
                        : null,
                ];
            })
            ->values()
            ->toArray();

        foreach ($roster as $student) {
            $this->grades[$student->id] = $student->pivot->grade;
            $this->remarks[$student->id] = $student->pivot->remarks;
        }
    }

    public function saveGrades()
    {
        $block = $this->currentBlock();

        if (!$block) {
            session()->flash('error', 'Please select a class first.');
            return;
        }

        if ($this->finalized) {
            session()->flash('error', 'This class record is finalized and can no longer be edited.');
            return;
        }

        $rules = [];
        foreach ($this->students as $student) {
            $rules['grades.' . $student['id']] = ['nullable', 'string', Rule::in($this->gradeOptions)];
            $rules['remarks.' . $student['id']] = ['nullable', 'string', 'max:255'];
        }
        $this->validate($rules);

        $updatedCount = 0;

        DB::transaction(function () use ($block, &$updatedCount) {
            foreach ($this->students as $student) {
                $grade = trim($this->grades[$student['id']] ?? '');
                $remarks = trim($this->remarks[$student['id']] ?? '');

                $updated = $block->students()->updateExistingPivot($student['id'], [
                    'grade' => $grade === '' ? null : $grade,
                    'remarks' => $remarks === '' ? null : $remarks,
                ]);

                if ($updated) {
                    $updatedCount++;
                }
            }
        });

        $this->loadBlockData();
        session()->flash('message', "Class record saved. {$updatedCount} student record(s) updated.");
    }

    public function finalizeRecord()
    {
        $block = $this->currentBlock();

        if (!$block) {
            session()->flash('error', 'Please select a class first.');
            return;
        }

        $block->update(['finalized' => true]);
        $this->finalized = true;

        session()->flash('message', 'Class record finalized. Grades can no longer be edited.');
    }

    public function unfinalizeRecord()
    {
        $block = $this->currentBlock();

        if (!$block) {
            session()->flash('error', 'Please select a class first.');
            return;
        }

        $block->update(['finalized' => false]);
        $this->finalized = false;

        session()->flash('message', 'Class record unlocked for editing.');
    }

    public function exportExcel()
    {
        $block = $this->currentBlock();

        if (!$block) {
            session()->flash('error', 'Please select a class first.');
            return;
        }

        return (new ClassRecordExport($this->students, $this->grades, $this->remarks, $block))->download();
    }

    private function buildGradeOptions(): array
    {
        $fixedGrades = ['5.0', '3.5'];

        $descendingGrades = [];
        for ($i = 30; $i >= 10; $i--) {
            $descendingGrades[] = number_format($i / 10, 1);
        }

        $allNumerical = array_merge($fixedGrades, $descendingGrades);
        usort($allNumerical, fn ($a, $b) => (float) $b <=> (float) $a);

        $options = array_merge(array_unique($allNumerical), ['INC', 'DRP']);

        return array_values(array_filter($options));
    }

    public function render()
    {
        return view('livewire.class-management.class-record', [
            'academicYears' => $this->academicYears,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
