<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\AttendanceRosterExport;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\CourseBlock;
use App\Models\Student;
use App\Support\AttendanceToken;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class AttendanceManager extends Component
{
    public $academicYearId;
    public $semester = '1st';
    public $selectedBlockId;

    public $attendanceDate;
    public $token;
    public $tokenExpiresAt;
    public $qrDataUri;
    public $qrUrl;

    public $facultyId;
    public $academicYears = [];
    public $semesterOptions = ['1st', '2nd Semester', 'Summer'];
    public $assignedBlocks = [];
    public $roster = [];
    public $summary = [];

    public $showAddStudent = false;
    public $studentSearch = '';
    public $searchResults = [];
    public $addStudentId = null;
    public $addStudentName = '';

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

        $this->attendanceDate = today()->toDateString();

        $this->loadAssignedBlocks();
    }

    public function updatedAcademicYearId()
    {
        $this->loadAssignedBlocks();
    }

    public function updatedSemester()
    {
        $this->loadAssignedBlocks();
    }

    public function updatedSelectedBlockId()
    {
        $this->token = null;
        $this->tokenExpiresAt = null;
        $this->qrDataUri = null;
        $this->qrUrl = null;
        $this->roster = [];
        $this->summary = [];
        $this->attendanceDate = today()->toDateString();
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

    public function startSession()
    {
        if (!$this->selectedBlockId) {
            session()->flash('error', 'Please select a course block first.');
            return;
        }

        $this->attendanceDate = today()->toDateString();
        $this->generateQr();
        $this->loadRoster();
    }

    public function regenerateQr()
    {
        $this->generateQr();
        $this->loadRoster();
    }

    public function tick()
    {
        if ($this->token && $this->tokenExpiresAt) {
            if (now()->getTimestamp() >= strtotime($this->tokenExpiresAt)) {
                $this->generateQr();
            }
        }
        $this->loadRoster();
    }

    private function generateQr()
    {
        $ttl = 90;
        $this->token = AttendanceToken::generate((int) $this->selectedBlockId, $ttl);
        $this->tokenExpiresAt = now()->addSeconds($ttl)->toIso8601String();
        $this->qrUrl = route('attendance.checkin', ['token' => $this->token]);

        $this->qrDataUri = (new Builder(
            data: $this->qrUrl,
            writer: new PngWriter(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 10,
        ))->build()->getDataUri();
    }

    public function setAttendanceDate($date)
    {
        $this->attendanceDate = $date;
        $this->loadRoster();
    }

    public function markStatus($studentId, $status)
    {
        if (!$this->selectedBlockId || !$this->attendanceDate) {
            return;
        }

        $allowed = [
            AttendanceRecord::STATUS_PRESENT,
            AttendanceRecord::STATUS_LATE,
            AttendanceRecord::STATUS_ABSENT,
            AttendanceRecord::STATUS_EXCUSED,
        ];

        if (!in_array($status, $allowed, true)) {
            return;
        }

        AttendanceRecord::updateOrCreate(
            [
                'course_block_id' => (int) $this->selectedBlockId,
                'student_id' => (int) $studentId,
                'attendance_date' => $this->attendanceDate,
            ],
            ['status' => $status, 'checked_in_at' => now(), 'remarks' => 'Marked manually by faculty.']
        );

        $this->loadRoster();
    }

    public function clearStatus($studentId)
    {
        if (!$this->selectedBlockId || !$this->attendanceDate) {
            return;
        }

        AttendanceRecord::where('course_block_id', (int) $this->selectedBlockId)
            ->where('student_id', (int) $studentId)
            ->where('attendance_date', $this->attendanceDate)
            ->delete();

        $this->loadRoster();
    }

    public function loadRoster()
    {
        $data = $this->buildRosterData();

        $this->roster = $data['roster'];
        $this->summary = $data['summary'];
    }

    private function verifyOwnership(): bool
    {
        return (bool) CourseBlock::whereKey((int) $this->selectedBlockId)
            ->where('faculty_id', $this->facultyId)
            ->exists();
    }

    private function buildRosterData(): array
    {
        $roster = [];
        $summary = [];

        if (!$this->selectedBlockId || !$this->attendanceDate) {
            return compact('roster', 'summary');
        }

        $block = CourseBlock::with('students')->find($this->selectedBlockId);

        if (!$block) {
            return compact('roster', 'summary');
        }

        $records = AttendanceRecord::where('course_block_id', $block->id)
            ->where('attendance_date', $this->attendanceDate)
            ->get()
            ->keyBy('student_id');

        $students = $block->students()->get();

        $rosterStudentIds = $students->pluck('id')->all();

        $extraRecords = AttendanceRecord::where('course_block_id', $block->id)
            ->where('attendance_date', $this->attendanceDate)
            ->whereNotIn('student_id', $rosterStudentIds)
            ->with('student')
            ->get();

        foreach ($extraRecords as $record) {
            if ($record->student) {
                $students->push($record->student);
            }
        }

        $roster = $students
            ->map(function ($student) use ($records) {
                $record = $records->get($student->id);

                return [
                    'student_id' => $student->id,
                    'student_number' => $student->student_id,
                    'name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                    'status' => $record?->status,
                    'checked_in_at' => $record?->checked_in_at,
                    'remarks' => $record?->remarks,
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $summary = [
            'total' => count($roster),
            'present' => count(array_filter($roster, fn ($r) => $r['status'] === 'present')),
            'late' => count(array_filter($roster, fn ($r) => $r['status'] === 'late')),
            'absent' => count(array_filter($roster, fn ($r) => $r['status'] === 'absent')),
            'excused' => count(array_filter($roster, fn ($r) => $r['status'] === 'excused')),
        ];

        return compact('roster', 'summary');
    }

    public function updatedStudentSearch()
    {
        $this->searchStudents();
    }

    public function openAddStudent()
    {
        $this->showAddStudent = true;
        $this->studentSearch = '';
        $this->searchResults = [];
        $this->addStudentId = null;
        $this->addStudentName = '';
    }

    public function closeAddStudent()
    {
        $this->showAddStudent = false;
        $this->studentSearch = '';
        $this->searchResults = [];
        $this->addStudentId = null;
        $this->addStudentName = '';
    }

    public function searchStudents()
    {
        $this->searchResults = [];

        if (!$this->selectedBlockId || mb_strlen(trim($this->studentSearch)) < 2) {
            return;
        }

        $term = trim($this->studentSearch);

        $results = Student::where(function ($q) use ($term) {
            $q->where('student_id', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('middle_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
                ->orWhereRaw("CONCAT(last_name, ', ', first_name) LIKE ?", ["%{$term}%"]);
        })
            ->orderBy('last_name')
            ->limit(15)
            ->get(['id', 'student_id', 'first_name', 'middle_name', 'last_name']);

        $this->searchResults = $results->map(function ($student) {
            return [
                'id' => $student->id,
                'student_number' => $student->student_id,
                'name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                'has_record' => AttendanceRecord::where('course_block_id', (int) $this->selectedBlockId)
                    ->where('student_id', $student->id)
                    ->where('attendance_date', $this->attendanceDate)
                    ->exists(),
            ];
        })->toArray();
    }

    public function selectStudent($studentId)
    {
        $student = Student::find((int) $studentId);

        if (!$student) {
            return;
        }

        $this->addStudentId = (int) $student->id;
        $this->addStudentName = trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : ''));
    }

    public function addAttendance($status)
    {
        $allowed = [
            AttendanceRecord::STATUS_PRESENT,
            AttendanceRecord::STATUS_LATE,
            AttendanceRecord::STATUS_ABSENT,
            AttendanceRecord::STATUS_EXCUSED,
        ];

        if (!$this->selectedBlockId || !$this->attendanceDate || !$this->addStudentId || !in_array($status, $allowed, true)) {
            return;
        }

        if (!$this->verifyOwnership()) {
            session()->flash('error', 'You are not assigned to this class.');
            return;
        }

        $name = $this->addStudentName;

        AttendanceRecord::updateOrCreate(
            [
                'course_block_id' => (int) $this->selectedBlockId,
                'student_id' => (int) $this->addStudentId,
                'attendance_date' => $this->attendanceDate,
            ],
            ['status' => $status, 'checked_in_at' => now(), 'remarks' => 'Added manually by faculty.']
        );

        $this->closeAddStudent();
        $this->loadRoster();

        session()->flash('message', "Attendance recorded for {$name} on {$this->attendanceDate}.");
    }

    public function exportExcel()
    {
        if (!$this->selectedBlockId || !$this->attendanceDate) {
            session()->flash('error', 'Please select a class and date first.');
            return;
        }

        $block = CourseBlock::with(['course', 'academicYear', 'faculty'])->find($this->selectedBlockId);

        if (!$block) {
            session()->flash('error', 'Class not found.');
            return;
        }

        $data = $this->buildRosterData();

        return (new AttendanceRosterExport($data['roster'], $block, $this->attendanceDate))->download();
    }

    public function printRoster()
    {
        if (!$this->selectedBlockId || !$this->attendanceDate) {
            session()->flash('error', 'Please select a class and date first.');
            return;
        }

        return redirect()->route('attendance.print', [
            'course_block_id' => $this->selectedBlockId,
            'date' => $this->attendanceDate,
        ]);
    }

    public function render()
    {
        return view('livewire.attendance.attendance-manager', [
            'academicYears' => $this->academicYears,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
