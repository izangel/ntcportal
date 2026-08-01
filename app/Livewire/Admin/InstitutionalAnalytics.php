<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Exports\InstitutionalAnalyticsExport;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\CourseBlock;
use App\Models\Employee;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class InstitutionalAnalytics extends Component
{
    public $academicYearId;
    public $semester = '1st';
    public $programId = '';
    public $facultyId = '';

    public $academicYears = [];
    public $semesterOptions = ['1st', '2nd Semester', 'Summer'];
    public $programs = [];
    public $faculties = [];

    public $summary = [];
    public $byProgram = [];
    public $byFaculty = [];
    public $gradeDistribution = [];
    public $atRiskStudents = [];

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $this->programs = Program::orderBy('name')->get();

        $facultyIds = CourseBlock::distinct()->pluck('faculty_id');
        $this->faculties = Employee::whereIn('id', $facultyIds)
            ->orderBy('last_name')
            ->get(['id', 'last_name', 'first_name', 'mid_name']);

        $latestWithBlocks = $this->academicYears
            ->filter(fn ($year) => CourseBlock::where('academic_year_id', $year->id)->exists())
            ->first();

        $this->academicYearId = ($latestWithBlocks ?: $this->academicYears->first())->id;

        $this->loadAnalytics();
    }

    public function updatedAcademicYearId()
    {
        $this->loadAnalytics();
    }

    public function updatedSemester()
    {
        $this->loadAnalytics();
    }

    public function updatedProgramId()
    {
        $this->loadAnalytics();
    }

    public function updatedFacultyId()
    {
        $this->loadAnalytics();
    }

    private function filteredBlocks()
    {
        $query = CourseBlock::with(['course', 'faculty', 'section.program', 'sections.program'])
            ->when($this->academicYearId, fn ($q) => $q->where('academic_year_id', $this->academicYearId))
            ->when($this->semester, fn ($q) => $q->where('semester', $this->semester))
            ->when($this->facultyId, fn ($q) => $q->where('faculty_id', $this->facultyId));

        $blocks = $query->get();

        if ($this->programId) {
            $blocks = $blocks->filter(fn ($block) => in_array((int) $this->programId, $this->blockProgramIds($block)));
        }

        return $blocks->values();
    }

    private function blockProgramIds($block): array
    {
        $ids = [];

        foreach ($block->sections as $section) {
            if ($section->program_id) {
                $ids[] = (int) $section->program_id;
            }
        }

        if ($block->section && $block->section->program_id) {
            $ids[] = (int) $block->section->program_id;
        }

        return array_values(array_unique($ids));
    }

    public function loadAnalytics()
    {
        $this->summary = [];
        $this->byProgram = [];
        $this->byFaculty = [];
        $this->gradeDistribution = [];
        $this->atRiskStudents = [];

        $blocks = $this->filteredBlocks();

        if ($blocks->isEmpty()) {
            return;
        }

        $blockIds = $blocks->pluck('id');

        $pivots = DB::table('student_courseblock')
            ->whereIn('course_block_id', $blockIds)
            ->get();

        $records = AttendanceRecord::whereIn('course_block_id', $blockIds)->get();

        $pivotsByBlock = $pivots->groupBy('course_block_id');
        $recordsByBlock = $records->groupBy('course_block_id');

        // ---------- Summary ----------
        $summary = $this->aggregateOverBlocks($blockIds->all(), $pivotsByBlock, $recordsByBlock);

        $this->summary = [
            'classes' => $blocks->count(),
            'faculty' => $blocks->pluck('faculty_id')->unique()->count(),
            'students' => $summary['students'],
            'sessions' => $records->pluck('attendance_date')->unique()->count(),
            'present' => $summary['present'],
            'late' => $summary['late'],
            'absent' => $summary['absent'],
            'excused' => $summary['excused'],
            'total' => $summary['total'],
            'rate' => $summary['rate'],
            'grades_entered' => $summary['grades_entered'],
        ];

        // ---------- By Program ----------
        $programMap = [];
        foreach ($blocks as $block) {
            foreach ($this->blockProgramIds($block) as $programId) {
                $programMap[$programId][] = $block->id;
            }
        }

        $programNameMap = $this->programs->pluck('name', 'id');

        $this->byProgram = collect($programMap)
            ->map(function ($ids, $programId) use ($pivotsByBlock, $recordsByBlock, $programNameMap) {
                $data = $this->aggregateOverBlocks(array_values($ids), $pivotsByBlock, $recordsByBlock);

                return [
                    'program' => $programNameMap[$programId] ?? 'Unknown Program',
                    'classes' => count(array_unique($ids)),
                    'students' => $data['students'],
                    'rate' => $data['rate'],
                    'grades_entered' => $data['grades_entered'],
                ];
            })
            ->sortByDesc('students')
            ->values()
            ->toArray();

        // ---------- By Faculty ----------
        $facultyMap = [];
        foreach ($blocks as $block) {
            $facultyMap[$block->faculty_id][] = $block->id;
        }

        $facultyNameMap = $this->faculties
            ->mapWithKeys(fn ($faculty) => [$faculty->id => trim($faculty->last_name . ', ' . $faculty->first_name . ($faculty->mid_name ? ' ' . $faculty->mid_name : ''))]);

        $this->byFaculty = collect($facultyMap)
            ->map(function ($ids, $facultyId) use ($pivotsByBlock, $recordsByBlock, $facultyNameMap) {
                $data = $this->aggregateOverBlocks(array_values($ids), $pivotsByBlock, $recordsByBlock);

                return [
                    'faculty' => $facultyNameMap[$facultyId] ?? 'Unknown Faculty',
                    'classes' => count(array_unique($ids)),
                    'students' => $data['students'],
                    'rate' => $data['rate'],
                    'grades_entered' => $data['grades_entered'],
                ];
            })
            ->sortByDesc('classes')
            ->values()
            ->toArray();

        // ---------- Grade Distribution ----------
        $buckets = [
            '1.0 - 1.9' => 0,
            '2.0 - 2.9' => 0,
            '3.0 - 3.9' => 0,
            '4.0 - 5.0' => 0,
            'INC / DRP' => 0,
        ];

        $numericSum = 0;
        $numericCount = 0;

        foreach ($pivots as $pivot) {
            $grade = trim((string) ($pivot->grade ?? ''));

            if ($grade === '') {
                continue;
            }

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

        $this->gradeDistribution = [
            'graded' => $pivots->whereNotNull('grade')->where('grade', '!=', '')->count(),
            'average' => $numericCount > 0 ? round($numericSum / $numericCount, 2) : null,
            'buckets' => $buckets,
        ];

        // ---------- At-Risk Students ----------
        $recordsByStudent = $records->groupBy('student_id');

        $atRiskIds = [];

        foreach ($recordsByStudent as $studentId => $studentRecords) {
            $present = $studentRecords->where('status', 'present')->count();
            $late = $studentRecords->where('status', 'late')->count();
            $absent = $studentRecords->where('status', 'absent')->count();
            $excused = $studentRecords->where('status', 'excused')->count();
            $total = $present + $late + $absent + $excused;

            if ($total === 0) {
                continue;
            }

            $rate = round((($present + $late) / $total) * 100, 1);

            if ($rate < 80) {
                $atRiskIds[$studentId] = [
                    'total' => $total,
                    'absent' => $absent,
                    'rate' => $rate,
                ];
            }
        }

        if (!empty($atRiskIds)) {
            $students = Student::whereIn('id', array_keys($atRiskIds))->get(['id', 'student_id', 'first_name', 'last_name', 'middle_name']);

            $this->atRiskStudents = $students
                ->map(function ($student) use ($atRiskIds) {
                    $data = $atRiskIds[$student->id];

                    return [
                        'student_number' => $student->student_id,
                        'name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                        'total' => $data['total'],
                        'absent' => $data['absent'],
                        'rate' => $data['rate'],
                    ];
                })
                ->sortBy('rate')
                ->values()
                ->toArray();
        }
    }

    private function aggregateOverBlocks(array $blockIds, $pivotsByBlock, $recordsByBlock): array
    {
        $studentIds = [];
        $gradesEntered = 0;
        $present = $late = $absent = $excused = 0;

        foreach ($blockIds as $blockId) {
            foreach (($pivotsByBlock[$blockId] ?? collect()) as $pivot) {
                $studentIds[] = $pivot->student_id;

                if ($pivot->grade !== null && trim((string) $pivot->grade) !== '') {
                    $gradesEntered++;
                }
            }

            foreach (($recordsByBlock[$blockId] ?? collect()) as $record) {
                switch ($record->status) {
                    case 'present':
                        $present++;
                        break;
                    case 'late':
                        $late++;
                        break;
                    case 'absent':
                        $absent++;
                        break;
                    case 'excused':
                        $excused++;
                        break;
                }
            }
        }

        $total = $present + $late + $absent + $excused;

        return [
            'students' => count(array_unique($studentIds)),
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'excused' => $excused,
            'total' => $total,
            'rate' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : null,
            'grades_entered' => $gradesEntered,
        ];
    }

    public function exportExcel()
    {
        if (empty($this->byProgram) && empty($this->byFaculty)) {
            session()->flash('error', 'No data available to export for the selected period.');
            return;
        }

        return (new InstitutionalAnalyticsExport(
            $this->summary,
            $this->byProgram,
            $this->byFaculty,
            $this->gradeDistribution,
        ))->download();
    }

    public function render()
    {
        return view('livewire.admin.institutional-analytics', [
            'academicYears' => $this->academicYears,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
