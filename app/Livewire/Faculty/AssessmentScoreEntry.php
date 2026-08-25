<?php

namespace App\Livewire\Faculty;

use App\Models\AcademicYear;
use App\Models\AssessmentTask;
use App\Models\CourseBlock;
use App\Models\StudentAssessmentMark;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Class record: teacher enters each enrolled student's marks on the assessment
 * items of every assessment task of the course (grouped by task type). The
 * task totals and a per-student overall percentage are computed live.
 */
class AssessmentScoreEntry extends Component
{
    public $academicYearId = null;
    public $semester = '1st';
    public $selectedCourseBlockId = null;
    public $scores = [];

    public $semesters = ['1st', '2nd', 'Summer'];

    public function mount(): void
    {
        $this->academicYearId = AcademicYear::orderByDesc('start_year')->value('id');
    }

    public function updatedAcademicYearId(): void
    {
        $this->resetSelection();
    }

    public function updatedSemester(): void
    {
        $this->resetSelection();
    }

    public function updatedSelectedCourseBlockId(): void
    {
        $this->loadScoresForSelection();
    }

    private function resetSelection(): void
    {
        $this->selectedCourseBlockId = null;
        $this->scores = [];
    }

    private function facultyId(): ?int
    {
        return Auth::user()?->employee?->id;
    }

    private function semesterVariants(): array
    {
        return match (strtolower(trim($this->semester))) {
            '1st', '1st semester', 'first', 'first semester' => ['1st', '1st semester', 'first', 'first semester'],
            '2nd', '2nd semester', 'second', 'second semester' => ['2nd', '2nd semester', 'second', 'second semester'],
            'summer', 'summer semester' => ['summer', 'summer semester'],
            default => [strtolower(trim($this->semester))],
        };
    }

    private function applySemesterFilter($query)
    {
        return $query->whereIn(\DB::raw('LOWER(TRIM(semester))'), $this->semesterVariants());
    }

    private function selectedBlock(): ?CourseBlock
    {
        if (!$this->selectedCourseBlockId || !$this->facultyId()) {
            return null;
        }

        return CourseBlock::with(['course', 'sections', 'academicYear'])
            ->whereKey($this->selectedCourseBlockId)
            ->where('faculty_id', $this->facultyId())
            ->where('academic_year_id', $this->academicYearId)
            ->tap(fn ($query) => $this->applySemesterFilter($query))
            ->first();
    }

    private function blockBatchYear(CourseBlock $block): ?string
    {
        $batch = $block->batchYear();

        return $batch !== null ? (string) $batch : null;
    }

    /**
     * All assessment tasks of the course/batch, with their items and CLOs,
     * in teacher-configured order (sort_order, then oldest first).
     */
    private function tasksFor(CourseBlock $block): \Illuminate\Support\Collection
    {
        return AssessmentTask::with(['items.clo', 'items' => fn ($q) => $q->orderBy('id')])
            ->where('course_id', $block->course_id)
            ->where('effective_batch_year', $this->blockBatchYear($block))
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * Rearrange the assessment tasks for this course/batch: swap the given
     * task with its neighbour (direction -1 = earlier, 1 = later).
     */
    public function moveTask(int $taskId, int $direction): void
    {
        $block = $this->selectedBlock();

        if (! $block) {
            return;
        }

        $batchYear = $this->blockBatchYear($block);

        $tasks = AssessmentTask::where('course_id', $block->course_id)
            ->where('effective_batch_year', $batchYear)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $task = $tasks->get($taskId);
        $ordered = $tasks->values();

        if (! $task) {
            return;
        }

        $index = $ordered->search(fn ($t) => $t->id === $taskId);
        $targetIndex = $index + $direction;

        if ($index === false || $targetIndex < 0 || $targetIndex >= $ordered->count()) {
            return;
        }

        $neighbour = $ordered[$targetIndex];

        // If the tasks still share the same sort_order (all defaulted to 0),
        // assign each a positional value first so the swap is meaningful.
        if ((int) $task->sort_order === (int) $neighbour->sort_order) {
            foreach ($ordered as $i => $t) {
                $t->update(['sort_order' => $i + 1]);
            }
        }

        $task->refresh();
        $neighbour->refresh();

        $tmp = $task->sort_order;
        $task->update(['sort_order' => $neighbour->sort_order]);
        $neighbour->update(['sort_order' => $tmp]);

        $this->dispatch('assessment-tasks-updated');
    }

    /**
     * Load existing marks (and pre-create the student×item slots) for the
     * currently selected block. Called only when the block changes — NOT on
     * every render — so typing into an input is preserved across Livewire
     * updates.
     */
    private function loadScoresForSelection(): void
    {
        $this->scores = [];

        $block = $this->selectedBlock();

        if (! $block) {
            return;
        }

        $tasks = $this->tasksFor($block);
        $students = $block->students()->with('user')->orderBy('last_name')->orderBy('first_name')->get();

        $this->loadExistingScores($tasks, $students);
    }

    private function loadExistingScores(\Illuminate\Support\Collection $tasks, \Illuminate\Support\Collection $students): void
    {
        // Pre-create every student × item slot so Livewire's nested array
        // hydration has a key to write into (a missing slot is silently dropped).
        $this->scores = [];

        foreach ($students as $student) {
            foreach ($tasks->flatMap->items as $item) {
                $this->scores[$student->id][$item->id] = '';
            }
        }

        $itemIds = $items = $tasks->flatMap->items;

        if ($itemIds->isEmpty() || $students->isEmpty()) {
            return;
        }

        StudentAssessmentMark::whereIn('assessment_item_id', $itemIds->pluck('id'))
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->each(function ($mark) {
                $this->scores[$mark->student_id][$mark->assessment_item_id] = $mark->marks_obtained;
            });
    }

    public function saveScores(): void
    {
        $block = $this->selectedBlock();

        if (!$block) {
            $this->addError('selectedCourseBlockId', 'Select an assigned course block.');
            return;
        }

        $studentIds = $block->students()->pluck('students.id');
        $tasks = $this->tasksFor($block);

        $items = $tasks->flatMap->items->keyBy('id');

        $rules = [];
        foreach ($this->scores as $studentId => $itemScores) {
            if (!$studentIds->contains((int) $studentId)) {
                continue;
            }

            foreach ($itemScores as $itemId => $score) {
                if (($items[$itemId] ?? null) && $score !== '' && $score !== null) {
                    $rules["scores.{$studentId}.{$itemId}"] = 'numeric|min:0|max:' . $items[$itemId]->max_marks;
                }
            }
        }

        $this->validate($rules);

        $saved = 0;

        \DB::transaction(function () use ($studentIds, $items, &$saved) {
            foreach ($this->scores as $studentId => $itemScores) {
                if (!$studentIds->contains((int) $studentId)) {
                    continue;
                }

                foreach ($itemScores as $itemId => $score) {
                    if (($items[$itemId] ?? null) && $score !== '' && $score !== null) {
                        StudentAssessmentMark::updateOrCreate(
                            ['student_id' => $studentId, 'assessment_item_id' => $itemId],
                            ['marks_obtained' => $score]
                        );
                        $saved++;
                    }
                }
            }
        });

        session()->flash('success', "Student scores saved. {$saved} item score(s) recorded.");
    }

    /**
     * Per-item metadata for client-side overall computation: each item's max
     * marks and the weight of its parent task.
     *
     * @return array<int, array{max: float, weight: float}>
     */
    public function scoreMeta(): array
    {
        $block = $this->selectedBlock();

        if (! $block) {
            return [];
        }

        $meta = [];
        foreach ($this->tasksFor($block) as $task) {
            foreach ($task->items as $item) {
                $meta[$item->id] = [
                    'max' => (float) $item->max_marks,
                    'weight' => (float) $task->weight_percentage,
                    'task_max' => (float) $task->total_marks,
                ];
            }
        }

        return $meta;
    }

    public function render()
    {
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        $blocks = collect();
        $tasks = collect();
        $students = collect();
        $taskGroups = collect();
        $selectedBlock = $this->selectedBlock();

        if ($this->facultyId() && $this->academicYearId) {
            $blocks = CourseBlock::with(['course', 'sections'])
                ->where('faculty_id', $this->facultyId())
                ->where('academic_year_id', $this->academicYearId)
                ->tap(fn ($query) => $this->applySemesterFilter($query))
                ->orderBy('course_id')
                ->get();
        }

        if ($selectedBlock) {
            $tasks = $this->tasksFor($selectedBlock);
            $students = $selectedBlock->students()->with('user')->orderBy('last_name')->orderBy('first_name')->get();

            $taskGroups = $tasks
                ->groupBy(fn ($t) => trim((string) $t->type) !== '' ? (string) $t->type : 'Others');

            // Note: scores are NOT reloaded here — that would overwrite any
            // value the teacher just typed. They load once in
            // loadScoresForSelection() when the block is first chosen.
        }

        return view('livewire.faculty.assessment-score-entry', [
            'academicYears' => $academicYears,
            'blocks' => $blocks,
            'tasks' => $tasks,
            'students' => $students,
            'taskGroups' => $taskGroups,
            'selectedBlock' => $selectedBlock,
            'scoreMeta' => $selectedBlock ? $this->scoreMeta() : [],
        ])->extends('layouts.admin')->section('content');
    }
}