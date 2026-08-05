<?php

namespace App\Livewire\ClassManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\CourseBlock;

class ClassScheduleView extends Component
{
    public $academicYearId;
    public $semester = '1st';

    public $facultyId;
    public $academicYears = [];
    public $semesterOptions = ['1st', '2nd Semester', 'Summer'];

    public $schedule = [];
    public $timetable = [];
    public $classCount = 0;

    public $dayLabels = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        0 => 'Sunday',
    ];

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

        $this->loadSchedule();
    }

    public function updatedAcademicYearId()
    {
        $this->loadSchedule();
    }

    public function updatedSemester()
    {
        $this->loadSchedule();
    }

    public function loadSchedule()
    {
        $this->schedule = [];
        $this->timetable = [];
        $this->classCount = 0;

        if (!$this->facultyId || !$this->academicYearId || !$this->semester) {
            return;
        }

        $blocks = CourseBlock::where('faculty_id', $this->facultyId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester', $this->semester)
            ->with(['course', 'sections.program', 'sections'])
            ->get();

        $this->classCount = $blocks->count();

        foreach ($blocks as $block) {
            $base = [
                'course_code' => $block->course?->code,
                'course_name' => $block->course?->name,
                'sections' => $block->section
                    ? trim(($block->section->program?->name ?? '') . ' - ' . $block->section->name)
                    : 'N/A',
                'room_name' => $block->room_name,
            ];

            foreach (preg_split('/[,;]/', $block->schedule_string ?? '') as $part) {
                $part = trim($part);

                if (!preg_match('/^([A-Za-z]+)/', $part, $matches)) {
                    continue;
                }

                $days = $this->parseDayToken(strtoupper($matches[1]));

                if (empty($days)) {
                    continue;
                }

                $time = '';
                if (preg_match('/(\d{4})-(\d{4})/', $part, $timeMatches)) {
                    $time = $this->formatTime($timeMatches[1]) . '–' . $this->formatTime($timeMatches[2]);
                }

                foreach ($days as $day) {
                    $this->schedule[$day][] = array_merge($base, ['time' => $time]);
                }
            }
        }

        foreach ($this->schedule as $day => $entries) {
            usort($this->schedule[$day], fn ($a, $b) => $this->startHour($a['time']) <=> $this->startHour($b['time']));
        }

        ksort($this->schedule);

        $this->timetable = $this->buildTimetable($this->schedule);
    }

    private function buildTimetable(array $schedule): array
    {
        $times = [];
        foreach ($schedule as $entries) {
            foreach ($entries as $entry) {
                if ($entry['time'] !== '' && !in_array($entry['time'], $times, true)) {
                    $times[] = $entry['time'];
                }
            }
        }

        usort($times, fn ($a, $b) => $this->startHour($a) <=> $this->startHour($b));

        $rows = [];
        foreach ($times as $time) {
            $row = ['time' => $time, 'days' => []];

            foreach (array_keys($schedule) as $day) {
                $row['days'][$day] = array_values(array_filter(
                    $schedule[$day],
                    fn ($entry) => $entry['time'] === $time
                ));
            }

            $rows[] = $row;
        }

        $untimed = [];
        foreach ($schedule as $day => $entries) {
            foreach ($entries as $entry) {
                if ($entry['time'] === '') {
                    $untimed[$day][] = $entry;
                }
            }
        }

        if (!empty($untimed)) {
            $rows[] = ['time' => 'TBA', 'days' => $untimed];
        }

        return $rows;
    }

    private function parseDayToken(string $token): array
    {
        $dayNumbers = [];

        $fullNames = [
            'MON' => 1,
            'TUES' => 2,
            'TUE' => 2,
            'WED' => 3,
            'THURS' => 4,
            'THUR' => 4,
            'THU' => 4,
            'FRI' => 5,
            'SAT' => 6,
            'SUN' => 0,
        ];

        if (isset($fullNames[$token])) {
            return [$fullNames[$token]];
        }

        $chars = str_split($token);
        for ($i = 0; $i < count($chars); $i++) {
            $ch = $chars[$i];
            $next = $chars[$i + 1] ?? '';

            if ($ch === 'T' && $next === 'H') {
                $dayNumbers[] = 4;
                $i++;
            } elseif ($ch === 'S' && $next === 'U') {
                $dayNumbers[] = 0;
                $i++;
            } elseif ($ch === 'M') {
                $dayNumbers[] = 1;
            } elseif ($ch === 'T') {
                $dayNumbers[] = 2;
            } elseif ($ch === 'W') {
                $dayNumbers[] = 3;
            } elseif ($ch === 'F') {
                $dayNumbers[] = 5;
            } elseif ($ch === 'S') {
                $dayNumbers[] = 6;
            }
        }

        return array_values(array_unique($dayNumbers));
    }

    private function formatTime(string $hhmm): string
    {
        $hour = (int) substr($hhmm, 0, 2);
        $minute = substr($hhmm, 2, 2);

        return $hour . ':' . $minute;
    }

    private function startHour(string $timeString): int
    {
        if (preg_match('/(\d+):/', $timeString, $matches)) {
            return (int) $matches[1];
        }

        return 24;
    }

    public function render()
    {
        return view('livewire.class-management.class-schedule-view', [
            'academicYears' => $this->academicYears,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
