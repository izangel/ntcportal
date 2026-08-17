<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\CourseBlock;
use App\Models\Section;
use App\Models\Semester;

class AcademicYearSetup
{
    /**
     * Resolve the academic year the school is currently operating on.
     * Mirrors the rest of the app: the active semester's year wins,
     * falling back to the active academic year flag.
     */
    public static function activeYear(): ?AcademicYear
    {
        $activeSemester = Semester::where('is_active', true)->first();

        if ($activeSemester && $activeSemester->academicYear) {
            return $activeSemester->academicYear;
        }

        return AcademicYear::where('is_active', true)->first();
    }

    /**
     * Build the year-start setup checklist for a given academic year.
     * Returns the items, aggregate counts, and a completeness flag.
     */
    public static function checklist(?AcademicYear $ay): array
    {
        if (!$ay) {
            return [
                'items' => [],
                'done_count' => 0,
                'total' => 0,
                'complete' => true,
            ];
        }

        $items = [
            'sections' => [
                'label' => 'Create Sections',
                'description' => 'Sections (e.g. BSIS-1A) exist for this academic year.',
                'done' => Section::where('academic_year_id', $ay->id)->exists(),
                'route' => 'sections.create',
                'icon' => 'fa-table-columns',
            ],
            'semesters' => [
                'label' => 'Set up Semesters',
                'description' => 'Semester records (1st, 2nd, Summer) exist for this academic year.',
                'done' => $ay->semesters()->exists(),
                'route' => 'semesters.create',
                'icon' => 'fa-timeline',
            ],
            'active_semester' => [
                'label' => 'Set Active Semester',
                'description' => 'One semester is marked as the active term.',
                'done' => $ay->semesters()->where('is_active', true)->exists(),
                'route' => 'semesters.index',
                'icon' => 'fa-circle-check',
            ],
            'course_blocks' => [
                'label' => 'Create Course Blocks / Loadings',
                'description' => 'Course blocks (faculty loadings) exist for this academic year.',
                'done' => CourseBlock::where('academic_year_id', $ay->id)->exists(),
                'route' => 'course_blocks.create',
                'icon' => 'fa-cubes',
            ],
        ];

        $doneCount = collect($items)->filter(fn ($item) => $item['done'])->count();

        return [
            'items' => $items,
            'done_count' => $doneCount,
            'total' => count($items),
            'complete' => $doneCount === count($items),
        ];
    }
}