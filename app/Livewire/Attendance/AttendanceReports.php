<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Exports\AttendanceSheetExport;
use App\Exports\AttendanceSummaryExport;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\CourseBlock;
use App\Models\Semester;

class AttendanceReports extends Component
{
    public $academicYearId;
    public $semester = '1st';
    public $selectedBlockId;

    public $facultyId;
    public $academicYears = [];
    public $semesterOptions = ['1st', '2nd Semester', 'Summer'];
    public $assignedBlocks = [];

    public $reportType = 'sheet';
    public $dateFrom;
    public $dateTo;

    public $generated = false;
    public $sheetDates = [];
    public $sheetRows = [];
    public $summaryRows = [];
    public $summaryStats = [];

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
        $this->resetReport();
    }

    public function updatedSemester()
    {
        $this->loadAssignedBlocks();
        $this->resetReport();
    }

    public function updatedSelectedBlockId()
    {
        $this->resetReport();
        $this->setDefaultDateRange();
    }

    public function updatedReportType()
    {
        $this->resetReport();
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
            ->with(['course', 'section.program', 'academicYear'])
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

    public function setDefaultDateRange()
    {
        if (!$this->selectedBlockId) {
            return;
        }

        $block = CourseBlock::find($this->selectedBlockId);

        if (!$block) {
            return;
        }

        $semester = Semester::where('academic_year_id', $block->academic_year_id)
            ->get()
            ->first(fn ($s) => $this->semesterMatches($s->name, $block->semester));

        if ($semester) {
            $this->dateFrom = $semester->start_date->toDateString();
            $this->dateTo = $semester->end_date->toDateString();
            return;
        }

        $earliest = AttendanceRecord::where('course_block_id', $block->id)
            ->min('attendance_date');

        $this->dateTo = today()->toDateString();
        $this->dateFrom = $earliest ?: today()->subDays(60)->toDateString();
    }

    private function semesterMatches(string $dbName, string $blockSemester): bool
    {
        $a = strtolower($dbName);
        $b = strtolower($blockSemester);

        if (str_contains($b, '1st') || str_contains($b, 'first')) {
            return str_contains($a, 'first') || str_contains($a, '1st');
        }

        if (str_contains($b, '2nd') || str_contains($b, 'second')) {
            return str_contains($a, 'second') || str_contains($a, '2nd');
        }

        if (str_contains($b, 'summer')) {
            return str_contains($a, 'summer');
        }

        return false;
    }

    public function resetReport()
    {
        $this->generated = false;
        $this->sheetDates = [];
        $this->sheetRows = [];
        $this->summaryRows = [];
        $this->summaryStats = [];
    }

    public function generate()
    {
        if (!$this->selectedBlockId) {
            session()->flash('error', 'Please select a class first.');
            return;
        }

        $this->reportType === 'summary'
            ? $this->generateSummary()
            : $this->generateSheet();
    }

    private function generateSheet(): void
    {
        $this->sheetDates = [];
        $this->sheetRows = [];

        if (!$this->dateFrom || !$this->dateTo) {
            $this->generated = false;
            return;
        }

        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->startOfDay();

        if ($to->lt($from)) {
            session()->flash('error', 'The end date must be after the start date.');
            $this->generated = false;
            return;
        }

        $block = CourseBlock::find($this->selectedBlockId);

        if (!$block) {
            $this->generated = false;
            return;
        }

        $records = AttendanceRecord::where('course_block_id', $block->id)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $dayNumbers = $this->parseDaysFromSchedule($block->schedule_string ?? '');
        $recordDates = $records->pluck('attendance_date')->map(fn ($d) => $d->toDateString())->unique()->all();

        $dates = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $ds = $d->toDateString();
            if (in_array($d->dayOfWeek, $dayNumbers) || in_array($ds, $recordDates)) {
                $dates[] = $ds;
            }
        }

        $students = $block->students()->get();
        $rosterIds = $students->pluck('id')->all();

        $extraRecords = AttendanceRecord::where('course_block_id', $block->id)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('student_id', $rosterIds)
            ->with('student')
            ->get();

        foreach ($extraRecords as $record) {
            if ($record->student) {
                $students->push($record->student);
            }
        }

        $recordsByStudent = $records->groupBy('student_id');

        $rows = $students->map(function ($student) use ($recordsByStudent, $dates) {
            $byDate = $recordsByStudent->get($student->id, collect())
                ->keyBy(fn ($r) => $r->attendance_date->toDateString());

            $perDate = [];
            $present = $late = $absent = $excused = 0;

            foreach ($dates as $date) {
                $status = $byDate->get($date)?->status;
                $perDate[$date] = $status;

                match ($status) {
                    'present' => $present++,
                    'late' => $late++,
                    'absent' => $absent++,
                    'excused' => $excused++,
                    default => null,
                };
            }

            $total = $present + $late + $absent + $excused;

            return [
                'student_number' => $student->student_id,
                'name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                'per_date' => $perDate,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'excused' => $excused,
                'total' => $total,
                'rate' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : null,
            ];
        })
            ->sortBy('name')
            ->values()
            ->toArray();

        $this->sheetDates = $dates;
        $this->sheetRows = $rows;
        $this->generated = true;
    }

    private function generateSummary(): void
    {
        $this->summaryRows = [];
        $this->summaryStats = [];

        $block = CourseBlock::find($this->selectedBlockId);

        if (!$block) {
            $this->generated = false;
            return;
        }

        $records = AttendanceRecord::where('course_block_id', $block->id)->get();

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

        $recordsByStudent = $records->groupBy('student_id');

        $rows = $students->map(function ($student) use ($recordsByStudent) {
            $studentRecords = $recordsByStudent->get($student->id, collect());

            $present = $studentRecords->where('status', 'present')->count();
            $late = $studentRecords->where('status', 'late')->count();
            $absent = $studentRecords->where('status', 'absent')->count();
            $excused = $studentRecords->where('status', 'excused')->count();
            $total = $present + $late + $absent + $excused;

            return [
                'student_number' => $student->student_id,
                'name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'excused' => $excused,
                'total' => $total,
                'rate' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : null,
            ];
        })
            ->sortBy('name')
            ->values()
            ->toArray();

        $this->summaryRows = $rows;
        $this->summaryStats = [
            'sessions' => $records->pluck('attendance_date')->unique()->count(),
            'below_threshold' => count(array_filter($rows, fn ($r) => ($r['rate'] ?? 100) < 80)),
        ];
        $this->generated = true;
    }

    private function parseDaysFromSchedule(string $schedule): array
    {
        $dayNumbers = [];

        foreach (preg_split('/[,;]/', $schedule) as $part) {
            if (!preg_match('/^([A-Za-z]+)/', trim($part), $matches)) {
                continue;
            }

            $token = strtoupper($matches[1]);

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
                $dayNumbers[] = $fullNames[$token];
                continue;
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
        }

        return array_values(array_unique($dayNumbers));
    }

    public function exportSheet()
    {
        if (!$this->generated || empty($this->sheetDates)) {
            session()->flash('error', 'Generate the attendance sheet first.');
            return;
        }

        $block = CourseBlock::with(['course', 'academicYear', 'faculty'])->find($this->selectedBlockId);

        if (!$block) {
            session()->flash('error', 'Class not found.');
            return;
        }

        return (new AttendanceSheetExport($this->sheetRows, $this->sheetDates, $block, $this->dateFrom, $this->dateTo))->download();
    }

    public function exportSummary()
    {
        if (!$this->generated || empty($this->summaryRows)) {
            session()->flash('error', 'Generate the summary first.');
            return;
        }

        $block = CourseBlock::with(['course', 'academicYear', 'faculty'])->find($this->selectedBlockId);

        if (!$block) {
            session()->flash('error', 'Class not found.');
            return;
        }

        return (new AttendanceSummaryExport($this->summaryRows, $block))->download();
    }

    public function render()
    {
        return view('livewire.attendance.attendance-reports', [
            'academicYears' => $this->academicYears,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
