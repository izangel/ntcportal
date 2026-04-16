<?php

namespace App\Http\Controllers;

use App\Models\CourseBlock;
use App\Models\Section;
use App\Models\Course;
use App\Models\User; 
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;

class CourseBlockController extends Controller
{
    public function create()
    {
        $sections = Section::all();
        $courses = Course::all();
        $faculties = User::all();
        $academicYears = AcademicYear::all();

        return view('course_blocks.create', compact('sections', 'courses', 'faculties', 'academicYears'));
    }

    public function index(Request $request)
    {
        $activeSemesters = Semester::where('is_active', 1)->get();
        $academicYears = AcademicYear::all();
        $filter = $request->get('term_filter');

        $query = CourseBlock::with(['section', 'course', 'faculty', 'academicYear']);

        if ($filter && str_contains($filter, '|')) {
            [$selectedYearId, $selectedSemName] = explode('|', $filter);

            $mapping = [
                'First Semester'  => '1st Semester',
                'Second Semester' => '2nd Semester',
                'Summer'          => 'Summer'
            ];

            $targetSemester = $mapping[$selectedSemName] ?? $selectedSemName;

            $query->where('academic_year_id', $selectedYearId)
                  ->where('semester', $targetSemester);
        }

        // --- NATIVE EXCEL/CSV EXPORT START ---
        if ($request->has('export_excel') && $filter) {
            $fileName = 'Academic Year & Semester' . now()->format('Y-m-d') . '.csv';
            
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $columns = ['Course Code', 'Section', 'Schedule', 'Room', 'Faculty', 'Semester', 'Academic Year'];

            $callback = function() use ($query, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($query->get() as $block) {
                    fputcsv($file, [
                        $block->course->code ?? 'N/A',
                        $block->section->name ?? 'N/A',
                        $block->schedule_string,
                        $block->room_name,
                        ($block->faculty->last_name ?? 'N/A') . ', ' . ($block->faculty->first_name ?? ''),
                        $block->semester,
                        ($block->academicYear->start_year ?? '') . '-' . ($block->academicYear->end_year ?? '')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }
        // --- NATIVE EXCEL/CSV EXPORT END ---

        $courseBlocks = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('course_blocks.index', compact(
            'courseBlocks', 
            'academicYears', 
            'activeSemesters'
        ));
    }

    public function destroy(CourseBlock $courseBlock)
    {
        $courseBlock->delete();
        return redirect()->route('course_blocks.index')->with('success', 'Block deleted successfully!');
    }
}