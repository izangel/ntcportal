<?php

namespace App\Livewire\Faculty;

use App\Models\CourseBlock;
use App\Models\CourseSyllabus;
use App\Models\SyllabusLearningPlanItem;
use App\Services\CourseSyllabusData;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CourseSyllabusEditor extends Component
{
    public $courseBlockId = null;

    public $grading_system = '';
    public $textbooks_references = '';
    public $classroom_policies = '';

    public $items = [];

    public function mount($courseBlock): void
    {
        $block = $this->loadBlock($courseBlock);
        if (!$block) {
            abort(404);
        }

        $this->courseBlockId = $block->id;

        $syllabus = CourseSyllabus::with('learningPlanItems')
            ->where('course_block_id', $block->id)
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
            ['course_block_id' => $block->id],
            ['course_block_id' => $block->id]
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

        return $block ? new CourseSyllabusData($block) : null;
    }

    public function render()
    {
        $data = $this->data();

        return view('livewire.faculty.course-syllabus-editor', [
            'data' => $data,
            'items' => $this->items,
        ])->extends('layouts.admin')->section('content');
    }
}