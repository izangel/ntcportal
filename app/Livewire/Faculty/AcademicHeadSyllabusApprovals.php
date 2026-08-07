<?php

namespace App\Livewire\Faculty;

use App\Models\CourseSyllabus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AcademicHeadSyllabusApprovals extends Component
{
    public $pending = [];

    public $approved = [];

    public $revisions = [];

    public $signingId = null;

    public $signatureName = '';

    public function mount(): void
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $rows = CourseSyllabus::with(['courseBlock.course', 'courseBlock.faculty', 'courseBlock.academicYear', 'program'])
            ->whereNotNull('submitted_at')
            ->orderByDesc('program_head_reviewed_at')
            ->get()
            ->map(function ($syllabus) {
                $block = $syllabus->courseBlock;

                return [
                    'id' => $syllabus->id,
                    'program' => $syllabus->program?->name ?? '—',
                    'course_code' => $block->course->code ?? '—',
                    'course_name' => $block->course->name ?? '—',
                    'schedule' => $block->schedule_string ?? '—',
                    'semester' => $block->semester ?? '—',
                    'faculty' => optional($block->faculty)->full_name ?? '—',
                    'reviewed_at' => $syllabus->program_head_reviewed_at?->format('M d, Y h:i A') ?? '—',
                    'reviewed_by' => $syllabus->program_head_reviewed_by_name ?? '—',
                    'approved_at' => $syllabus->academic_head_approved_at?->format('M d, Y h:i A') ?? null,
                    'approved_by' => $syllabus->academic_head_approved_by_name ?? '—',
                    'block_id' => $block->id,
                    'program_id' => $syllabus->program_id,
                ];
            });

        $this->pending = $rows->filter(fn ($row) => is_null($row['approved_at']))->values()->toArray();
        $this->approved = $rows->filter(fn ($row) => !is_null($row['approved_at']))->values()->toArray();

        $this->revisions = CourseSyllabus::with(['courseBlock.course', 'courseBlock.faculty', 'courseBlock.academicYear', 'program'])
            ->whereNull('submitted_at')
            ->whereNotNull('revision_requested_at')
            ->orderByDesc('revision_requested_at')
            ->get()
            ->map(function ($syllabus) {
                $block = $syllabus->courseBlock;

                return [
                    'id' => $syllabus->id,
                    'program' => $syllabus->program?->name ?? '—',
                    'course_code' => $block->course->code ?? '—',
                    'course_name' => $block->course->name ?? '—',
                    'faculty' => optional($block->faculty)->full_name ?? '—',
                    'requested_by' => $syllabus->revision_requested_by_name ?? '—',
                    'requested_at' => $syllabus->revision_requested_at?->format('M d, Y h:i A') ?? '—',
                    'remarks' => $syllabus->revision_remarks ?? '—',
                    'block_id' => $block->id,
                    'program_id' => $syllabus->program_id,
                ];
            })
            ->values()
            ->toArray();
    }

    public function openApprove($id): void
    {
        $this->signingId = (int) $id;
        $employee = Auth::user()?->employee;
        $this->signatureName = $employee ? $employee->full_name : '';
    }

    public function cancelApprove(): void
    {
        $this->reset('signingId', 'signatureName');
    }

    public function confirmApprove(): void
    {
        $this->validate([
            'signingId' => 'required|integer',
            'signatureName' => 'required|string|min:3|max:255',
        ], [], [
            'signatureName' => 'full name',
        ]);

        $employee = Auth::user()?->employee;

        if (!$employee) {
            $this->addError('signatureName', 'Your employee account could not be resolved.');
            return;
        }

        $syllabus = CourseSyllabus::findOrFail($this->signingId);

        if (!$syllabus->submitted_at) {
            $this->reset('signingId', 'signatureName');
            return;
        }

        if ($syllabus->academic_head_approved_at) {
            $this->reset('signingId', 'signatureName');
            return;
        }

        $syllabus->update([
            'academic_head_approved_at' => now(),
            'academic_head_approved_by_id' => $employee->id,
            'academic_head_approved_by_name' => $this->signatureName,
        ]);

        $this->reset('signingId', 'signatureName');
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.faculty.academic-head-syllabus-approvals', [
            'pending' => $this->pending,
            'approved' => $this->approved,
            'revisions' => $this->revisions,
        ])->extends('layouts.admin')->section('content');
    }
}