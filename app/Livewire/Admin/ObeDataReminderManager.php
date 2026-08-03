<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\AcademicYear;
use App\Models\CourseBlock;
use App\Models\Employee;
use App\Notifications\ObeDataReminder;
use App\Services\ObeDataCompleteness;

class ObeDataReminderManager extends Component
{
    public $selectedAcademicYearId = null;
    public $selectedSemester = '';
    public $semesters = ['1st', '2nd', 'Summer'];

    public $academicYears = [];
    public $blocksByFaculty = [];
    public $stats = [
        'blocks' => 0,
        'incomplete' => 0,
        'complete' => 0,
        'faculty' => 0,
        'reminders_sent' => 0,
    ];

    public function mount(): void
    {
        $this->academicYears = AcademicYear::orderByDesc('start_year')->get();

        $latestAyWithBlocks = AcademicYear::whereIn('id', CourseBlock::pluck('academic_year_id')->filter())
            ->orderByDesc('start_year')
            ->value('id');

        $this->selectedAcademicYearId = $latestAyWithBlocks
            ? (string) $latestAyWithBlocks
            : ($this->academicYears->first()?->id ?? null);

        $this->loadBlocks();
    }

    private function isAdminView(): bool
    {
        $user = Auth::user();

        return $user && ($user->hasRole('admin')
            || $user->hasRole('academic_head')
            || $user->hasRole('hr')
            || $user->hasRole('registrar')
            || $user->hasRole('program_head_shs'));
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->loadBlocks();
    }

    public function updatedSelectedSemester(): void
    {
        $this->loadBlocks();
    }

    public function loadBlocks(): void
    {
        $this->reset('blocksByFaculty', 'stats');

        $query = CourseBlock::query()
            ->with(['course', 'academicYear', 'faculty', 'students'])
            ->whereNotNull('faculty_id');

        if ($this->selectedAcademicYearId) {
            $query->where('academic_year_id', $this->selectedAcademicYearId);
        }

        if ($this->selectedSemester) {
            $query->where('semester', $this->selectedSemester);
        }

        if (!$this->isAdminView()) {
            $query->where('faculty_id', Auth::user()?->employee?->id);
        }

        $blocks = $query->get();

        $missingByBlock = ObeDataCompleteness::evaluateMany($blocks);

        $grouped = $blocks->groupBy('faculty_id');

        $this->stats['blocks'] = $blocks->count();
        $this->stats['faculty'] = $grouped->count();

        $byFaculty = [];

        foreach ($grouped as $facultyId => $facultyBlocks) {
            $faculty = $facultyBlocks->first()->faculty;

            $items = $facultyBlocks->map(function ($block) use ($missingByBlock) {
                $missing = $missingByBlock[$block->id] ?? [];

                if (empty($missing)) {
                    $this->stats['complete']++;
                } else {
                    $this->stats['incomplete']++;
                }

                return [
                    'id' => $block->id,
                    'course_code' => $block->course->code,
                    'course_name' => $block->course->name,
                    'semester' => $block->semester,
                    'sections' => $block->sections->pluck('name')->filter()->unique()->implode(', ') ?: '—',
                    'schedule' => trim($block->room_name . ($block->schedule_string ? ' | ' . $block->schedule_string : '')),
                    'student_count' => $block->students->count(),
                    'missing' => $missing,
                    'missing_labels' => collect($missing)
                        ->map(fn ($key) => ObeDataCompleteness::labels()[$key] ?? $key)
                        ->values(),
                    'complete' => empty($missing),
                    'action_url' => $this->actionUrlFor($missing),
                ];
            })->values();

            $byFaculty[] = [
                'faculty_id' => $facultyId,
                'faculty_name' => trim(($faculty->first_name ?? '') . ' ' . ($faculty->last_name ?? '')),
                'incomplete' => $items->filter(fn ($i) => !$i['complete'])->count(),
                'blocks' => $items,
            ];
        }

        usort($byFaculty, fn ($a, $b) => $a['incomplete'] <=> $b['incomplete']);

        $this->blocksByFaculty = collect($byFaculty)->values();
    }

    private function actionUrlFor(array $missing): string
    {
        if (in_array(ObeDataCompleteness::MISSING_SCORES, $missing, true)) {
            return route('faculty.assessment-scores');
        }
        if (in_array(ObeDataCompleteness::MISSING_ASSESSMENT, $missing, true)) {
            return route('faculty.assessment-tasks');
        }
        if (in_array(ObeDataCompleteness::MISSING_ATTAINMENT, $missing, true)) {
            return route('attainment.index');
        }

        return route('faculty.obe.course-dashboard');
    }

    public function sendReminder(int $blockId): void
    {
        $block = CourseBlock::with(['course', 'academicYear', 'faculty.user', 'students'])
            ->findOrFail($blockId);

        if (!$this->isAdminView() && $block->faculty_id !== (int) Auth::user()?->employee?->id) {
            abort(403);
        }

        $missing = ObeDataCompleteness::missing($block);

        if (empty($missing)) {
            session()->flash('obe-reminder-message', "Block #{$blockId} ({$block->course->code}) is already complete.");
            return;
        }

        $user = $block->faculty?->user;

        if (!$user) {
            session()->flash('obe-reminder-error', 'This block has no linked faculty user account to notify.');
            return;
        }

        $user->notify(new ObeDataReminder($block, $missing));

        $this->stats['reminders_sent']++;
        session()->flash('obe-reminder-message', "Reminder sent to {$block->faculty->first_name} {$block->faculty->last_name} for {$block->course->code}.");
    }

    public function sendRemindersForFaculty(int $facultyId): void
    {
        $blocks = CourseBlock::with(['course', 'academicYear', 'faculty.user', 'students'])
            ->where('faculty_id', $facultyId)
            ->when($this->selectedAcademicYearId, fn ($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->when($this->selectedSemester, fn ($q) => $q->where('semester', $this->selectedSemester))
            ->get();

        $missingByBlock = ObeDataCompleteness::evaluateMany($blocks);

        $sent = 0;

        foreach ($blocks as $block) {
            $missing = $missingByBlock[$block->id] ?? [];

            if (empty($missing) || !$block->faculty?->user) {
                continue;
            }

            $block->faculty->user->notify(new ObeDataReminder($block, $missing));
            $sent++;
        }

        $this->stats['reminders_sent'] += $sent;
        session()->flash('obe-reminder-message', "Sent {$sent} reminder(s) to this faculty.");
    }

    public function sendAllReminders(): void
    {
        $query = CourseBlock::with(['course', 'academicYear', 'faculty.user', 'students'])
            ->whereNotNull('faculty_id');

        if ($this->selectedAcademicYearId) {
            $query->where('academic_year_id', $this->selectedAcademicYearId);
        }

        if ($this->selectedSemester) {
            $query->where('semester', $this->selectedSemester);
        }

        $blocks = $query->get();

        $missingByBlock = ObeDataCompleteness::evaluateMany($blocks);

        $sent = 0;

        foreach ($blocks as $block) {
            $missing = $missingByBlock[$block->id] ?? [];

            if (empty($missing) || !$block->faculty?->user) {
                continue;
            }

            $already = DatabaseNotification::query()
                ->where('notifiable_id', $block->faculty->user->id)
                ->where('type', ObeDataReminder::class)
                ->whereNull('read_at')
                ->where('data->course_block_id', $block->id)
                ->exists();

            if ($already) {
                continue;
            }

            $block->faculty->user->notify(new ObeDataReminder($block, $missing));
            $sent++;
        }

        $this->stats['reminders_sent'] += $sent;
        session()->flash('obe-reminder-message', "Sent {$sent} new reminder(s) for incomplete blocks.");

        $this->loadBlocks();
    }

    public function render()
    {
        return view('livewire.admin.obe-data-reminder-manager', [
            'isAdminView' => $this->isAdminView(),
        ])->extends('layouts.admin')
            ->section('content');
    }
}
