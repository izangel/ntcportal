<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\CourseBlock;
use App\Services\ObeDataCompleteness;

class ObeSubmissionOverview extends Component
{
    public $selectedAcademicYearId = null;
    public $selectedSemester = '';
    public $semesters = ['1st', '2nd', 'Summer'];

    public $academicYears = [];
    public $myBlocks = [];
    public $statusByFaculty = [];
    public $stats = [
        'blocks' => 0,
        'incomplete' => 0,
        'complete' => 0,
        'faculty' => 0,
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

        $this->loadData();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->loadData();
    }

    public function updatedSelectedSemester(): void
    {
        $this->loadData();
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

    public function loadData(): void
    {
        $this->reset('myBlocks', 'statusByFaculty', 'stats');

        $blocks = CourseBlock::query()
            ->with(['course', 'academicYear', 'faculty', 'students', 'sections'])
            ->whereNotNull('faculty_id')
            ->when($this->selectedAcademicYearId, fn ($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->when($this->selectedSemester, fn ($q) => $q->where('semester', $this->selectedSemester))
            ->get();

        $missingByBlock = ObeDataCompleteness::evaluateMany($blocks);

        $employeeId = (int) Auth::user()?->employee?->id;

        $this->stats['blocks'] = $blocks->count();
        $this->stats['faculty'] = $blocks->pluck('faculty_id')->unique()->count();

        // -- What the logged-in employee still needs to submit --
        $this->myBlocks = $blocks
            ->filter(fn ($block) => (int) $block->faculty_id === $employeeId)
            ->values()
            ->map(fn ($block) => $this->blockRow($block, $missingByBlock[$block->id] ?? []));

        // -- Submission status of all faculty --
        $grouped = $blocks->groupBy('faculty_id');

        $byFaculty = [];

        foreach ($grouped as $facultyId => $facultyBlocks) {
            $faculty = $facultyBlocks->first()->faculty;

            $items = $facultyBlocks->values()->map(function ($block) use ($missingByBlock, $employeeId) {
                $row = $this->blockRow($block, $missingByBlock[$block->id] ?? []);
                $row['is_mine'] = (int) $block->faculty_id === $employeeId;

                return $row;
            });

            $incomplete = $items->filter(fn ($item) => !$item['complete'])->count();

            $byFaculty[] = [
                'faculty_id' => (int) $facultyId,
                'faculty_name' => trim(($faculty->first_name ?? '') . ' ' . ($faculty->last_name ?? '')),
                'is_me' => (int) $facultyId === $employeeId,
                'incomplete' => $incomplete,
                'complete' => $items->count() - $incomplete,
                'blocks' => $items,
            ];
        }

        usort($byFaculty, fn ($a, $b) => $b['incomplete'] <=> $a['incomplete']);

        $this->statusByFaculty = collect($byFaculty)->values();

        $this->stats['incomplete'] = $this->statusByFaculty->sum('incomplete');
        $this->stats['complete'] = $this->statusByFaculty->sum('complete');
    }

    private function blockRow($block, array $missing): array
    {
        return [
            'id' => $block->id,
            'course_code' => $block->course?->code,
            'course_name' => $block->course?->name,
            'semester' => $block->semester,
            'sections' => $block->sections->pluck('name')->filter()->unique()->implode(', ') ?: '—',
            'schedule' => trim(($block->room_name ?? '') . ($block->schedule_string ? ' | ' . $block->schedule_string : '')),
            'student_count' => $block->students->count(),
            'missing' => $missing,
            'missing_labels' => collect($missing)
                ->map(fn ($key) => ObeDataCompleteness::labels()[$key] ?? $key)
                ->values(),
            'complete' => empty($missing),
            'accomplish_links' => $this->accomplishLinksFor($missing),
        ];
    }

    private function accomplishLinksFor(array $missing): array
    {
        $links = [];

        if (in_array(ObeDataCompleteness::MISSING_ASSESSMENT, $missing, true)) {
            $links[] = [
                'label' => 'Assessment Setup',
                'url' => route('faculty.assessment-tasks'),
                'icon' => 'fa-list-check',
            ];
        }

        if (in_array(ObeDataCompleteness::MISSING_SCORES, $missing, true)) {
            $links[] = [
                'label' => 'Assessment Scores',
                'url' => route('faculty.assessment-scores'),
                'icon' => 'fa-pen-to-square',
            ];
        }

        if (in_array(ObeDataCompleteness::MISSING_ATTAINMENT, $missing, true)) {
            $links[] = [
                'label' => 'CLO Attainment Report',
                'url' => route('attainment.index'),
                'icon' => 'fa-bullseye',
            ];
        }

        return $links;
    }

    public function render()
    {
        return view('livewire.admin.obe-submission-overview', [
            'isAdminView' => $this->isAdminView(),
        ])->extends('layouts.admin')
            ->section('content');
    }
}
