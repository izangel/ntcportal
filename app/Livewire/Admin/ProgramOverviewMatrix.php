<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Program;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentAssessmentMark;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class ProgramOverviewMatrix extends Component
{
    public $selectedProgramId = null;
    public $selectedBatchYear = null;

    public function mount()
    {
        $this->selectedBatchYear = $this->getAvailableBatchYears()->first();
    }

    /**
     * Livewire lifecycle hook: Runs automatically when batch dropdown changes
     */
    public function updatedSelectedBatchYear()
    {
        // Re-evaluates component state and triggers Blade view refresh
    }

    /**
     * Livewire lifecycle hook: Runs automatically when program dropdown changes
     */
    public function updatedSelectedProgramId()
    {
        // Re-evaluates component state and triggers Blade view refresh
    }

    /**
     * Determine whether a versioned item is effective for the selected batch.
     */
    private function isEffectiveForBatch($effectiveBatchYear, $selectedBatchYear): bool
    {
        if (empty($selectedBatchYear)) {
            return true;
        }

        if (empty($effectiveBatchYear)) {
            return false;
        }

        $targetYear = $this->normalizeBatchYear($selectedBatchYear);
        if ($targetYear === null) {
            return true;
        }

        $years = [];
        if (preg_match_all('/\d{4}/', (string) $effectiveBatchYear, $matches)) {
            $years = array_map('intval', $matches[0]);
        }

        if (empty($years)) {
            return false;
        }

        if (count($years) === 1) {
            return $years[0] === $targetYear;
        }

        $startYear = $years[0];
        $endYear = $years[count($years) - 1];

        return $targetYear >= $startYear && $targetYear <= $endYear;
    }

    private function normalizeBatchYear($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (preg_match('/\d{4}/', (string) $value, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    /**
     * Get students enrolled in the selected academic-year start and a
     * first-year section. The batch is defined by the enrollment year, not by
     * the student's stored/calculated batch accessor.
     */
    private function getBatchStudentIds()
    {
        if (!$this->selectedBatchYear) {
            return null; // Return null if "All Batches" is selected
        }

        $batchStudentIds = DB::table('section_student')
            ->join('academic_years', 'academic_years.id', '=', 'section_student.academic_year_id')
            ->join('sections', 'sections.id', '=', 'section_student.section_id')
            ->where('academic_years.start_year', $this->selectedBatchYear)
            ->where('sections.name', 'like', '%1%')
            ->distinct()
            ->pluck('section_student.student_id')
            ->toArray();

        if (empty($batchStudentIds)) {
            return [];
        }

        $latestTerm = DB::table('section_student')
            ->select('academic_year_id', 'semester')
            ->selectRaw("
                CASE
                    WHEN semester LIKE '%1%' OR LOWER(semester) LIKE '%first%' THEN 1
                    WHEN semester LIKE '%2%' OR LOWER(semester) LIKE '%second%' THEN 2
                    WHEN semester LIKE '%3%' OR LOWER(semester) LIKE '%summer%' THEN 3
                    ELSE 0
                END as normalized_semester
            ")
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('normalized_semester', 'desc')
            ->first();

        if (!$latestTerm) {
            return [];
        }

        return DB::table('section_student')
            ->where('academic_year_id', $latestTerm->academic_year_id)
            ->where('semester', $latestTerm->semester)
            ->whereIn('student_id', $batchStudentIds)
            ->distinct()
            ->pluck('student_id')
            ->toArray();
    }

    /**
     * Fetch available batch options dynamically from existing student records.
     */
    private function getAvailableBatchYears()
    {
        return AcademicYear::query()
            ->whereNotNull('start_year')
            ->orderBy('start_year', 'desc')
            ->pluck('start_year')
            ->map(fn ($year) => (string) $year)
            ->unique()
            ->values();
    }

    /**
     * Retrieves and formats students belonging to the selected batch for debugging.
     * Sorted alphabetically by Section name.
     */
    private function getBatchStudentsForDebug($batchStudentIds)
    {
        if (!$this->selectedBatchYear || empty($batchStudentIds)) {
            return collect();
        }

        $query = Student::with(['sections.program'])
            ->whereIn('id', $batchStudentIds);

        // Filter by selected program if one is chosen
        if ($this->selectedProgramId) {
            $query->whereHas('sections', function ($q) {
                $q->where('program_id', $this->selectedProgramId);
            });
        }

        return $query->get()->sortBy(function ($student) {
            return $student->sections->pluck('name')->implode(', ');
        });
    }

   public function render()
{
    $programs = Program::all();
    $batchYears = $this->getAvailableBatchYears();
    $batchStudentIds = $this->getBatchStudentIds();
    
   
    // 1. Inline query to get debug students (with sections & programs)
    $debugStudents = collect();
    
    if ($this->selectedBatchYear && !empty($batchStudentIds)) {
        $studentQuery = Student::with(['sections.program'])
            ->whereIn('id', $batchStudentIds);

        if ($this->selectedProgramId) {
            $studentQuery->whereHas('sections', function ($q) {
                $q->where('program_id', $this->selectedProgramId);
            });
        }

        $debugStudents = $studentQuery->get()->sortBy(function ($student) {
            return $student->sections->pluck('name')->implode(', ');
        });
    }

    // Extract student IDs belonging strictly to this program and batch
    $programBatchStudentIds = $debugStudents->pluck('id')->toArray();
    $activeSectionIds = $debugStudents->pluck('sections')->flatten()->pluck('id')->unique();

    $selectedProgram = $this->selectedProgramId
        ? Program::with(['programEducationalObjectives', 'programOutcomes'])->find($this->selectedProgramId)
        : null;

    if ($selectedProgram) {
        $selectedProgram->setRelation(
            'programEducationalObjectives',
            $selectedProgram->programEducationalObjectives
                ->filter(fn ($peo) => $this->isEffectiveForBatch($peo->effective_batch_year, $this->selectedBatchYear))
                ->values()
        );

        $selectedProgram->setRelation(
            'programOutcomes',
            $selectedProgram->programOutcomes
                ->filter(fn ($po) => $this->isEffectiveForBatch($po->effective_batch_year, $this->selectedBatchYear))
                ->values()
        );
    }

    $courses = collect();

    if ($selectedProgram) {
    $courses = Course::whereHas('programs', function ($query) {
            $query->where('programs.id', $this->selectedProgramId);
            if ($this->selectedBatchYear) {
                $query->where('course_program.effective_batch_year', $this->selectedBatchYear);
            }
        })
        ->with([
            'learningOutcomes',
            'learningOutcomes.programOutcomes', 
            'assessmentTasks',
            'courseBlocks' => function ($q) use ($activeSectionIds) {
                $q->whereHas('sections', function ($sq) use ($activeSectionIds) {
                    $sq->whereIn('sections.id', $activeSectionIds);
                });
            }
        ])
        ->orderBy('code')
        ->orderBy('name')
        ->get();

        foreach ($courses as $course) {
            $course->setRelation(
                'learningOutcomes',
                $course->learningOutcomes
                    ->filter(fn ($clo) => $this->isEffectiveForBatch($clo->effective_batch_year, $this->selectedBatchYear))
                    ->values()
            );

            $course->setRelation(
                'assessmentTasks',
                $course->assessmentTasks
                    ->filter(fn ($task) => $this->isEffectiveForBatch($task->effective_batch_year, $this->selectedBatchYear))
                    ->map(function ($task) {
                        $task->setRelation(
                            'items',
                            $task->items
                                ->filter(fn ($item) => $this->isEffectiveForBatch($item->effective_batch_year, $this->selectedBatchYear))
                                ->values()
                        );

                        return $task;
                    })
                    ->values()
            );

            $course->matched_course_blocks = $course->courseBlocks->where('course_id', $course->id);

            // 1. TOTAL ENROLLED BATCH STUDENTS
            $course->total_students = count($programBatchStudentIds);

            // -------------------------------------------------------------
            // 2. GET DISTINCT STUDENT IDs WITH MARKS FOR THIS COURSE
            // -------------------------------------------------------------
            $studentsWithMarksIds = [];

            if (!empty($programBatchStudentIds)) {
                $studentsWithMarksIds = StudentAssessmentMark::whereHas('assessmentItem.task', function ($q) use ($course) {
                        $q->where('course_id', $course->id);
                    })
                    ->whereIn('student_id', $programBatchStudentIds)
                    ->distinct('student_id')
                    ->pluck('student_id')
                    ->toArray();
            }

            $course->total_students_with_marks = count($studentsWithMarksIds);

            
            // -------------------------------------------------------------
            // 3. COMPUTE CLO ATTAINMENT DIRECTLY FROM BREAKDOWN
            // -------------------------------------------------------------
            $validCloAttainments = [];

            foreach ($course->learningOutcomes as $clo) {
                // Start clean
                $clo->student_breakdown = collect();
                $clo->attainment = null;

                if (empty($studentsWithMarksIds)) {
                    continue;
                }

                // Query breakdown
                $breakdown = StudentAssessmentMark::whereHas('assessmentItem', function ($q) use ($clo) {
                        $q->where('course_learning_outcome_id', $clo->id);
                    })
                    ->whereIn('student_id', $studentsWithMarksIds)
                    ->join('assessment_items', 'student_assessment_marks.assessment_item_id', '=', 'assessment_items.id')
                    ->where('assessment_items.course_learning_outcome_id', $clo->id)
                    ->selectRaw('
                        student_assessment_marks.student_id,
                        SUM(student_assessment_marks.marks_obtained) as total_obtained,
                        SUM(assessment_items.max_marks) as total_max
                    ')
                    ->groupBy('student_assessment_marks.student_id')
                    ->get()
                    ->reject(function ($row) {
                        return is_null($row->total_max) || $row->total_max == 0;
                    })
                    ->map(function ($row) {
                        return [
                            'student_id'     => $row->student_id,
                            'total_obtained' => $row->total_obtained,
                            'total_max'      => $row->total_max,
                            'percentage'     => ($row->total_obtained / $row->total_max) * 100
                        ];
                    })
                    ->values();

                if ($breakdown->isNotEmpty()) {
                    $clo->student_breakdown = $breakdown;
                    $clo->attainment = $breakdown->avg('percentage');
                    $validCloAttainments[] = $clo->attainment;
                }
            }

            // 4. Overall Course Attainment Rate (Explicitly set to NULL if empty)
            $course->completion_rate = !empty($validCloAttainments)
                ? (array_sum($validCloAttainments) / count($validCloAttainments))
                : null;

        }
    }

    return view('livewire.admin.program-overview-matrix', [
        'programs'        => $programs,
        'selectedProgram' => $selectedProgram,
        'courses'         => $courses,
        'batchYears'      => $batchYears,
        'debugStudents'   => $debugStudents,
    ])->extends('layouts.admin')
      ->section('content');
}
}