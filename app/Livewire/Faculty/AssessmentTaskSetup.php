<?php

namespace App\Livewire\Faculty;

use App\Models\AcademicYear;
use App\Models\AssessmentItem;
use App\Models\AssessmentTask;
use App\Models\CourseBlock;
use App\Models\CourseLearningOutcome;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AssessmentTaskSetup extends Component
{
    public $academicYearId = null;

    public $semester = '1st';

    public $selectedCourseBlockId = null;

    public $selectedTaskId = null;

    public $editingTaskId = null;

    public $taskTitle = '';

    public $taskType = 'Exam';

    public $taskWeight = '';

    public $taskTotalMarks = '';

    public $itemName = '';

    public $itemCloId = null;

    public $itemMarks = '';

    public $semesters = ['1st', '2nd', 'Summer'];

    public $locked = false;

    public function mount($courseBlockId = null): void
    {
        $this->academicYearId = AcademicYear::orderByDesc('start_year')->value('id');

        if ($courseBlockId) {
            $block = CourseBlock::whereKey($courseBlockId)
                ->where('faculty_id', $this->facultyId())
                ->first();

            if ($block) {
                $this->academicYearId = $block->academic_year_id;
                $this->semester = $this->normalizeSemester($block->semester);
                $this->selectedCourseBlockId = (string) $block->id;
            }
        }
    }

    private function normalizeSemester(?string $semester): string
    {
        $s = strtolower(trim((string) $semester));
        if (in_array($s, ['1st', 'first', '1st semester', 'first semester'])) {
            return '1st';
        }
        if (in_array($s, ['2nd', 'second', '2nd semester', 'second semester'])) {
            return '2nd';
        }

        return 'Summer';
    }

    public function updatedAcademicYearId(): void
    {
        $this->selectedCourseBlockId = null;
        $this->selectedTaskId = null;
        $this->resetTaskForm();
    }

    public function updatedSemester(): void
    {
        $this->selectedCourseBlockId = null;
        $this->selectedTaskId = null;
        $this->resetTaskForm();
    }

    private function facultyId(): ?int
    {
        return Auth::user()?->employee?->id;
    }

    private function semesterVariants(): array
    {
        return match (strtolower(trim($this->semester))) {
            '1st', '1st semester', 'first', 'first semester' => [
                '1st',
                '1st semester',
                'first',
                'first semester',
            ],
            '2nd', '2nd semester', 'second', 'second semester' => [
                '2nd',
                '2nd semester',
                'second',
                'second semester',
            ],
            'summer', 'summer semester' => [
                'summer',
                'summer semester',
            ],
            default => [strtolower(trim($this->semester))],
        };
    }

    private function applySemesterFilter($query)
    {
        return $query->whereIn(
            \DB::raw('LOWER(TRIM(semester))'),
            $this->semesterVariants()
        );
    }

    private function selectedBlock(): ?CourseBlock
    {
        if (! $this->selectedCourseBlockId || ! $this->facultyId()) {
            return null;
        }

        return CourseBlock::with(['sections', 'academicYear'])
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

    public function saveTask(): void
    {
        if ($this->locked) {
            return;
        }
        $block = $this->selectedBlock();
        if (! $block) {
            $this->addError('selectedCourseBlockId', 'Select one of your assigned course blocks.');

            return;
        }

        $batchYear = $this->blockBatchYear($block);

        $this->validate([
            'taskTitle' => 'required|string|max:100',
            'taskType' => 'required|in:Exam,Quiz,Assignment,Project,Practical',
            'taskWeight' => 'required|numeric|min:0.01|max:100',
            'taskTotalMarks' => 'required|numeric|min:0.01',
        ]);

        $data = [
            'course_id' => $block->course_id,
            'title' => $this->taskTitle,
            'type' => $this->taskType,
            'weight_percentage' => $this->taskWeight,
            'total_marks' => $this->taskTotalMarks,
            'effective_batch_year' => $batchYear,
        ];

        if ($this->editingTaskId) {
            AssessmentTask::whereKey($this->editingTaskId)
                ->where('course_id', $block->course_id)
                ->where('effective_batch_year', $batchYear)
                ->firstOrFail()
                ->update($data);
            $this->resetTaskForm();
            session()->flash('success', 'Assessment task updated.');
        } else {
            AssessmentTask::create($data);
            $this->reset(['taskTitle']);
            session()->flash('success', 'Assessment task created for the selected course and batch.');
        }

        $this->dispatch('assessment-tasks-updated');
    }

    public function editTask(int $taskId): void
    {
        if ($this->locked) {
            return;
        }
        $block = $this->selectedBlock();
        if (! $block) {
            return;
        }

        $task = AssessmentTask::whereKey($taskId)
            ->where('course_id', $block->course_id)
            ->where('effective_batch_year', $this->blockBatchYear($block))
            ->firstOrFail();

        $this->editingTaskId = $task->id;
        $this->selectedTaskId = (string) $task->id;
        $this->taskTitle = $task->title;
        $this->taskType = $task->type;
        $this->taskWeight = (string) $task->weight_percentage;
        $this->taskTotalMarks = (string) $task->total_marks;
        $this->resetErrorBag();
    }

    public function cancelEditTask(): void
    {
        $this->resetTaskForm();
    }

    private function resetTaskForm(): void
    {
        $this->reset(['editingTaskId', 'taskTitle', 'taskType', 'taskWeight', 'taskTotalMarks']);
        $this->resetErrorBag();
    }

    public function saveItem(): void
    {
        if ($this->locked) {
            return;
        }
        $block = $this->selectedBlock();
        if (! $block) {
            $this->addError('selectedCourseBlockId', 'Select one of your assigned course blocks.');

            return;
        }

        $batchYear = $this->blockBatchYear($block);

        $this->validate([
            'selectedTaskId' => 'required|exists:assessment_tasks,id',
            'itemName' => 'required|string|max:100',
            'itemCloId' => 'required|exists:course_learning_outcomes,id',
            'itemMarks' => 'required|numeric|min:0.01',
        ]);

        $task = AssessmentTask::whereKey($this->selectedTaskId)
            ->where('course_id', $block->course_id)
            ->where('effective_batch_year', $batchYear)
            ->firstOrFail();

        $clo = CourseLearningOutcome::whereKey($this->itemCloId)
            ->where('course_id', $block->course_id)
            ->where('effective_batch_year', $batchYear)
            ->firstOrFail();

        AssessmentItem::create([
            'assessment_task_id' => $task->id,
            'course_learning_outcome_id' => $clo->id,
            'item_name' => $this->itemName,
            'max_marks' => $this->itemMarks,
            'effective_batch_year' => $batchYear,
        ]);

        $this->reset(['itemName', 'itemCloId', 'itemMarks']);
        session()->flash('success', 'Assessment item mapped to the selected CLO.');
        $this->dispatch('assessment-tasks-updated');
    }

    public function deleteTask(int $taskId): void
    {
        if ($this->locked) {
            return;
        }
        $block = $this->selectedBlock();
        if (! $block) {
            $this->addError('selectedCourseBlockId', 'Select one of your assigned course blocks.');

            return;
        }

        $batchYear = $this->blockBatchYear($block);

        $task = AssessmentTask::whereKey($taskId)
            ->where('course_id', $block->course_id)
            ->where('effective_batch_year', $batchYear)
            ->firstOrFail();

        $task->delete();

        if ((int) $this->selectedTaskId === $taskId) {
            $this->selectedTaskId = null;
        }

        session()->flash('success', 'Assessment task and its mapped items were deleted.');
        $this->dispatch('assessment-tasks-updated');
    }

    public function render()
    {
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        $blocks = collect();
        $clos = collect();
        $tasks = collect();
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
            $batchYear = $this->blockBatchYear($selectedBlock);
            $clos = CourseLearningOutcome::where('course_id', $selectedBlock->course_id)
                ->where('effective_batch_year', $batchYear)
                ->orderBy('code')
                ->get();

            $tasks = AssessmentTask::with('items.clo')
                ->where('course_id', $selectedBlock->course_id)
                ->where('effective_batch_year', $batchYear)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('livewire.faculty.assessment-task-setup', [
            'academicYears' => $academicYears,
            'blocks' => $blocks,
            'clos' => $clos,
            'tasks' => $tasks,
            'selectedBlock' => $selectedBlock,
            'locked' => $this->locked,
        ])->extends('layouts.admin')->section('content');
    }
}
