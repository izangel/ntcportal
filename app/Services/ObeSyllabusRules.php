<?php

namespace App\Services;

use App\Models\AssessmentItem;
use App\Models\AssessmentTask;
use App\Models\CourseBlock;
use App\Models\CourseLearningOutcome;
use App\Models\Program;
use App\Models\ProgramOutcome;

/**
 * Enforces the OBE mapping rules for a syllabus before it can be prepared:
 *   1. Assessment task weights for a course+batch must total 100%.
 *   2. Every course learning outcome must be mapped to at least one Program
 *      Outcome of the syllabus program and to at least one assessment item.
 *
 * A task's total marks are not validated — they are derived automatically
 * from its items' max_marks.
 */
class ObeSyllabusRules
{
    /**
     * All violations for a block's syllabus as human-readable messages.
     *
     * @return array<int, string>
     */
    public static function violations(CourseBlock $block, ?Program $program = null): array
    {
        $violations = [];

        $batch = (string) $block->batchYear();

        $violations = array_merge(
            $violations,
            self::assessmentWeightViolations($block->course_id, $batch)
        );

        $violations = array_merge(
            $violations,
            self::cloMappingViolations($block->course_id, $batch, $program)
        );

        $violations = array_merge(
            $violations,
            self::taskCloMappingViolations($block->course_id, $batch)
        );

        return $violations;
    }

    /**
     * Rule 1: the sum of weight_percentage across all assessment tasks for the
     * course+batch must be 100%.
     *
     * @return array<int, string>
     */
    public static function assessmentWeightViolations(int $courseId, ?string $batch): array
    {
        $tasks = AssessmentTask::where('course_id', $courseId)
            ->where('effective_batch_year', $batch)
            ->get();

        if ($tasks->isEmpty()) {
            return ['Assessment tasks are required; set up at least one assessment task.'];
        }

        $total = (float) $tasks->sum('weight_percentage');

        if (abs($total - 100.0) > 0.001) {
            return ["Assessment task weights total {$total}%; they must total 100%."];
        }

        return [];
    }

    /**
     * Rule 2: each active CLO of the course+batch must map to at least one PO
     * of the syllabus program and to at least one assessment item.
     *
     * @return array<int, string>
     */
    public static function cloMappingViolations(int $courseId, ?string $batch, ?Program $program): array
    {
        if (!$program) {
            return ['No program resolved for this course block.'];
        }

        $clos = CourseLearningOutcome::with('programOutcomes')
            ->where('course_id', $courseId)
            ->where('is_active', true)
            ->where('effective_batch_year', $batch)
            ->orderBy('code')
            ->get();

        if ($clos->isEmpty()) {
            return [];
        }

        $programPoIds = ProgramOutcome::where('program_id', $program->id)
            ->where('effective_batch_year', $batch)
            ->pluck('id');

        $itemCloIds = AssessmentItem::where('effective_batch_year', $batch)
            ->whereIn('course_learning_outcome_id', $clos->pluck('id'))
            ->pluck('course_learning_outcome_id');

        $violations = [];

        foreach ($clos as $clo) {
            $hasPo = $clo->programOutcomes
                ->pluck('id')
                ->intersect($programPoIds)
                ->isNotEmpty();

            $hasItem = $itemCloIds->contains($clo->id);

            if (!$hasPo) {
                $violations[] = "{$clo->code} has no mapped Program Outcome for {$program->name}.";
            }

            if (!$hasItem) {
                $violations[] = "{$clo->code} has no mapped assessment item.";
            }
        }

        return $violations;
    }

    /**
     * Rule 3: each assessment task must have at least one item mapped to a
     * CLO, so that every assessment is relevant to a course learning outcome.
     *
     * @return array<int, string>
     */
    public static function taskCloMappingViolations(int $courseId, ?string $batch): array
    {
        $tasks = AssessmentTask::with('items')
            ->where('course_id', $courseId)
            ->where('effective_batch_year', $batch)
            ->get();

        $violations = [];

        foreach ($tasks as $task) {
            if ($task->items->isEmpty()) {
                $violations[] = "{$task->title} has no mapped assessment item; every assessment task must map to a CLO.";
            }
        }

        return $violations;
    }

    /**
     * Whether the block's syllabus satisfies all rules.
     */
    public static function passes(CourseBlock $block, ?Program $program = null): bool
    {
        return empty(self::violations($block, $program));
    }
}
