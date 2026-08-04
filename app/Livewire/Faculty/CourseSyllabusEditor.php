<?php

namespace App\Livewire\Faculty;

use App\Models\CourseBlock;
use App\Models\CourseSyllabus;
use App\Models\Program;
use App\Models\SyllabusLearningPlanItem;
use App\Services\CourseSyllabusData;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CourseSyllabusEditor extends Component
{
    public $courseBlockId = null;

    public $programId = null;

    public $grading_system = '';
    public $textbooks_references = '';
    public $classroom_policies = '';

    public $items = [];

    public function mount($courseBlock, $program = null): void
    {
        $block = $this->loadBlock($courseBlock);
        if (!$block) {
            abort(404);
        }

        $this->courseBlockId = $block->id;

        $programId = $this->resolveProgramId($block, $program);
        if (!$programId) {
            abort(404);
        }

        $this->programId = $programId;

        $syllabus = CourseSyllabus::with('learningPlanItems')
            ->where('course_block_id', $block->id)
            ->where('program_id', $programId)
            ->first();

        if ($syllabus) {
            $this->grading_system = (string) $syllabus->grading_system;
            $this->textbooks_references = (string) $syllabus->textbooks_references;
            $this->classroom_policies = (string) $syllabus->classroom_policies;

            $this->items = $syllabus->learningPlanItems
                ->sortBy('sort_order')
                ->map(fn ($item) => [
                    'learning_outcomes' => (string) $item->learning_outcomes,
                    'topics_readings' => (string) $item->topics_readings,
                    'schedule' => (string) $item->schedule,
                    'learning_activities' => (string) $item->learning_activities,
                    'assessment_tools' => (string) $item->assessment_tools,
                ])
                ->values()
                ->toArray();
        }

        if (empty($this->items)) {
            $this->items = [$this->blankItem()];
        }
    }

    /**
     * Resolve which program syllabus we are editing: an explicit program
     * segment, else the single program served by the block, else the
     * highest-priority program for mixed blocks.
     */
    private function resolveProgramId(CourseBlock $block, $program): ?int
    {
        $programs = (new CourseSyllabusData($block))->programs();

        if ($program) {
            $programId = (int) $program;
            return $programs->contains('id', $programId) ? $programId : null;
        }

        if ($programs->count() === 1) {
            return (int) $programs->first()->id;
        }

        $default = (new CourseSyllabusData($block))->program();
        return $default?->id;
    }

    private function blankItem(): array
    {
        return [
            'learning_outcomes' => '',
            'topics_readings' => '',
            'schedule' => '',
            'learning_activities' => '',
            'assessment_tools' => '',
        ];
    }

    private function loadBlock($courseBlock): ?CourseBlock
    {
        return CourseBlock::with(['course', 'academicYear'])
            ->whereKey($courseBlock)
            ->where('faculty_id', Auth::user()?->employee?->id)
            ->first();
    }

    public function addRow(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeRow($index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (empty($this->items)) {
            $this->items[] = $this->blankItem();
        }
    }

    public function save(): void
    {
        $block = $this->loadBlock($this->courseBlockId);
        if (!$block) {
            abort(404);
        }

        $syllabus = CourseSyllabus::firstOrCreate(
            [
                'course_block_id' => $block->id,
                'program_id' => $this->programId,
            ],
            [
                'course_block_id' => $block->id,
                'program_id' => $this->programId,
            ]
        );

        $syllabus->update([
            'grading_system' => $this->grading_system,
            'textbooks_references' => $this->textbooks_references,
            'classroom_policies' => $this->classroom_policies,
        ]);

        SyllabusLearningPlanItem::where('course_syllabus_id', $syllabus->id)->delete();

        foreach (array_values($this->items) as $i => $item) {
            SyllabusLearningPlanItem::create([
                'course_syllabus_id' => $syllabus->id,
                'learning_outcomes' => $item['learning_outcomes'] ?? null,
                'topics_readings' => $item['topics_readings'] ?? null,
                'schedule' => $item['schedule'] ?? null,
                'learning_activities' => $item['learning_activities'] ?? null,
                'assessment_tools' => $item['assessment_tools'] ?? null,
                'sort_order' => $i,
            ]);
        }

        session()->flash('success', 'Syllabus saved successfully.');
    }

    public function data(): ?CourseSyllabusData
    {
        $block = $this->loadBlock($this->courseBlockId);

        if (!$block) {
            return null;
        }

        $program = Program::find($this->programId);

        return $program ? new CourseSyllabusData($block, $program) : null;
    }

    public function programs()
    {
        $block = $this->loadBlock($this->courseBlockId);

        return $block ? (new CourseSyllabusData($block))->programs() : collect();
    }

    public function render()
    {
        $data = $this->data();

        return view('livewire.faculty.course-syllabus-editor', [
            'data' => $data,
            'items' => $this->items,
            'programs' => $this->programs(),
            'courseBlockId' => $this->courseBlockId,
            'programId' => $this->programId,
        ])->extends('layouts.admin')->section('content');
    }
}
