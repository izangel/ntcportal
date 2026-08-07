<?php

namespace App\Livewire\Faculty;

use App\Models\CourseSyllabus;
use App\Models\ProgramHead;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProgramHeadSyllabusReviews extends Component
{
    public $pending = [];

    public $signingId = null;

    public $signatureName = '';

    public $requestingId = null;

    public $remarks = '';

    public function mount(): void
    {
        $this->loadPending();
    }

    private function myProgramIds(): array
    {
        $employeeId = Auth::user()?->employee?->id;

        if (!$employeeId) {
            return [];
        }

        return ProgramHead::active()
            ->where('employee_id', $employeeId)
            ->pluck('program_id')
            ->all();
    }

    private function loadPending(): void
    {
        $programIds = $this->myProgramIds();
        $myEmployeeId = Auth::user()?->employee?->id;

        $rows = CourseSyllabus::with(['courseBlock.course', 'courseBlock.faculty', 'courseBlock.academicYear', 'program'])
            ->whereIn('program_id', $programIds)
            ->whereNotNull('submitted_at')
            ->whereNull('program_head_reviewed_at')
            // Block self-review: the head cannot review a syllabus they authored.
            ->whereHas('courseBlock', function ($q) use ($myEmployeeId) {
                $q->where('faculty_id', '!=', $myEmployeeId);
            })
            ->orderByDesc('submitted_at')
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
                    'submitted_at' => $syllabus->submitted_at?->format('M d, Y h:i A') ?? '—',
                    'block_id' => $block->id,
                    'program_id' => $syllabus->program_id,
                    'revision_remarks' => $syllabus->revision_remarks,
                    'revision_requested_by' => $syllabus->revision_requested_by_name,
                    'revision_requested_at' => $syllabus->revision_requested_at?->format('M d, Y h:i A'),
                ];
            });

        $this->pending = $rows->values()->toArray();
    }

    public function openReview($id): void
    {
        $this->signingId = (int) $id;
        $employee = Auth::user()?->employee;
        $this->signatureName = $employee ? $employee->full_name : '';
    }

    public function cancelReview(): void
    {
        $this->reset('signingId', 'signatureName');
    }

    public function confirmReview(): void
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

        if (!in_array($syllabus->program_id, $this->myProgramIds(), true)) {
            $this->addError('signatureName', 'You are not the assigned program head for this program.');
            return;
        }

        if ($syllabus->courseBlock?->faculty_id === $employee->id) {
            $this->addError('signatureName', 'You cannot review your own syllabus. It should be reviewed by another program head.');
            return;
        }

        if ($syllabus->program_head_reviewed_at) {
            $this->reset('signingId', 'signatureName');
            return;
        }

        $syllabus->update([
            'program_head_reviewed_at' => now(),
            'program_head_reviewed_by_id' => $employee->id,
            'program_head_reviewed_by_name' => $this->signatureName,
        ]);

        $this->reset('signingId', 'signatureName');
        $this->loadPending();
    }

    public function openRequestChanges($id): void
    {
        $this->requestingId = (int) $id;
        $this->remarks = '';
    }

    public function cancelRequestChanges(): void
    {
        $this->reset('requestingId', 'remarks');
    }

    public function confirmRequestChanges(): void
    {
        $this->validate([
            'requestingId' => 'required|integer',
            'remarks' => 'required|string|min:5|max:2000',
        ], [], [
            'remarks' => 'remarks',
        ]);

        $employee = Auth::user()?->employee;

        if (!$employee) {
            $this->addError('remarks', 'Your employee account could not be resolved.');
            return;
        }

        $syllabus = CourseSyllabus::findOrFail($this->requestingId);

        if (!in_array($syllabus->program_id, $this->myProgramIds(), true)) {
            $this->addError('remarks', 'You are not the assigned program head for this program.');
            return;
        }

        if ($syllabus->courseBlock?->faculty_id === $employee->id) {
            $this->addError('remarks', 'You cannot request changes on your own syllabus.');
            return;
        }

        // Return for revision: unlock the syllabus so the teacher can edit again.
        $syllabus->update([
            'submitted_at' => null,
            'program_head_reviewed_at' => null,
            'program_head_reviewed_by_id' => null,
            'program_head_reviewed_by_name' => null,
            'revision_requested_at' => now(),
            'revision_requested_by_id' => $employee->id,
            'revision_requested_by_name' => $employee->full_name,
            'revision_remarks' => $this->remarks,
        ]);

        $this->reset('requestingId', 'remarks');
        $this->loadPending();
    }

    public function render()
    {
        return view('livewire.faculty.program-head-syllabus-reviews', [
            'pending' => $this->pending,
        ])->extends('layouts.admin')->section('content');
    }
}