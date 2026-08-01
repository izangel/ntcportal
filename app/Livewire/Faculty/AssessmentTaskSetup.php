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

    public $taskTitle = '';
    public $taskType = 'Exam';
    public $taskWeight = '';
    public $taskTotalMarks = '';
    public $itemName = '';
    public $itemCloId = null;
    public $itemMarks = '';

    public $semesters = ['1st', '2nd', 'Summer'];

    public function mount(): void
    {
        $this->academicYearId = AcademicYear::orderByDesc('start_year')->value('id');
    }

    public function updatedAcademicYearId(): void
    {
        $this->selectedCourseBlockId = null;
        $this->selectedTaskId = null;
    }

    public function updatedSemester(): void
    {
        $this->selectedCourseBlockId = null;
        $this->selectedTaskId = null;
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
        if (!$this->selectedCourseBlockId || !$this->facultyId()) {
            return null;
        }

        return CourseBlock::with('academicYear')
            ->whereKey($this->selectedCourseBlockId)
            ->where('faculty_id', $this->facultyId())
            ->where('academic_year_id', $this->academicYearId)
            ->tap(fn ($query) => $this->applySemesterFilter($query))
            ->first();
    }

    public function saveTask(): void
    {
        $block = $this->selectedBlock();
        if (!$block) {
            $this->addError('selectedCourseBlockId', 'Select one of your assigned course blocks.');
            return;
        }

        $this->validate([
            'taskTitle' => 'required|string|max:100',
            'taskType' => 'required|in:Exam,Quiz,Assignment,Project,Practical',
            'taskWeight' => 'required|numeric|min:0.01|max:100',
            'taskTotalMarks' => 'required|numeric|min:0.01',
        ]);

        AssessmentTask::create([
            'course_id' => $block->course_id,
            'title' => $this->taskTitle,
            'type' => $this->taskType,
            'weight_percentage' => $this->taskWeight,
            'total_marks' => $this->taskTotalMarks,
            'effective_batch_year' => (string) $block->academicYear->start_year,
        ]);

        $this->reset(['taskTitle', 'taskType', 'taskWeight', 'taskTotalMarks']);
        session()->flash('success', 'Assessment task created for the selected course and batch.');
    }

    public function saveItem(): void
    {
        $block = $this->selectedBlock();
        if (!$block) {
            $this->addError('selectedCourseBlockId', 'Select one of your assigned course blocks.');
            return;
        }

        $this->validate([
            'selectedTaskId' => 'required|exists:assessment_tasks,id',
            'itemName' => 'required|string|max:100',
            'itemCloId' => 'required|exists:course_learning_outcomes,id',
            'itemMarks' => 'required|numeric|min:0.01',
        ]);

        $task = AssessmentTask::whereKey($this->selectedTaskId)
            ->where('course_id', $block->course_id)
            ->where('effective_batch_year', (string) $block->academicYear->start_year)
            ->firstOrFail();

        $clo = CourseLearningOutcome::whereKey($this->itemCloId)
            ->where('course_id', $block->course_id)
            ->where('effective_batch_year', (string) $block->academicYear->start_year)
            ->firstOrFail();

        AssessmentItem::create([
            'assessment_task_id' => $task->id,
            'course_learning_outcome_id' => $clo->id,
            'item_name' => $this->itemName,
            'max_marks' => $this->itemMarks,
            'effective_batch_year' => (string) $block->academicYear->start_year,
        ]);

        $this->reset(['itemName', 'itemCloId', 'itemMarks']);
        session()->flash('success', 'Assessment item mapped to the selected CLO.');
    }

    public function deleteTask(int $taskId): void
    {
        $block = $this->selectedBlock();
        if (!$block) {
            $this->addError('selectedCourseBlockId', 'Select one of your assigned course blocks.');
            return;
        }

        $task = AssessmentTask::whereKey($taskId)
            ->where('course_id', $block->course_id)
            ->where('effective_batch_year', (string) $block->academicYear->start_year)
            ->firstOrFail();

        $task->delete();

        if ((int) $this->selectedTaskId === $taskId) {
            $this->selectedTaskId = null;
        }

        session()->flash('success', 'Assessment task and its mapped items were deleted.');
    }

    public function render()
    {
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        $blocks = collect();
        $clos = collect();
        $tasks = collect();
        $selectedBlock = $this->selectedBlock();

        if ($this->facultyId() && $this->academicYearId) {
            $blocks = CourseBlock::with(['course', 'section'])
                ->where('faculty_id', $this->facultyId())
                ->where('academic_year_id', $this->academicYearId)
                ->tap(fn ($query) => $this->applySemesterFilter($query))
                ->orderBy('course_id')
                ->get();
        }

        if ($selectedBlock) {
            $batchYear = (string) $selectedBlock->academicYear->start_year;
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
        ])->extends('layouts.admin')->section('content');
    }
}
