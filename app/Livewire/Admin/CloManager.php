<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Program;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\BloomsTaxonomy;
use App\Models\CourseLearningOutcome;
use Illuminate\Support\Facades\DB;

class CloManager extends Component
{
    public $selectedProgramId = null;
    public $selectedBatchYear = null;

    public $cloCourseId = null;
    public $editingCloId = null;
    public $cloCode = '';
    public $cloDescription = '';
    public $cloTaxonomyId = null;

    public function updatedSelectedProgramId()
    {
        $this->resetCloForm();
    }

    public function updatedSelectedBatchYear()
    {
        $this->resetCloForm();
    }

    public function saveClo(): void
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'cloCourseId' => 'required|exists:courses,id',
            'cloCode' => 'required|string|max:20',
            'cloDescription' => 'required|string|min:10',
            'cloTaxonomyId' => 'required|exists:blooms_taxonomies,id',
        ]);

        $isAssignedToProgram = Program::findOrFail($this->selectedProgramId)
            ->courses()
            ->where('courses.id', $this->cloCourseId)
            ->when($this->selectedBatchYear, function ($query) {
                $query->where('course_program.effective_batch_year', $this->selectedBatchYear);
            })
            ->exists();

        if (! $isAssignedToProgram) {
            $this->addError('cloCourseId', 'Select a course assigned to this program and batch first.');
            return;
        }

        $data = [
            'course_id' => $this->cloCourseId,
            'code' => $this->cloCode,
            'description' => $this->cloDescription,
            'blooms_taxonomy_id' => $this->cloTaxonomyId,
            'effective_batch_year' => $this->selectedBatchYear ?: null,
        ];

        if ($this->editingCloId) {
            CourseLearningOutcome::findOrFail($this->editingCloId)->update($data);
            session()->flash('success', 'CLO updated successfully.');
        } else {
            CourseLearningOutcome::create($data);
            session()->flash('success', 'CLO assigned to the selected course.');
        }

        $this->resetCloForm();
    }

    public function beginCloCreate($courseId): void
    {
        $this->resetCloForm();
        $this->cloCourseId = (int) $courseId;
        $this->dispatch('scroll-to-clo-form');
    }

    public function editClo($id): void
    {
        $clo = CourseLearningOutcome::query()
            ->whereKey($id)
            ->where('effective_batch_year', $this->selectedBatchYear ?: null)
            ->firstOrFail();

        $this->editingCloId = $clo->id;
        $this->cloCourseId = $clo->course_id;
        $this->cloCode = (string) $clo->code;
        $this->cloDescription = (string) $clo->description;
        $this->cloTaxonomyId = $clo->blooms_taxonomy_id;
        $this->resetErrorBag();
        $this->dispatch('scroll-to-clo-form');
    }

    public function deleteClo($id): void
    {
        $clo = CourseLearningOutcome::query()
            ->whereKey($id)
            ->where('effective_batch_year', $this->selectedBatchYear ?: null)
            ->first();

        if (! $clo) {
            return;
        }

        $course = $clo->course()->first();

        DB::transaction(function () use ($clo) {
            $clo->programOutcomes()->detach();
            $clo->delete();
        });

        if ($this->editingCloId === $clo->id) {
            $this->resetCloForm();
        }

        session()->flash('success', "CLO {$clo->code} deleted" . ($course ? " from {$course->code}" : '') . '.');
    }

    public function resetCloForm(): void
    {
        $this->reset(['cloCourseId', 'editingCloId', 'cloCode', 'cloDescription', 'cloTaxonomyId']);
        $this->resetErrorBag();
    }

    private function assignedCourses(): \Illuminate\Support\Collection
    {
        if (! $this->selectedProgramId) {
            return collect();
        }

        $courseIds = Program::findOrFail($this->selectedProgramId)
            ->courses()
            ->when($this->selectedBatchYear, function ($query) {
                $query->where('course_program.effective_batch_year', $this->selectedBatchYear);
            })
            ->pluck('courses.id')
            ->unique()
            ->values();

        if ($courseIds->isEmpty()) {
            return collect();
        }

        return Course::whereIn('id', $courseIds)
            ->orderBy('code')
            ->orderBy('name')
            ->with([
                'learningOutcomes' => function ($query) {
                    if ($this->selectedBatchYear) {
                        $query->where('effective_batch_year', $this->selectedBatchYear);
                    }
                    $query->with(['bloomsTaxonomy', 'programOutcomes'])->orderBy('code');
                },
            ])
            ->get();
    }

    public function render()
    {
        $programs = Program::orderBy('name')->get();
        $courses = Course::orderBy('code')->get();

        $assignedCourses = $this->assignedCourses();

        $batchOptions = AcademicYear::query()
            ->whereNotNull('start_year')
            ->orderBy('start_year', 'desc')
            ->pluck('start_year')
            ->unique()
            ->values();

        $taxonomies = BloomsTaxonomy::orderBy('domain')->orderBy('code')->get();

        return view('livewire.admin.clo-manager', [
            'programs' => $programs,
            'courses' => $courses,
            'batchOptions' => $batchOptions,
            'assignedCourses' => $assignedCourses,
            'taxonomies' => $taxonomies,
        ])->extends('layouts.admin')
            ->section('content');
    }
}