<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Program;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\CourseLearningOutcome;
use App\Models\Peo;
use App\Models\ProgramOutcome;
use App\Models\Student;
use App\Models\StudentAssessmentMark;

class ProgramBatchReport extends Component
{
    public $selectedProgramId = null;
    public $selectedBatchYear = null;
    public $thresholdPercentage = 60;

    public function render()
    {
        $programs = Program::orderBy('name')->get();
        $batchOptions = AcademicYear::query()
            ->whereNotNull('start_year')
            ->orderBy('start_year', 'desc')
            ->get()
            ->map(fn ($academicYear) => (string) $academicYear->start_year)
            ->filter()
            ->unique()
            ->values();

        $currentBatchCourses = collect();
        $peos = collect();
        $programOutcomes = collect();
        $batchClos = collect();
        $selectedProgram = null;

        if ($this->selectedProgramId) {
            $selectedProgram = $programs->firstWhere('id', (int) $this->selectedProgramId);

            $peos = Peo::query()
                ->where('program_id', $this->selectedProgramId)
                ->when($this->selectedBatchYear, function ($query) {
                    $query->where('effective_batch_year', $this->selectedBatchYear);
                }, function ($query) {
                    $query->whereNull('effective_batch_year');
                })
                ->orderBy('code')
                ->get();

            $programOutcomes = ProgramOutcome::query()
                ->where('program_id', $this->selectedProgramId)
                ->when($this->selectedBatchYear, function ($query) {
                    $query->where('effective_batch_year', $this->selectedBatchYear);
                }, function ($query) {
                    $query->whereNull('effective_batch_year');
                })
                ->orderBy('code')
                ->get();

            $program = Program::find($this->selectedProgramId);
            if ($program) {
                $currentBatchCourses = $program->courses()
                    ->when($this->selectedBatchYear, function ($query) {
                        $query->where('course_program.effective_batch_year', $this->selectedBatchYear);
                    }, function ($query) {
                        $query->whereNull('course_program.effective_batch_year');
                    })
                    ->orderBy('courses.code')
                    ->orderBy('courses.name')
                    ->with([
                        'learningOutcomes' => function ($query) {
                            if ($this->selectedBatchYear) {
                                $query->where('effective_batch_year', $this->selectedBatchYear);
                            }
                            $query->with(['bloomsTaxonomy', 'programOutcomes', 'course'])->orderBy('code');
                        },
                        'assessmentTasks.items',
                        'courseBlocks' => function ($query) {
                            $query
                                ->whereHas('sections', function ($sectionQuery) {
                                    $sectionQuery->where('program_id', $this->selectedProgramId);
                                })
                                ->with(['faculty', 'sections', 'academicYear', 'students']);
                        },
                    ])
                    ->get();

                foreach ($currentBatchCourses as $course) {
                    $course->setRelation(
                        'assessmentTasks',
                        $course->assessmentTasks
                            ->filter(fn ($task) => $task->effective_batch_year === (string) $this->selectedBatchYear)
                            ->values()
                    );

                    $course->setRelation(
                        'courseBlocks',
                        $course->courseBlocks
                            ->filter(fn ($block) => (string) ($block->academicYear?->start_year) === (string) $this->selectedBatchYear)
                            ->values()
                    );

                    $studentIds = $course->courseBlocks
                        ->flatMap(fn ($block) => $block->students->pluck('id'))
                        ->unique()
                        ->values();

                    $students = $studentIds->isNotEmpty()
                        ? Student::whereIn('id', $studentIds)->get()->keyBy('id')
                        : collect();

                    foreach ($course->learningOutcomes as $clo) {
                        $items = $course->assessmentTasks
                            ->flatMap(fn ($task) => $task->items->map(function ($item) use ($task) {
                                $item->task_title = $task->title;
                                return $item;
                            }))
                            ->filter(fn ($item) => (int) $item->course_learning_outcome_id === (int) $clo->id)
                            ->values();

                        $itemIds = $items->pluck('id');

                        $clo->total_students = $studentIds->count();
                        $clo->students_assessed = 0;
                        $clo->completion_rate = null;
                        $clo->student_breakdown = collect();
                        $clo->assessment_items = $items;

                        if ($studentIds->isNotEmpty() && $itemIds->isNotEmpty()) {
                            $maxMarks = $items->sum('max_marks');

                            $marksByStudent = StudentAssessmentMark::with('assessmentItem')
                                ->whereIn('student_id', $studentIds)
                                ->whereIn('assessment_item_id', $itemIds)
                                ->get()
                                ->groupBy('student_id');

                            $breakdown = collect();

                            foreach ($marksByStudent as $studentId => $marks) {
                                $student = $students->get((int) $studentId);
                                if (!$student) {
                                    continue;
                                }

                                $obtained = $marks->sum('marks_obtained');
                                $percentage = $maxMarks > 0
                                    ? ($obtained / $maxMarks) * 100
                                    : null;

                                if ($percentage === null) {
                                    continue;
                                }

                                $breakdown->push([
                                    'student_id' => (int) $studentId,
                                    'student_name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                                    'student_number' => $student->student_id,
                                    'total_obtained' => $obtained,
                                    'total_max' => $maxMarks,
                                    'percentage' => $percentage,
                                    'marks' => $marks->map(fn ($mark) => [
                                        'item_id' => $mark->assessment_item_id,
                                        'item_name' => $mark->assessmentItem?->item_name,
                                        'max_marks' => $mark->assessmentItem?->max_marks,
                                        'marks_obtained' => $mark->marks_obtained,
                                    ])->values(),
                                ]);
                            }

                            $clo->student_breakdown = $breakdown->sortByDesc('percentage')->values();
                            $clo->students_assessed = $breakdown->count();
                            $clo->completion_rate = $breakdown->isNotEmpty()
                                ? $breakdown->avg('percentage')
                                : null;
                        }
                    }

                    $courseAttainments = $course->learningOutcomes
                        ->pluck('completion_rate')
                        ->filter(fn ($rate) => $rate !== null);

                    $course->completion_rate = $courseAttainments->isNotEmpty()
                        ? $courseAttainments->avg()
                        : null;
                }
            }

            $batchClos = $currentBatchCourses
                ->flatMap(fn ($course) => $course->learningOutcomes)
                ->unique('id')
                ->values();
        }

        return view('livewire.admin.program-batch-report', [
            'programs' => $programs,
            'batchOptions' => $batchOptions,
            'currentBatchCourses' => $currentBatchCourses,
            'peos' => $peos,
            'programOutcomes' => $programOutcomes,
            'batchClos' => $batchClos,
            'selectedProgram' => $selectedProgram,
            'thresholdPercentage' => $this->thresholdPercentage,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
