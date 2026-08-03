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

    /**
     * Submission window: OBE submissions only apply to course blocks offered
     * in SY 2025-2026 (2nd semester). Blocks before this term are not part of
     * the submission cycle.
     */
    public const SUBMISSION_ACADEMIC_YEAR_START = 2025;

    /**
     * Sections whose name starts with the letter "G" (e.g. G11/G12 grade level
     * sections) are excluded from the OBE submission cycle.
     */
    public const EXCLUDED_SECTION_PREFIX = 'g';

    private static function sectionStartsWithG(?string $name): bool
    {
        return $name !== null && str_starts_with(strtolower(trim($name)), self::EXCLUDED_SECTION_PREFIX);
    }

    public static function normalizeSemester($value): ?string
    {
        $value = strtolower(trim((string) $value));

        if (in_array($value, ['1st', 'first', '1st semester', 'first semester', 'semester 1', 'sem 1', '1st sem', '1'], true)) {
            return '1st';
        }

        if (in_array($value, ['2nd', 'second', '2nd semester', 'second semester', 'semester 2', 'sem 2', '2nd sem', '2'], true)) {
            return '2nd';
        }

        if (in_array($value, ['summer', 'summer term', '3rd', 'third', '3rd semester', 'third semester', 'semester 3', 'sem 3', '3'], true)) {
            return 'Summer';
        }

        return null;
    }

    /**
     * Whether a course block is part of the current OBE submission cycle.
     */
    public static function inSubmissionScope(CourseBlock $block): bool
    {
        $startYear = (int) optional($block->academicYear)->start_year;

        if ($startYear !== self::SUBMISSION_ACADEMIC_YEAR_START
            || self::normalizeSemester($block->semester) !== '2nd') {
            return false;
        }

        return $block->sections->isEmpty()
            || $block->sections->every(fn ($section) => !self::sectionStartsWithG($section->name));
    }

    /**
     * Apply the submission-scope filter to an Eloquent builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeQuery($query)
    {
        return $query
            ->whereHas('academicYear', fn ($q) => $q->where('start_year', self::SUBMISSION_ACADEMIC_YEAR_START))
            ->whereIn(\DB::raw('LOWER(TRIM(semester))'), [
                '2nd', 'second', '2nd semester', 'second semester', 'semester 2', 'sem 2', '2nd sem', '2',
            ])
            ->whereDoesntHave('sections', function ($q) {
                $q->whereRaw('LOWER(TRIM(sections.name)) LIKE ?', [self::EXCLUDED_SECTION_PREFIX . '%']);
            });
    }

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
