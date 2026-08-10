<?php

namespace App\Services;

use App\Models\CourseBlock;
use App\Models\CourseEvaluation;
use App\Models\StudentAssessmentMark;

class CourseAttainmentReportService
{
    public const THRESHOLD = 60;

    public const DIRECT_WEIGHT = 0.8;

    public const INDIRECT_WEIGHT = 0.2;

    /**
     * Build the full Course Attainment Report dataset for a course block.
     */
    public function build(CourseBlock $block): array
    {
        $course = $block->course;
        $batchYear = $block->batchYear();

        $clos = $course?->learningOutcomes()
            ->with('bloomsTaxonomy')
            ->get()
            ->filter(fn ($clo) => $this->matchesBatchYear($clo->effective_batch_year, $batchYear))
            ->sortBy('code')
            ->values() ?? collect();

        $tasks = $course?->assessmentTasks()
            ->with('items.clo')
            ->get()
            ->filter(fn ($task) => $this->matchesBatchYear($task->effective_batch_year, $batchYear))
            ->values() ?? collect();

        $itemsByClo = [];
        foreach ($tasks as $task) {
            foreach ($task->items as $item) {
                $itemsByClo[$item->course_learning_outcome_id][] = (object) [
                    'item_id' => $item->id,
                    'task_title' => $task->title,
                    'task_type' => $task->type,
                    'item_name' => $item->item_name,
                    'max_marks' => (float) $item->max_marks,
                ];
            }
        }

        $students = $block->students()->get()
            ->map(fn ($student) => (object) [
                'id' => $student->id,
                'student_number' => $student->student_id,
                'name' => trim($student->last_name.', '.$student->first_name.($student->middle_name ? ' '.$student->middle_name : '')),
            ])
            ->sortBy('name')
            ->values();

        $studentIds = $students->pluck('id');
        $allItemIds = collect($itemsByClo)->flatten()->pluck('item_id')->unique();

        $marksByStudentItem = collect();
        if ($studentIds->isNotEmpty() && $allItemIds->isNotEmpty()) {
            $marksByStudentItem = StudentAssessmentMark::whereIn('student_id', $studentIds)
                ->whereIn('assessment_item_id', $allItemIds)
                ->get()
                ->keyBy(fn ($mark) => $mark->student_id.'-'.$mark->assessment_item_id);
        }

        $indirectRaw = $this->indirectRating($block);
        $indirectPercentage = $indirectRaw !== null ? round($indirectRaw * 20, 1) : null;

        $cloRows = $clos->map(function ($clo) use ($itemsByClo, $students, $marksByStudentItem, $indirectPercentage) {
            $items = collect($itemsByClo[$clo->id] ?? []);
            $maxMarks = $items->sum('max_marks');

            $studentScores = $students->map(function ($student) use ($items, $marksByStudentItem, $maxMarks) {
                $hasData = $items->contains(fn ($item) => $marksByStudentItem->has($student->id.'-'.$item->item_id));
                $obtained = $items->sum(fn ($item) => (float) ($marksByStudentItem->get($student->id.'-'.$item->item_id)?->marks_obtained ?? 0));

                return [
                    'name' => $student->name,
                    'student_number' => $student->student_number,
                    'obtained' => $obtained,
                    'max' => $maxMarks,
                    'percentage' => ($hasData && $maxMarks > 0) ? round(($obtained / $maxMarks) * 100, 1) : null,
                ];
            });

            $assessed = $studentScores->filter(fn ($row) => $row['percentage'] !== null);

            $direct = $assessed->isNotEmpty()
                ? round($assessed->avg('percentage'), 1)
                : null;

            $weighted = ($direct !== null && $indirectPercentage !== null)
                ? round(($direct * self::DIRECT_WEIGHT) + ($indirectPercentage * self::INDIRECT_WEIGHT), 1)
                : $direct;

            return [
                'id' => $clo->id,
                'code' => $clo->code,
                'description' => $clo->description,
                'blooms' => $clo->bloomsTaxonomy?->name,
                'items' => $items->values(),
                'max_marks' => $maxMarks,
                'assessed' => $assessed->count(),
                'total_students' => $students->count(),
                'direct' => $direct,
                'indirect' => $indirectPercentage,
                'weighted' => $weighted,
                'attained' => $weighted !== null && $weighted >= self::THRESHOLD,
                'student_scores' => $studentScores,
            ];
        });

        $sections = $block->sections()->get();

        return [
            'block' => $block,
            'course' => $course,
            'batch_year' => $batchYear,
            'sections_label' => $sections->pluck('name')->filter()->unique()->implode(', ') ?: '—',
            'program' => $sections->first()?->program?->name,
            'faculty' => $block->faculty ? trim($block->faculty->first_name.' '.$block->faculty->last_name) : 'TBA',
            'students_count' => $students->count(),
            'clos' => $cloRows,
            'indirect_rating' => $indirectRaw,
            'indirect_percentage' => $indirectPercentage,
            'combined_weighted' => $cloRows->filter(fn ($c) => $c['weighted'] !== null)->isNotEmpty(),
            'threshold' => self::THRESHOLD,
        ];
    }

    /**
     * Average exit-survey rating (1-5) for the course/offering, or null when
     * no student evaluations exist for the term.
     */
    private function indirectRating(CourseBlock $block): ?float
    {
        $ratings = CourseEvaluation::where('course_id', $block->course_id)
            ->where('academic_year_id', $block->academic_year_id)
            ->whereIn('semester', $this->semesterVariants($block->semester))
            ->whereNotNull('rating')
            ->pluck('rating');

        if ($ratings->isEmpty()) {
            return null;
        }

        return (float) $ratings->avg();
    }

    private function semesterVariants(string $raw): array
    {
        $n = strtolower(trim($raw));

        if (str_contains($n, 'summer')) {
            return ['Summer', 'summer', '3rd'];
        }

        if (str_contains($n, 'second') || str_contains($n, '2nd') || $n === '2') {
            return ['2nd', '2nd Semester', 'Second Semester'];
        }

        return ['1st', '1st Semester', 'First Semester'];
    }

    private function matchesBatchYear($effectiveBatchYear, $batchYear): bool
    {
        if ($effectiveBatchYear === null || $effectiveBatchYear === '') {
            return true;
        }

        return $batchYear !== null && (string) $effectiveBatchYear === (string) $batchYear;
    }
}
