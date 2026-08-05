<?php

namespace App\Livewire\CourseMaterials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\CourseBlock;
use App\Models\CourseMaterial;

class CourseMaterialsManager extends Component
{
    public $academicYearId;
    public $semester = '1st';
    public $selectedBlockId;

    public $facultyId;
    public $academicYears = [];
    public $semesterOptions = ['1st', '2nd Semester', 'Summer'];
    public $assignedBlocks = [];

    public $materials = [];

    public $formType = 'lms';
    public $formTitle = '';
    public $formUrl = '';
    public $editingId = null;

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
        $this->resetSelection();
    }

    public function updatedSemester()
    {
        $this->loadAssignedBlocks();
        $this->resetSelection();
    }

    public function updatedSelectedBlockId()
    {
        $this->resetForm();
        $this->loadMaterials();
    }

    public function resetSelection()
    {
        $this->selectedBlockId = null;
        $this->materials = [];
        $this->resetForm();
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
            ->with(['course', 'sections.program', 'academicYear'])
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

    public function loadMaterials()
    {
        $this->materials = [];

        if (!$this->selectedBlockId) {
            return;
        }

        $records = CourseMaterial::where('course_block_id', $this->selectedBlockId)
            ->orderBy('type')
            ->orderByDesc('created_at')
            ->get();

        $this->materials = collect(CourseMaterial::TYPES)->mapWithKeys(function ($type) use ($records) {
            $items = $records->where('type', $type)->values()->map(fn ($m) => [
                'id' => $m->id,
                'type' => $m->type,
                'title' => $m->title,
                'url' => $m->url,
                'description' => $m->description,
                'created_at' => $m->created_at?->toDateTimeString(),
            ])->toArray();

            return [$type => $items];
        })->toArray();
    }

    public function resetForm()
    {
        $this->formType = 'lms';
        $this->formTitle = '';
        $this->formUrl = '';
        $this->editingId = null;
    }

    public function startAdd($type = null)
    {
        $this->resetForm();
        if (in_array($type, CourseMaterial::TYPES, true)) {
            $this->formType = $type;
        }
    }

    public function editMaterial($materialId)
    {
        $material = CourseMaterial::find((int) $materialId);

        if (!$material || !$this->verifyOwnership((int) $material->course_block_id)) {
            return;
        }

        $this->formType = $material->type;
        $this->formTitle = $material->title;
        $this->formUrl = $material->url;
        $this->editingId = $material->id;
    }

    public function saveMaterial()
    {
        if (!$this->selectedBlockId || !$this->verifyOwnership((int) $this->selectedBlockId)) {
            session()->flash('error', 'You are not assigned to this class.');
            return;
        }

        $this->validate([
            'formType' => ['required', 'in:' . implode(',', CourseMaterial::TYPES)],
            'formTitle' => ['required', 'string', 'max:191'],
            'formUrl' => ['required', 'url', 'max:2048'],
        ]);

        $data = [
            'course_block_id' => (int) $this->selectedBlockId,
            'type' => $this->formType,
            'title' => trim($this->formTitle),
            'url' => trim($this->formUrl),
        ];

        if ($this->editingId) {
            CourseMaterial::whereKey($this->editingId)
                ->where('course_block_id', $this->selectedBlockId)
                ->update($data);

            session()->flash('message', 'Material link updated.');
        } else {
            CourseMaterial::create($data);

            session()->flash('message', 'Material link added.');
        }

        $this->resetForm();
        $this->loadMaterials();
    }

    public function deleteMaterial($materialId)
    {
        $material = CourseMaterial::find((int) $materialId);

        if (!$material || !$this->verifyOwnership((int) $material->course_block_id)) {
            return;
        }

        $material->delete();

        if ($this->editingId === $material->id) {
            $this->resetForm();
        }

        $this->loadMaterials();
        session()->flash('message', 'Material link removed.');
    }

    private function verifyOwnership(int $blockId): bool
    {
        return (bool) CourseBlock::whereKey($blockId)
            ->where('faculty_id', $this->facultyId)
            ->exists();
    }

    public function render()
    {
        return view('livewire.course-materials.course-materials-manager', [
            'academicYears' => $this->academicYears,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
