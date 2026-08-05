<?php

namespace App\Services;

use App\Models\AssessmentTask;
use App\Models\CourseBlock;
use App\Models\CourseLearningOutcome;
use App\Models\Peo;
use App\Models\Program;
use App\Models\ProgramOutcome;

/**
 * Resolves the auto-displayed syllabus data (PEO, PO, COs, CO-PO mapping,
 * Assessment Tasks, and course descriptive data) for a given course block.
 */
class CourseSyllabusData
{
    public function __construct(
        private CourseBlock $block,
        private ?Program $programOverride = null
    ) {
    }

    public function block(): CourseBlock
    {
        return $this->block;
    }

    /**
     * The distinct programs served by this block's sections (used to decide
     * whether one or multiple syllabi need to be generated).
     */
    public function programs(): \Illuminate\Support\Collection
    {
        $sections = $this->block->sections()->get();

        return $sections->pluck('program')
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * The program that owns the current syllabus. A supplied override always
     * wins; otherwise prefer the direct program_id FK, then the single
     * distinct program of the block's sections, then the course-program
     * pivot (used when a course spans multiple programs).
     */
    public function program(): ?Program
    {
        if ($this->programOverride) {
            return $this->programOverride;
        }

        $course = $this->block->course;

        if ($course && $course->program_id) {
            return $course->program;
        }

        $sectionPrograms = $this->sectionPrograms();

        if ($sectionPrograms->count() === 1) {
            return $sectionPrograms->first();
        }

        $program = $this->preferProgram($sectionPrograms);

        if ($program) {
            return $program;
        }

        if ($course && $course->programs()->count() > 0) {
            return $course->programs()->first();
        }

        return null;
    }

    /**
     * Pick the highest-priority program from the given programs. When a block
     * serves multiple programs, BSIS wins, then BTVTED, then DIT/DHRT, then ACT.
     */
    private function preferProgram($programs): ?Program
    {
        $priority = [1 => 1, 6 => 2, 7 => 2, 8 => 3, 9 => 4, 2 => 5, 3 => 5, 4 => 5, 5 => 5];

        return $programs->sortBy(fn ($program) => $priority[$program->id] ?? 99)->first();
    }

    /**
     * The distinct programs of the block's sections (pivot first, falling
     * back to the legacy section_id column).
     */
    public function sectionPrograms()
    {
        return $this->programs();
    }

    public function batchYear(): ?string
    {
        $batch = $this->block->batchYear();

        return $batch !== null ? (string) $batch : null;
    }

    public function peos()
    {
        $program = $this->program();
        if (!$program) {
            return collect();
        }

        $query = Peo::where('program_id', $program->id);

        if ($this->batchYear()) {
            $query->where('effective_batch_year', $this->batchYear());
        }

        return $query->orderBy('code')->get();
    }

    public function programOutcomes()
    {
        $program = $this->program();
        if (!$program) {
            return collect();
        }

        $query = ProgramOutcome::where('program_id', $program->id);

        if ($this->batchYear()) {
            $query->where('effective_batch_year', $this->batchYear());
        }

        return $query->orderBy('code')->get();
    }

    /**
     * Course outcomes (COs/CLOs), loaded with their CO-PO mapping and Bloom's
     * taxonomy for the syllabus render.
     */
    public function courseLearningOutcomes()
    {
        $query = CourseLearningOutcome::with(['bloomsTaxonomy', 'programOutcomes'])
            ->where('course_id', $this->block->course_id);

        if ($this->batchYear()) {
            $query->where('effective_batch_year', $this->batchYear());
        }

        return $query->orderBy('code')->get();
    }

    /**
     * Assessment tasks with their mapped items for this course/batch.
     */
    public function assessmentTasks()
    {
        $query = AssessmentTask::with('items.clo')
            ->where('course_id', $this->block->course_id);

        if ($this->batchYear()) {
            $query->where('effective_batch_year', $this->batchYear());
        }

        return $query->orderBy('created_at')->get();
    }

    /**
     * Assessment tasks mapped to the given course outcome (via their items).
     */
    public function tasksForClo(CourseLearningOutcome $clo)
    {
        return AssessmentTask::where('course_id', $this->block->course_id)
            ->whereIn('id', $clo->assessmentItems()->pluck('assessment_task_id'))
            ->orderBy('created_at')
            ->get();
    }

    /**
     * CO-PO mapping level (I/G/A) for the given CO and PO.
     */
    public function coPoLevel(CourseLearningOutcome $clo, ProgramOutcome $po): string
    {
        return (string) $clo->programOutcomes->firstWhere('id', $po->id)?->pivot?->level ?? '';
    }

    /**
     * All auto-displayed data as one associative array for views/exports.
     */
    public function toArray(): array
    {
        $block = $this->block;
        $course = $block->course;

        return [
            'block' => $block,
            'course' => $course,
            'program' => $this->program(),
            'batch_year' => $this->batchYear(),
            'academic_year_label' => optional($block->academicYear)->label,
            'semester' => $block->semester,
            'sections' => $this->sectionLabels(),
            'schedule' => $block->schedule_string,
            'peos' => $this->peos(),
            'program_outcomes' => $this->programOutcomes(),
            'course_learning_outcomes' => $this->courseLearningOutcomes(),
            'assessment_tasks' => $this->assessmentTasks(),
        ];
    }

    public function sectionLabels(): string
    {
        $sections = $this->block->sections()->get();

        return $sections->map(function ($section) {
            $program = $section->program->name ?? '';
            return $program ? "{$program}-{$section->name}" : ($section->name ?? '');
        })->unique()->implode(', ');
    }
}