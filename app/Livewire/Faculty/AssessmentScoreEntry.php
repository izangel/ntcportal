<?php

namespace App\Livewire\Faculty;

use App\Models\AcademicYear;
use App\Models\AssessmentTask;
use App\Models\CourseBlock;
use App\Models\StudentAssessmentMark;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AssessmentScoreEntry extends Component
{
    public $academicYearId = null;
    public $semester = '1st';
    public $selectedCourseBlockId = null;
    public $selectedTaskId = null;
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
        $this->selectedTaskId = null;
        $this->scores = [];
    }

    public function updatedSelectedTaskId(): void
    {
        $this->loadExistingScores();
    }

    private function resetSelection(): void
    {
        $this->selectedCourseBlockId = null;
        $this->selectedTaskId = null;
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

    private function selectedTask(): ?AssessmentTask
    {
        $block = $this->selectedBlock();
        if (!$block || !$this->selectedTaskId) {
            return null;
        }

        return AssessmentTask::with('items.clo')
            ->whereKey($this->selectedTaskId)
            ->where('course_id', $block->course_id)
            ->where('effective_batch_year', (string) $block->academicYear->start_year)
            ->first();
    }

    private function loadExistingScores(): void
    {
        $this->scores = [];
        $task = $this->selectedTask();
        $block = $this->selectedBlock();

        if (!$task || !$block) {
            return;
        }

        StudentAssessmentMark::whereIn('assessment_item_id', $task->items->pluck('id'))
            ->whereIn('student_id', $block->students()->pluck('students.id'))
            ->get()
            ->each(function ($mark) {
                $this->scores[$mark->student_id][$mark->assessment_item_id] = $mark->marks_obtained;
            });
    }

    public function saveScores(): void
    {
        $task = $this->selectedTask();
        $block = $this->selectedBlock();

        if (!$task || !$block) {
            $this->addError('selectedTaskId', 'Select an assigned course block and assessment task.');
            return;
        }

        $studentIds = $block->students()->pluck('students.id');
        $items = $task->items->keyBy('id');
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
                }
            }
        }

        session()->flash('success', 'Student scores saved successfully.');
    }

    public function render()
    {
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        $blocks = collect();
        $tasks = collect();
        $students = collect();
        $selectedBlock = $this->selectedBlock();
        $selectedTask = $this->selectedTask();

        if ($this->facultyId() && $this->academicYearId) {
            $blocks = CourseBlock::with(['course', 'sections'])
                ->where('faculty_id', $this->facultyId())
                ->where('academic_year_id', $this->academicYearId)
                ->tap(fn ($query) => $this->applySemesterFilter($query))
                ->orderBy('course_id')
                ->get();
        }

        if ($selectedBlock) {
            $batchYear = (string) $selectedBlock->academicYear->start_year;
            $tasks = AssessmentTask::where('course_id', $selectedBlock->course_id)
                ->where('effective_batch_year', $batchYear)
                ->with('items.clo')
                ->orderByDesc('created_at')
                ->get();
            $students = $selectedBlock->students()->with('user')->orderBy('last_name')->orderBy('first_name')->get();
        }

        return view('livewire.faculty.assessment-score-entry', [
            'academicYears' => $academicYears,
            'blocks' => $blocks,
            'tasks' => $tasks,
            'students' => $students,
            'selectedBlock' => $selectedBlock,
            'selectedTask' => $selectedTask,
        ])->extends('layouts.admin')->section('content');
    }
}
