<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Program;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\CourseBlock;
use App\Models\Student;
use App\Models\StudentAssessmentMark;
use Illuminate\Support\Facades\Auth;

class ObeCourseDashboard extends Component
{
    public $selectedProgramId = null;
    public $selectedAcademicYearId = null;
    public $selectedSemester = null;
    public $thresholdPercentage = 60;

    public $semesters = ['1st', '2nd', 'Summer'];

    private function normalizeSemester($value): ?string
    {
        $value = strtolower(trim((string) $value));

        if (in_array($value, ['1st', 'first', '1st semester', 'first semester', 'semester 1', 'sem 1', '1st sem', '1'])) {
            return '1st';
        }

        if (in_array($value, ['2nd', 'second', '2nd semester', 'second semester', 'semester 2', 'sem 2', '2nd sem', '2'])) {
            return '2nd';
        }

        if (in_array($value, ['summer', 'summer term', '3rd', 'third', '3rd semester', 'third semester', 'semester 3', 'sem 3', '3'])) {
            return 'Summer';
        }

        return null;
    }

    public function mount(): void
    {
        $this->selectedAcademicYearId = AcademicYear::orderByDesc('start_year')->value('id');
    }

    private function isAdminView(): bool
    {
        $user = Auth::user();

        return $user && ($user->hasRole('admin')
            || $user->hasRole('academic_head')
            || $user->hasRole('hr')
            || $user->hasRole('registrar')
            || $user->hasRole('program_head_shs'));
    }

    private function facultyId(): ?int
    {
        return Auth::user()?->employee?->id;
    }

    private function semesterFilterValues(): array
    {
        $storedSemesters = CourseBlock::distinct()->pluck('semester')->filter();

        if (!$this->selectedSemester) {
            return [];
        }

        $values = $storedSemesters
            ->filter(fn ($s) => $this->normalizeSemester($s) === $this->selectedSemester)
            ->values()
            ->all();

        return $values ?: [$this->selectedSemester];
    }

    public function render()
    {
        $programs = Program::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();

        $storedSemesters = CourseBlock::distinct()->pluck('semester')->filter();
        $semesterOptions = $storedSemesters
            ->map(fn ($s) => $this->normalizeSemester($s))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $this->semesters = $semesterOptions->isNotEmpty() ? $semesterOptions->all() : ['1st', '2nd', 'Summer'];

        $isAdminView = $this->isAdminView();

        $batchYear = null;
        if ($this->selectedAcademicYearId) {
            $batchYear = (string) (AcademicYear::find($this->selectedAcademicYearId)?->start_year);
        }

        [$courses, $allStudentIds] = $this->getFilteredCourses();

        $totalClos = 0;
        $attainedClos = 0;
        $attainmentScores = [];
        $poAggregates = [];

        foreach ($courses as $course) {
            $totalClos += $course->clo_attainments->count();

            foreach ($course->clo_attainments as $clo) {
                if (!is_null($clo->completion_rate)) {
                    $attainmentScores[] = $clo->completion_rate;
                    if ($clo->completion_rate >= $this->thresholdPercentage) {
                        $attainedClos++;
                    }
                }
            }

            foreach ($course->po_attainments as $po) {
                if (!isset($poAggregates[$po['code']])) {
                    $poAggregates[$po['code']] = [
                        'description' => $po['description'],
                        'scores' => [],
                    ];
                }
                if (!is_null($po['score'])) {
                    $poAggregates[$po['code']]['scores'][] = $po['score'];
                }
            }
        }

        $overallPoAttainments = collect($poAggregates)
            ->map(function ($data, $code) {
                $score = !empty($data['scores'])
                    ? array_sum($data['scores']) / count($data['scores'])
                    : null;

                return [
                    'code' => $code,
                    'description' => $data['description'],
                    'score' => $score,
                    'attained' => !is_null($score) && $score >= $this->thresholdPercentage,
                ];
            })
            ->sortBy('code')
            ->values();

        if ($this->selectedAcademicYearId) {
            $batchGroups = $courses->isEmpty()
                ? collect()
                : collect([['batch' => (string) $batchYear, 'courses' => $courses]]);
        } else {
            $batchGroups = $this->buildBatchGroups($courses);
        }

        $latestAyWithDataId = AcademicYear::whereIn('id', CourseBlock::pluck('academic_year_id')->filter())
            ->orderByDesc('start_year')
            ->value('id');

        $overallAttainment = !empty($attainmentScores)
            ? array_sum($attainmentScores) / count($attainmentScores)
            : null;

        return view('livewire.admin.obe-course-dashboard', [
            'programs' => $programs,
            'academicYears' => $academicYears,
            'semesters' => $this->semesters,
            'courses' => $courses,
            'batchGroups' => $batchGroups,
            'isAdminView' => $isAdminView,
            'facultyName' => $isAdminView ? null : trim(optional(Auth::user()?->employee)->first_name . ' ' . optional(Auth::user()?->employee)->last_name),
            'totalCourses' => $courses->count(),
            'totalStudents' => $allStudentIds->unique()->count(),
            'totalClos' => $totalClos,
            'attainedClos' => $attainedClos,
            'overallAttainment' => $overallAttainment,
            'overallPoAttainments' => $overallPoAttainments,
            'latestAyWithDataId' => $latestAyWithDataId,
            'totalBlocks' => $courses->sum(fn ($course) => $course->courseBlocks->count()),
            'thresholdPercentage' => $this->thresholdPercentage,
        ])->extends('layouts.admin')
            ->section('content');
    }

    private function getFilteredCourses()
    {
        $isAdminView = $this->isAdminView();
        $facultyId = $this->facultyId();

        $batchYear = null;
        if ($this->selectedAcademicYearId) {
            $batchYear = (string) (AcademicYear::find($this->selectedAcademicYearId)?->start_year);
        }

        $programId = $this->selectedProgramId ? (int) $this->selectedProgramId : null;
        $semesterFilterValues = $this->semesterFilterValues();

        $courseQuery = Course::with([
            'learningOutcomes' => function ($query) use ($batchYear) {
                if ($batchYear) {
                    $query->where('effective_batch_year', $batchYear);
                }
                $query->with(['programOutcomes', 'bloomsTaxonomy'])->orderBy('code');
            },
            'assessmentTasks' => function ($query) use ($batchYear) {
                if ($batchYear) {
                    $query->where('effective_batch_year', $batchYear);
                }
                $query->with('items.clo');
            },
            'courseBlocks' => function ($query) use ($facultyId, $isAdminView, $programId, $semesterFilterValues) {
                if ($this->selectedAcademicYearId) {
                    $query->where('academic_year_id', $this->selectedAcademicYearId);
                }
                if (!empty($semesterFilterValues)) {
                    $query->whereIn('semester', $semesterFilterValues);
                }
                if ($programId) {
                    $query->where(function ($q) use ($programId) {
                        $q->whereHas('sections.program', function ($sq) use ($programId) {
                            $sq->where('programs.id', $programId);
                        });
                    });
                }
                if (!$isAdminView && $facultyId) {
                    $query->where('faculty_id', $facultyId);
                }
                $query->with(['faculty', 'sections', 'academicYear', 'students']);
            },
        ]);

        if ($this->selectedProgramId) {
            $courseQuery->whereHas('programs', function ($query) {
                $query->where('programs.id', $this->selectedProgramId);
            });
        }

        if (!$isAdminView && $facultyId) {
            $courseQuery->whereHas('courseBlocks', function ($query) use ($facultyId) {
                $query->where('faculty_id', $facultyId);
                if ($this->selectedAcademicYearId) {
                    $query->where('academic_year_id', $this->selectedAcademicYearId);
                }
            });
        }

        $courses = $courseQuery->orderBy('code')->orderBy('name')->get();

        $allStudentIds = collect();

        foreach ($courses as $course) {
            $allStudentIds = $allStudentIds->merge($this->computeCourseAttainment($course));
        }

        return [$courses, $allStudentIds];
    }

    public function exportCsv()
    {
        $batchYear = null;
        if ($this->selectedAcademicYearId) {
            $batchYear = (string) (AcademicYear::find($this->selectedAcademicYearId)?->start_year);
        }

        [$courses] = $this->getFilteredCourses();

        if ($this->selectedAcademicYearId) {
            $batchGroups = $courses->isEmpty()
                ? collect()
                : collect([['batch' => (string) $batchYear, 'courses' => $courses]]);
        } else {
            $batchGroups = $this->buildBatchGroups($courses);
        }

        $programLabel = $this->selectedProgramId
            ? (Program::find($this->selectedProgramId)?->name ?? 'All Programs')
            : 'All Programs';

        $semesterLabel = $this->selectedSemester ?: 'All Semesters';

        return response()->streamDownload(function () use ($batchGroups, $programLabel, $semesterLabel) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['OBE Course Attainment Export']);
            fputcsv($out, ['Program', $programLabel]);
            fputcsv($out, ['Semester', $semesterLabel]);
            fputcsv($out, ['Threshold', $this->thresholdPercentage . '%']);
            fputcsv($out, ['']);
            fputcsv($out, ['batch', 'course_code', 'course_name', 'row_type', 'code', 'name', 'attainment_%', 'assessed', 'total']);

            foreach ($batchGroups as $group) {
                foreach ($group['courses'] as $course) {
                    $batch = $group['batch'];

                    fputcsv($out, [
                        $batch,
                        $course->code,
                        $course->name,
                        'COURSE',
                        $course->code,
                        $course->name,
                        is_null($course->computed_completion_rate) ? '' : number_format($course->computed_completion_rate, 1),
                        $course->clo_attainments->filter(fn ($clo) => !is_null($clo->completion_rate))->count(),
                        $course->clo_attainments->count(),
                    ]);

                    foreach ($course->clo_attainments as $clo) {
                        fputcsv($out, [
                            $batch,
                            $course->code,
                            $course->name,
                            'CLO',
                            $clo->code,
                            $clo->description,
                            is_null($clo->completion_rate) ? '' : number_format($clo->completion_rate, 1),
                            $clo->students_assessed,
                            $clo->total_students,
                        ]);
                    }

                    foreach ($course->po_attainments as $po) {
                        fputcsv($out, [
                            $batch,
                            $course->code,
                            $course->name,
                            'PO',
                            $po['code'],
                            $po['description'],
                            is_null($po['score']) ? '' : number_format($po['score'], 1),
                            '',
                            '',
                        ]);
                    }

                    foreach ($course->courseBlocks as $block) {
                        $sections = $block->sections->pluck('name')->filter()->unique()->implode(', ');

                        fputcsv($out, [
                            $batch,
                            $course->code,
                            $course->name,
                            'BLOCK',
                            'Block #' . $block->id,
                            $sections,
                            is_null($block->attainment) ? '' : number_format($block->attainment, 1),
                            $block->assessed_students,
                            $block->student_count,
                        ]);

                        foreach ($block->student_details ?? collect() as $student) {
                            fputcsv($out, [
                                $batch,
                                $course->code,
                                $course->name,
                                'STUDENT',
                                $student['student_number'],
                                $student['student_name'],
                                is_null($student['percentage']) ? '' : number_format($student['percentage'], 1),
                                $student['clo_count'],
                                '',
                            ]);
                        }
                    }
                }
            }

            fclose($out);
        }, 'obe-course-attainment.csv', ['Content-Type' => 'text/csv']);
    }

    private function computeCourseAttainment(Course $course)
    {
        $studentIds = $course->courseBlocks
            ->flatMap(fn ($block) => $block->students->pluck('id'))
            ->unique()
            ->values();

        $students = $studentIds->isNotEmpty()
            ? Student::with('sections.program')->whereIn('id', $studentIds)->get()->keyBy('id')
            : collect();

        if ($this->selectedProgramId) {
            $studentIds = $studentIds
                ->filter(function ($studentId) use ($students) {
                    $student = $students->get((int) $studentId);
                    return $student && $this->studentProgramId($student) === (int) $this->selectedProgramId;
                })
                ->values();
        }

        $course->total_students = $studentIds->count();

        $course->clo_attainments = collect();
        $course->faculty_names = $course->courseBlocks
            ->map(fn ($block) => trim(($block->faculty->first_name ?? '') . ' ' . ($block->faculty->last_name ?? '')))
            ->filter()
            ->unique()
            ->values();

        $poAggregates = [];

        foreach ($course->learningOutcomes as $clo) {
            $items = $course->assessmentTasks
                ->flatMap(fn ($task) => $task->items
                    ->each(fn ($item) => $item->task_title = $task->title))
                ->filter(fn ($item) => (int) $item->course_learning_outcome_id === (int) $clo->id)
                ->values();

            $itemIds = $items->pluck('id');

            $clo->assessment_items = $items;
            $clo->total_students = $studentIds->count();
            $clo->students_assessed = 0;
            $clo->completion_rate = null;
            $clo->student_breakdown = collect();

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

            $course->clo_attainments->push($clo);

            foreach ($clo->programOutcomes as $po) {
                if (!isset($poAggregates[$po->id])) {
                    $poAggregates[$po->id] = [
                        'code' => $po->code,
                        'description' => $po->description,
                        'scores' => [],
                    ];
                }
                if (!is_null($clo->completion_rate)) {
                    $poAggregates[$po->id]['scores'][] = $clo->completion_rate;
                }
            }
        }

        $course->po_attainments = collect($poAggregates)
            ->map(function ($data) {
                $score = !empty($data['scores'])
                    ? array_sum($data['scores']) / count($data['scores'])
                    : null;

                return [
                    'code' => $data['code'],
                    'description' => $data['description'],
                    'score' => $score,
                    'attained' => !is_null($score) && $score >= $this->thresholdPercentage,
                ];
            })
            ->values();

        $courseRates = $course->clo_attainments
            ->pluck('completion_rate')
            ->filter(fn ($rate) => $rate !== null);

        $course->computed_completion_rate = $courseRates->isNotEmpty()
            ? $courseRates->avg()
            : null;

        foreach ($course->courseBlocks as $block) {
            $blockStudentIds = $block->students
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $students->has($id))
                ->values();

            $block->student_count = $blockStudentIds->count();

            $blockRates = [];
            $blockAssessed = 0;
            $studentDetails = [];

            foreach ($blockStudentIds as $studentId) {
                $student = $students->get($studentId);
                if (!$student) {
                    continue;
                }

                $studentRates = [];
                $studentClos = 0;

                foreach ($course->clo_attainments as $clo) {
                    $entry = $clo->student_breakdown
                        ->firstWhere('student_id', $studentId);

                    if ($entry) {
                        $studentRates[] = $entry['percentage'];
                        $studentClos++;
                    }
                }

                $studentDetails[] = [
                    'student_id' => $studentId,
                    'student_name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                    'student_number' => $student->student_id,
                    'percentage' => !empty($studentRates)
                        ? array_sum($studentRates) / count($studentRates)
                        : null,
                    'clo_count' => $studentClos,
                ];
            }

            $block->student_details = collect($studentDetails)
                ->sortByDesc(fn ($entry) => $entry['percentage'] ?? -1)
                ->values();

            foreach ($course->clo_attainments as $clo) {
                $matching = $clo->student_breakdown
                    ->filter(fn ($entry) => $blockStudentIds->contains($entry['student_id']));

                if ($matching->isNotEmpty()) {
                    $blockRates[] = $matching->avg('percentage');
                    $blockAssessed += $matching->count();
                }
            }

            $block->attainment = !empty($blockRates)
                ? array_sum($blockRates) / count($blockRates)
                : null;
            $block->assessed_students = $blockAssessed;
        }

        return $studentIds;
    }

    private function studentProgramId(Student $student): ?int
    {
        $latest = $student->sections
            ->sortByDesc(fn ($section) => $section->pivot?->created_at?->timestamp ?: 0)
            ->first();

        return $latest?->program_id;
    }

    private function buildBatchGroups($courses)
    {
        $groups = [];

        foreach ($courses as $course) {
            $batches = collect()
                ->merge($course->courseBlocks->map(fn ($block) => $block->academicYear?->start_year))
                ->merge($course->learningOutcomes->pluck('effective_batch_year'))
                ->merge($course->assessmentTasks->pluck('effective_batch_year'))
                ->map(fn ($year) => $year === null || $year === '' ? null : (string) $year)
                ->unique()
                ->values();

            if ($batches->isEmpty()) {
                $batches = collect([null]);
            }

            foreach ($batches as $batch) {
                $copy = clone $course;

                $copy->setRelation(
                    'learningOutcomes',
                    $course->learningOutcomes->filter(function ($clo) use ($batch) {
                        return ($clo->effective_batch_year === null || $clo->effective_batch_year === '')
                            ? $batch === null
                            : (string) $clo->effective_batch_year === $batch;
                    })
                );

                $copy->setRelation(
                    'assessmentTasks',
                    $course->assessmentTasks->filter(function ($task) use ($batch) {
                        return ($task->effective_batch_year === null || $task->effective_batch_year === '')
                            ? $batch === null
                            : (string) $task->effective_batch_year === $batch;
                    })
                );

                $copy->setRelation(
                    'courseBlocks',
                    $course->courseBlocks->filter(function ($block) use ($batch) {
                        $year = $block->academicYear?->start_year;
                        return ($year === null || $year === '') ? $batch === null : (string) $year === $batch;
                    })
                );

                $this->computeCourseAttainment($copy);

                $label = $batch ?? 'Legacy';
                $groups[$label][] = $copy;
            }
        }

        $grouped = collect($groups)
            ->map(fn ($courseList, $label) => [
                'batch' => $label,
                'courses' => collect($courseList)->sortBy('code')->values(),
            ])
            ->sortKeysDesc()
            ->values();

        if ($grouped->isNotEmpty() && $grouped->last()['batch'] === 'Legacy') {
            $legacy = $grouped->last();
            $grouped = $grouped->filter(fn ($group) => $group['batch'] !== 'Legacy')->push($legacy);
        }

        return $grouped;
    }
}
