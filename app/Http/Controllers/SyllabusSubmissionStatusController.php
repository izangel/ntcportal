<?php

namespace App\Http\Controllers;

use App\Models\CourseBlock;
use App\Models\CourseSyllabus;
use App\Models\Program;
use App\Models\Semester;
use Illuminate\Http\Request;

class SyllabusSubmissionStatusController extends Controller
{
    public function show(Request $request)
    {
        // Determine the effective term the SAME way the Course Blocks page does:
        // the ACTIVE academic year via Semester::getActiveSemester(), then map the
        // active semester name to the exact semester buckets stored in course_blocks.
        $activeSemester = Semester::getActiveSemester();
        $semKey = null;

        if ($activeSemester) {
            $activeSemName = strtolower((string) $activeSemester->name);
            $semKey = match (true) {
                str_contains($activeSemName, 'first') => '1st',
                str_contains($activeSemName, 'second') => '2nd',
                str_contains($activeSemName, 'summer') => 'Summer',
                default => null,
            };
        }

        $selAy = $activeSemester?->academic_year_id;
        $semMap = [
            '1st' => ['1st', '1st Semester'],
            '2nd' => ['2nd', '2nd Semester'],
            'Summer' => ['Sum', 'Summer'],
        ];

        $blocks = collect();

        if ($selAy && $semKey && isset($semMap[$semKey])) {
            $blocks = CourseBlock::with(['course', 'faculty', 'sections.program', 'academicYear'])
                ->where('academic_year_id', $selAy)
                ->whereIn('semester', $semMap[$semKey])
                ->orderBy('schedule_string')
                ->get();
        }

        $syllabusIndex = CourseSyllabus::with('program')
            ->whereIn('course_block_id', $blocks->pluck('id'))
            ->get()
            ->keyBy(fn ($s) => $s->course_block_id.'|'.$s->program_id);

        // List one row per course block, exactly like the Course Blocks page.
        $rows = $blocks->map(function ($block) use ($syllabusIndex) {
            $programs = $block->sections->pluck('program')
                ->filter()
                ->unique('id');

            $programEntries = $programs->map(function (Program $program) use ($block, $syllabusIndex) {
                $syllabus = $syllabusIndex->get($block->id.'|'.$program->id);

                return [
                    'id' => $program->id,
                    'name' => $program->name,
                    'status' => $this->statusOf($syllabus),
                ];
            })->values();

            $sections = $block->sections
                ->map(fn ($section) => (($section->program->name ?? 'N/A').'-'.$section->name))
                ->unique()
                ->sort()
                ->implode(', ');

            return [
                'block_id' => $block->id,
                'course_code' => $block->course->code ?? '—',
                'course_name' => $block->course->name ?? '—',
                'faculty' => optional($block->faculty)->full_name ?? 'Unassigned',
                'schedule' => trim(($block->room_name ?? '').($block->schedule_string ? ' | '.$block->schedule_string : '')),
                'sections' => $sections,
                'programs' => $programEntries,
            ];
        })->values();

        $stats = [
            'none' => 0,
            'draft' => 0,
            'submitted' => 0,
            'reviewed' => 0,
            'approved' => 0,
            'revision' => 0,
        ];

        foreach ($rows as $row) {
            foreach ($row['programs'] as $entry) {
                $stats[$entry['status']['key']] = ($stats[$entry['status']['key']] ?? 0) + 1;
            }
        }

        $activeSemesterLabel = $activeSemester
            ? optional($activeSemester->academicYear)->label.' · '.$activeSemester->name
            : '—';

        return view('faculty.syllabus-status', compact('activeSemesterLabel', 'rows', 'stats'));
    }

    private function statusOf(?CourseSyllabus $syllabus): array
    {
        if (! $syllabus) {
            return ['key' => 'none', 'label' => 'Not Prepared'];
        }

        if ($syllabus->academic_head_approved_at) {
            return ['key' => 'approved', 'label' => 'Approved'];
        }

        if ($syllabus->revision_requested_at) {
            return ['key' => 'revision', 'label' => 'In Revision'];
        }

        if ($syllabus->program_head_reviewed_at) {
            return ['key' => 'reviewed', 'label' => 'Reviewed · For AH Approval'];
        }

        if ($syllabus->submitted_at) {
            return ['key' => 'submitted', 'label' => 'Submitted · For PH Review'];
        }

        return ['key' => 'draft', 'label' => 'Draft / In Progress'];
    }
}