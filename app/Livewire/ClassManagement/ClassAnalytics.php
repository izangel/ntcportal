<?php

namespace App\Livewire\ClassManagement;

use Livewire\Component;
use App\Models\AttendanceRecord;

class ClassAnalytics extends Component
{
    use InteractsWithClassSelection;

    public $blockInfo = null;
    public $attendance = [];
    public $gradeStats = [];
    public $atRiskStudents = [];

    public function mount()
    {
        $this->mountInteractsWithClassSelection();
    }

    public function resetBlockData()
    {
        $this->blockInfo = null;
        $this->attendance = [];
        $this->gradeStats = [];
        $this->atRiskStudents = [];
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

        $this->loadAttendanceStats($block);
        $this->loadGradeStats($block);
        $this->loadAtRiskStudents($block);
    }

    private function loadAttendanceStats($block)
    {
        $records = AttendanceRecord::where('course_block_id', $block->id)->get();

        $sessions = $records->pluck('attendance_date')->unique()->sort();
        $present = $records->where('status', 'present')->count();
        $late = $records->where('status', 'late')->count();
        $absent = $records->where('status', 'absent')->count();
        $excused = $records->where('status', 'excused')->count();
        $total = $present + $late + $absent + $excused;

        $this->attendance = [
            'sessions' => $sessions->count(),
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'excused' => $excused,
            'total' => $total,
            'rate' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : null,
        ];
    }

    private function loadGradeStats($block)
    {
        $roster = $block->students()->get();

        $grades = $roster->map(fn ($student) => $student->pivot->grade)
            ->filter(fn ($grade) => !empty($grade));

        $buckets = [
            '1.0 - 1.9' => 0,
            '2.0 - 2.9' => 0,
            '3.0 - 3.9' => 0,
            '4.0 - 5.0' => 0,
            'INC / DRP' => 0,
        ];

        $numericSum = 0;
        $numericCount = 0;

        foreach ($grades as $grade) {
            if (in_array(strtoupper($grade), ['INC', 'DRP'])) {
                $buckets['INC / DRP']++;
                continue;
            }

            $value = (float) $grade;

            if ($value >= 1.0 && $value < 2.0) {
                $buckets['1.0 - 1.9']++;
            } elseif ($value >= 2.0 && $value < 3.0) {
                $buckets['2.0 - 2.9']++;
            } elseif ($value >= 3.0 && $value < 4.0) {
                $buckets['3.0 - 3.9']++;
            } else {
                $buckets['4.0 - 5.0']++;
            }

            $numericSum += $value;
            $numericCount++;
        }

        $this->gradeStats = [
            'total_students' => $roster->count(),
            'graded' => $grades->count(),
            'pending' => $roster->count() - $grades->count(),
            'average' => $numericCount > 0 ? round($numericSum / $numericCount, 2) : null,
            'buckets' => $buckets,
        ];
    }

    private function loadAtRiskStudents($block)
    {
        $records = AttendanceRecord::where('course_block_id', $block->id)
            ->get()
            ->groupBy('student_id');

        $students = $block->students()->get();

        $this->atRiskStudents = $students
            ->map(function ($student) use ($records) {
                $studentRecords = $records->get($student->id, collect());

                $present = $studentRecords->where('status', 'present')->count();
                $late = $studentRecords->where('status', 'late')->count();
                $absent = $studentRecords->where('status', 'absent')->count();
                $excused = $studentRecords->where('status', 'excused')->count();
                $total = $present + $late + $absent + $excused;

                return [
                    'id' => $student->id,
                    'name' => trim($student->last_name . ', ' . $student->first_name),
                    'student_number' => $student->student_id,
                    'total' => $total,
                    'absent' => $absent,
                    'rate' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : null,
                ];
            })
            ->filter(fn ($student) => $student['rate'] !== null && $student['rate'] < 80)
            ->sortBy('rate')
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.class-management.class-analytics', [
            'academicYears' => $this->academicYears,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
