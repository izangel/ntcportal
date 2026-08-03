<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\CourseBlock;
use App\Models\AssessmentTask;
use App\Models\StudentAssessmentMark;
use App\Models\CourseAttainment;

/**
 * Determines how much of the OBE data (assessment setup, scores, CLO attainment)
 * is still missing for one or more course blocks.
 */
class ObeDataCompleteness
{
    public const MISSING_ASSESSMENT = 'assessment_tasks';
    public const MISSING_SCORES = 'scores';
    public const MISSING_ATTAINMENT = 'attainment';

    public static function labels(): array
    {
        return [
            self::MISSING_ASSESSMENT => 'Assessment setup (tasks / items)',
            self::MISSING_SCORES => 'Assessment scores',
            self::MISSING_ATTAINMENT => 'CLO attainment report',
        ];
    }

    /**
     * Evaluate many blocks efficiently (a handful of queries instead of N+1).
     *
     * @param  Collection<int, CourseBlock>  $blocks
     * @return array<int, array<int, string>>  map of block id => list of missing keys
     */
    public static function evaluateMany(Collection $blocks): array
    {
        $result = [];

        if ($blocks->isEmpty()) {
            return $result;
        }

        $blocksByBatch = $blocks->groupBy(fn ($block) => (string) optional($block->academicYear)->start_year);

        foreach ($blocksByBatch as $batchYear => $batchBlocks) {
            $courseIds = $batchBlocks->pluck('course_id')->unique()->values();

            $tasks = AssessmentTask::whereIn('course_id', $courseIds)
                ->where('effective_batch_year', $batchYear)
                ->with('items')
                ->get();

            $tasksByCourse = $tasks->groupBy('course_id');

            $itemCourseMap = [];
            foreach ($tasks as $task) {
                foreach ($task->items as $item) {
                    $itemCourseMap[$item->id] = $task->course_id;
                }
            }

            $allItemIds = array_keys($itemCourseMap);

            $marks = !empty($allItemIds)
                ? StudentAssessmentMark::whereIn('assessment_item_id', $allItemIds)
                    ->select('student_id', 'assessment_item_id')
                    ->get()
                : collect();

            $markedStudentsByCourse = [];
            foreach ($marks as $mark) {
                $courseId = $itemCourseMap[$mark->assessment_item_id] ?? null;
                if ($courseId) {
                    $markedStudentsByCourse[$courseId][$mark->student_id] = true;
                }
            }

            $attainedBlockIds = CourseAttainment::whereIn('course_session_id', $batchBlocks->pluck('id'))
                ->whereIn('status', ['submitted', 'reviewed', 'approved'])
                ->pluck('course_session_id')
                ->flip();

            foreach ($batchBlocks as $block) {
                $courseTasks = $tasksByCourse->get($block->course_id, collect());

                $missing = [];

                $hasAssessmentSetup = $courseTasks->isNotEmpty()
                    && $courseTasks->flatMap(fn ($task) => $task->items)->isNotEmpty();

                if (!$hasAssessmentSetup) {
                    $missing[] = self::MISSING_ASSESSMENT;
                }

                $enrolledStudentIds = $block->students->pluck('id')->all();
                $enrolledCount = count($enrolledStudentIds);

                if ($enrolledCount > 0 && !empty($allItemIds)) {
                    $markedForCourse = array_keys($markedStudentsByCourse[$block->course_id] ?? []);
                    $markedCount = count(array_intersect($enrolledStudentIds, $markedForCourse));

                    if ($markedCount < $enrolledCount) {
                        $missing[] = self::MISSING_SCORES;
                    }
                }

                if (!$attainedBlockIds->has($block->id)) {
                    $missing[] = self::MISSING_ATTAINMENT;
                }

                $result[$block->id] = $missing;
            }
        }

        return $result;
    }

    /**
     * Evaluate a single block (used by the command for targeted checks).
     *
     * @return array<int, string>
     */
    public static function missing(CourseBlock $block): array
    {
        return self::evaluateMany(collect([$block]))[$block->id] ?? [];
    }

    public static function isComplete(CourseBlock $block): bool
    {
        return empty(self::missing($block));
    }
}
