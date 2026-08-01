<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\CourseBlock;

class StudentAttendanceHistory extends Component
{
    public $selectedAcademicYear;
    public $selectedSemester = '1st';
    public $semesterOptions = ['1st', '2nd Semester', 'Summer'];

    public function mount()
    {
        $activeAY = AcademicYear::where('is_active', true)->first();

        $this->selectedAcademicYear = $activeAY ? $activeAY->id : AcademicYear::orderBy('start_year', 'desc')->value('id');
    }

    public function render()
    {
        $student = Auth::user()->student;
        $blocks = collect();
        $overall = [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'excused' => 0,
            'total' => 0,
            'rate' => null,
        ];

        if ($student && $this->selectedAcademicYear) {
            $studentBlockIds = $student->courseBlocks()
                ->where('course_blocks.academic_year_id', $this->selectedAcademicYear)
                ->where('course_blocks.semester', $this->selectedSemester)
                ->pluck('course_blocks.id');

            $records = AttendanceRecord::where('student_id', $student->id)
                ->whereIn('course_block_id', $studentBlockIds)
                ->get()
                ->groupBy('course_block_id');

            $blocks = CourseBlock::with(['course', 'faculty', 'academicYear'])
                ->whereIn('id', $studentBlockIds)
                ->orderBy('course_blocks.id')
                ->get()
                ->map(function ($block) use ($records) {
                    $blockRecords = $records->get($block->id, collect());

                    $stats = [
                        'present' => $blockRecords->where('status', 'present')->count(),
                        'late' => $blockRecords->where('status', 'late')->count(),
                        'absent' => $blockRecords->where('status', 'absent')->count(),
                        'excused' => $blockRecords->where('status', 'excused')->count(),
                        'total' => $blockRecords->count(),
                    ];

                    $stats['rate'] = $stats['total'] > 0
                        ? round((($stats['present'] + $stats['late']) / $stats['total']) * 100, 1)
                        : null;

                    return [
                        'block_id' => $block->id,
                        'course_code' => $block->course?->code,
                        'course_name' => $block->course?->name,
                        'faculty_name' => $block->faculty ? trim($block->faculty->first_name . ' ' . $block->faculty->last_name) : 'TBA',
                        'schedule_string' => $block->schedule_string,
                        'room_name' => $block->room_name,
                        'stats' => $stats,
                        'records' => $blockRecords
                            ->sortByDesc('attendance_date')
                            ->values()
                            ->map(fn ($r) => [
                                'date' => $r->attendance_date->toDateString(),
                                'status' => $r->status,
                                'checked_in_at' => $r->checked_in_at,
                            ]),
                    ];
                })
                ->values();

            foreach ($blocks as $block) {
                foreach (['present', 'late', 'absent', 'excused'] as $key) {
                    $overall[$key] += $block['stats'][$key];
                }
            }
            $overall['total'] = $overall['present'] + $overall['late'] + $overall['absent'] + $overall['excused'];
            $overall['rate'] = $overall['total'] > 0
                ? round((($overall['present'] + $overall['late']) / $overall['total']) * 100, 1)
                : null;
        }

        return view('livewire.attendance.student-attendance-history', [
            'academicYears' => AcademicYear::orderBy('start_year', 'desc')->get(),
            'blocks' => $blocks,
            'overall' => $overall,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
