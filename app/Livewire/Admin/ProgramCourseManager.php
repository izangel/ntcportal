<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Program;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\BloomsTaxonomy;
use App\Models\CourseLearningOutcome;
use App\Models\Peo;
use App\Models\ProgramOutcome;
use App\Models\Student;
use App\Models\StudentAssessmentMark;
use Illuminate\Support\Facades\DB;

class ProgramCourseManager extends Component
{
    public $selectedProgramId = null;
    public $selectedBatchYear = null;
    public $selectedCourseIds = []; // Stores checked course IDs
    public $cloCourseId = null;
    public $editingCloId = null;
    public $cloCode = '';
    public $cloDescription = '';
    public $cloTaxonomyId = null;
    public $copySourceBatch = [];

    public function updatedSelectedProgramId($programId)
    {
        if ($programId) {
            $program = Program::find($programId);
            // Pre-check courses already assigned to this program for the current batch
            $this->selectedCourseIds = $program
                ? $program->courses()
                    ->when($this->selectedBatchYear, function ($query) {
                        $query->where('course_program.effective_batch_year', $this->selectedBatchYear);
                    }, function ($query) {
                        $query->whereNull('course_program.effective_batch_year');
                    })
                    ->pluck('courses.id')
                    ->toArray()
                : [];
        } else {
            $this->selectedCourseIds = [];
        }
    }

    public function updatedSelectedBatchYear()
    {
        $this->refreshSelectedCourses();
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

        if (!$isAssignedToProgram) {
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
            $clo = CourseLearningOutcome::findOrFail($this->editingCloId);
            $clo->update($data);
            $message = 'CLO updated successfully.';
        } else {
            CourseLearningOutcome::create($data);
            $message = 'CLO assigned to the selected course.';
        }

        $this->resetCloForm();
        session()->flash('success', $message);
    }

    public function assignClo($courseId): void
    {
        $this->editingCloId = null;
        $this->cloCourseId = $courseId;
        $this->cloCode = '';
        $this->cloDescription = '';
        $this->cloTaxonomyId = null;
        $this->resetErrorBag();
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

    public function resetCloForm(): void
    {
        $this->reset(['cloCourseId', 'editingCloId', 'cloCode', 'cloDescription', 'cloTaxonomyId']);
        $this->resetErrorBag();
    }

    public function updateCloPoMapping($cloId, $poId, $level): void
    {
        $clo = CourseLearningOutcome::findOrFail($cloId);
        $poQuery = ProgramOutcome::whereKey($poId)
            ->where('program_id', $this->selectedProgramId);

        if ($this->selectedBatchYear) {
            $poQuery->where('effective_batch_year', $this->selectedBatchYear);
        } else {
            $poQuery->whereNull('effective_batch_year');
        }

        $po = $poQuery->firstOrFail();

        if (empty($level)) {
            $clo->programOutcomes()->detach($po->id);
        } else {
            validator(
                ['level' => $level],
                ['level' => 'required|in:I,G,A']
            )->validate();

            $clo->programOutcomes()->syncWithoutDetaching([
                $po->id => ['level' => $level],
            ]);
        }
    }

    /**
     * Copy the CLOs (and their CO-PO mapping) of the given course from another
     * batch into the currently selected batch. CO-PO links are only re-attached
     * to Program Outcomes of the target batch whose code matches an outcome the
     * source CLO was mapped to — i.e. only when the POs are the same.
     */
    public function copyClosFromBatch($courseId): void
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'selectedBatchYear' => 'required',
        ]);

        $courseId = (int) $courseId;

        if (! Course::whereKey($courseId)->exists()) {
            return;
        }
        $targetBatch = (string) $this->selectedBatchYear;
        $sourceBatch = (string) ($this->copySourceBatch[$courseId] ?? '');

        if ($sourceBatch === '' || $sourceBatch === $targetBatch) {
            $this->addError('copySourceBatch.'.$courseId, 'Pick a different batch to copy CLOs from.');
            return;
        }

        $course = Course::findOrFail($courseId);

        $isAssigned = Program::findOrFail($this->selectedProgramId)
            ->courses()
            ->where('courses.id', $courseId)
            ->where('course_program.effective_batch_year', $targetBatch)
            ->exists();

        if (! $isAssigned) {
            $this->addError('copySourceBatch.'.$courseId, 'This course is not assigned to the selected batch yet.');
            return;
        }

        $sourceClos = CourseLearningOutcome::with('programOutcomes')
            ->where('course_id', $courseId)
            ->where('effective_batch_year', $sourceBatch)
            ->orderBy('code')
            ->get();

        if ($sourceClos->isEmpty()) {
            session()->flash('error', "No CLOs found for {$course->code} in Batch {$sourceBatch}.");
            return;
        }

        // Map the target batch's POs by code, so we only relink when the POs are the same.
        $targetPosByCode = ProgramOutcome::query()
            ->where('program_id', $this->selectedProgramId)
            ->where('effective_batch_year', $targetBatch)
            ->get()
            ->keyBy('code');

        $created = 0;
        $updated = 0;
        $mapped = 0;
        $skippedMapping = 0;

        DB::transaction(function () use (
            $courseId,
            $targetBatch,
            $sourceClos,
            $targetPosByCode,
            &$created,
            &$updated,
            &$mapped,
            &$skippedMapping
        ) {
            foreach ($sourceClos as $sourceClo) {
                $targetClo = CourseLearningOutcome::query()
                    ->where('course_id', $courseId)
                    ->where('effective_batch_year', $targetBatch)
                    ->where('code', $sourceClo->code)
                    ->first();

                if ($targetClo) {
                    $targetClo->update([
                        'description' => $sourceClo->description,
                        'blooms_taxonomy_id' => $sourceClo->blooms_taxonomy_id,
                    ]);
                    $updated++;
                } else {
                    $targetClo = CourseLearningOutcome::create([
                        'course_id' => $courseId,
                        'code' => $sourceClo->code,
                        'description' => $sourceClo->description,
                        'blooms_taxonomy_id' => $sourceClo->blooms_taxonomy_id,
                        'effective_batch_year' => $targetBatch,
                    ]);
                    $created++;
                }

                foreach ($sourceClo->programOutcomes as $sourcePo) {
                    $targetPo = $targetPosByCode->get($sourcePo->code);

                    // Only relink when a PO with the same code exists in this batch.
                    if (! $targetPo) {
                        $skippedMapping++;
                        continue;
                    }

                    $targetClo->programOutcomes()->syncWithoutDetaching([
                        $targetPo->id => ['level' => $sourcePo->pivot->level],
                    ]);
                    $mapped++;
                }
            }
        });

        $message = "Copied {$created} CLO(s), updated {$updated} existing CLO(s) from Batch {$sourceBatch} to Batch {$targetBatch} for {$course->code}.";

        if ($mapped > 0) {
            $message .= " Re-mapped {$mapped} CO-PO link(s) to the matching POs of Batch {$targetBatch}.";
        } elseif ($skippedMapping > 0) {
            $message .= " CO-PO mappings were NOT copied because no POs with matching codes exist for Batch {$targetBatch} yet. Add the POs for this batch first.";
        } elseif ($sourceClos->sum(fn ($clo) => $clo->programOutcomes->count()) > 0) {
            $message .= " No CO-PO links in the source batch were matched for this batch.";
        }

        session()->flash('success', $message);
    }

    private function refreshSelectedCourses(): void
    {
        if (!$this->selectedProgramId) {
            $this->selectedCourseIds = [];
            return;
        }

        $program = Program::find($this->selectedProgramId);
        $this->selectedCourseIds = $program
            ? $program->courses()
                ->when($this->selectedBatchYear, function ($query) {
                    $query->where('course_program.effective_batch_year', $this->selectedBatchYear);
                }, function ($query) {
                    $query->whereNull('course_program.effective_batch_year');
                })
                ->pluck('courses.id')
                ->toArray()
            : [];
    }

    public function saveAssignments()
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'selectedCourseIds' => 'nullable|array',
            'selectedCourseIds.*' => 'exists:courses,id',
        ]);

        $program = Program::findOrFail($this->selectedProgramId);
        $batchYear = $this->selectedBatchYear ?: null;

        DB::transaction(function () use ($program, $batchYear) {
            $assignmentQuery = DB::table('course_program')
                ->where('program_id', $program->id);

            if ($batchYear === null) {
                $assignmentQuery->whereNull('effective_batch_year');
            } else {
                $assignmentQuery->where('effective_batch_year', $batchYear);
            }

            $assignmentQuery->delete();

            if (!empty($this->selectedCourseIds)) {
                $rows = collect($this->selectedCourseIds)
                    ->unique()
                    ->map(fn ($courseId) => [
                        'course_id' => $courseId,
                        'program_id' => $program->id,
                        'effective_batch_year' => $batchYear,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->all();

                DB::table('course_program')->insert($rows);
            }
        });

        session()->flash('success', "Program courses updated successfully for {$program->name}!");
    }

    public function carryForwardCoursesFromPreviousBatch(): void
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'selectedBatchYear' => 'required',
        ]);

        $programId = (int) $this->selectedProgramId;
        $newBatch = (string) $this->selectedBatchYear;

        $previousBatch = $this->previousBatchWithCourses();

        if (!$previousBatch) {
            session()->flash('error', 'No earlier batch with course assignments found to carry forward from.');
            return;
        }

        $previousAssignments = DB::table('course_program')
            ->where('program_id', $programId)
            ->where('effective_batch_year', $previousBatch)
            ->pluck('course_id');

        if ($previousAssignments->isEmpty()) {
            session()->flash('error', "No courses found for batch {$previousBatch}.");
            return;
        }

        $clos = CourseLearningOutcome::with('programOutcomes')
            ->whereIn('course_id', $previousAssignments)
            ->where('effective_batch_year', $previousBatch)
            ->get();

        $previousMappingsCount = $clos->sum(fn ($clo) => $clo->programOutcomes->count());

        $mappedCount = 0;

        DB::transaction(function () use ($programId, $newBatch, $previousAssignments, $clos, &$mappedCount) {
            DB::table('course_program')->insert(
                $previousAssignments
                    ->unique()
                    ->map(fn ($courseId) => [
                        'course_id' => $courseId,
                        'program_id' => $programId,
                        'effective_batch_year' => $newBatch,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->all()
            );

            foreach ($clos as $clo) {
                $newClo = CourseLearningOutcome::create([
                    'course_id' => $clo->course_id,
                    'code' => $clo->code,
                    'description' => $clo->description,
                    'blooms_taxonomy_id' => $clo->blooms_taxonomy_id,
                    'effective_batch_year' => $newBatch,
                ]);

                foreach ($clo->programOutcomes as $po) {
                    $newPo = ProgramOutcome::query()
                        ->where('program_id', $programId)
                        ->where('code', $po->code)
                        ->where('effective_batch_year', $newBatch)
                        ->first();

                    if ($newPo) {
                        $newClo->programOutcomes()->syncWithoutDetaching([
                            $newPo->id => ['level' => $po->pivot->level],
                        ]);
                        $mappedCount++;
                    }
                }
            }
        });

        $this->refreshSelectedCourses();

        $message = "Carried forward {$previousAssignments->count()} course(s) and {$clos->count()} CLO(s) from batch {$previousBatch} to batch {$newBatch}.";

        if ($mappedCount > 0) {
            $message .= " Re-mapped {$mappedCount} CLO-to-PO link(s) to the matching POs of batch {$newBatch}.";
        } elseif ($previousMappingsCount > 0) {
            $message .= " CLO-to-PO mappings were NOT carried forward because no matching POs exist for batch {$newBatch} yet. Add POs for this batch first, then re-map them under the CLO-to-PO mapping table.";
        }

        session()->flash('success', $message);
    }

    private function previousBatchWithCourses(): ?string
    {
        if (!$this->selectedProgramId || !$this->selectedBatchYear) {
            return null;
        }

        return DB::table('course_program')
            ->where('program_id', (int) $this->selectedProgramId)
            ->where('effective_batch_year', '<', (string) $this->selectedBatchYear)
            ->orderByDesc('effective_batch_year')
            ->value('effective_batch_year');
    }

    public function render()
    {
        $programs = Program::orderBy('name')->get();
        $courses = Course::orderBy('code')->get();

        $currentBatchCourses = collect();
        if ($this->selectedProgramId) {
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
        }

        $peos = collect();
        $programOutcomes = collect();
        if ($this->selectedProgramId) {
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
        }

        $batchOptions = AcademicYear::query()
            ->whereNotNull('start_year')
            ->orderBy('start_year', 'desc')
            ->get()
            ->map(fn ($academicYear) => (string) $academicYear->start_year)
            ->filter()
            ->unique()
            ->values();

        $previousBatchWithCourses = $this->previousBatchWithCourses();

        $copySourceBatches = collect();
        if ($this->selectedProgramId && $this->selectedBatchYear) {
            $assignedCourseIds = $currentBatchCourses->pluck('id')->toArray();

            if ($assignedCourseIds) {
                $copySourceBatches = CourseLearningOutcome::query()
                    ->whereIn('course_id', $assignedCourseIds)
                    ->where('effective_batch_year', '!=', (string) $this->selectedBatchYear)
                    ->whereNotNull('effective_batch_year')
                    ->get(['course_id', 'effective_batch_year'])
                    ->groupBy('course_id')
                    ->map(fn ($rows) => $rows->pluck('effective_batch_year')->unique()->sortDesc()->values());
            }
        }

        return view('livewire.admin.program-course-manager', [
            'programs' => $programs,
            'courses' => $courses,
            'batchOptions' => $batchOptions,
            'currentBatchCourses' => $currentBatchCourses,
            'peos' => $peos,
            'programOutcomes' => $programOutcomes,
            'previousBatchWithCourses' => $previousBatchWithCourses,
            'copySourceBatches' => $copySourceBatches,
            'taxonomies' => BloomsTaxonomy::orderBy('domain')->orderBy('code')->get(),
        ])->extends('layouts.admin')
            ->section('content');
    }
}