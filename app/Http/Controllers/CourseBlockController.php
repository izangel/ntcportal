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
    // Add this method inside the class
public function index(Request $request)
{
    // 1. Get Academic Years for the dropdown
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

    // 2. Start Query
    $query = CourseBlock::with(['section.program', 'course', 'faculty', 'academicYear']);

    // 3. APPLY STRICT FILTERS 
    // This ensures only the chosen Year and Semester are returned.
    if ($request->filled('academic_year_id')) {
        $query->where('academic_year_id', $request->academic_year_id);
    }

    if ($request->filled('semester')) {
        // We use a strict where match here
        $query->where('semester', $request->semester);
    }

    // 4. Execute Query
    $courseBlocks = $query->latest()
                          ->paginate(10)
                          ->withQueryString(); 

    return view('course_blocks.index', compact('courseBlocks', 'academicYears'));
}

public function edit(CourseBlock $courseBlock)
{
    // Fetch dropdown data
    $sections = \App\Models\Section::all();
    $courses = \App\Models\Course::all();
    $employees = \App\Models\Employee::all(); 
    $academicYears = \App\Models\AcademicYear::all();

    return view('course_blocks.edit', compact('courseBlock', 'sections', 'courses', 'employees', 'academicYears'));
}

public function update(Request $request, CourseBlock $courseBlock)
{
    $validated = $request->validate([
        'section_id' => 'required|exists:sections,id',
        'course_id' => 'required|exists:courses,id',
        'faculty_id' => 'required|exists:employees,id',
        'academic_year_id' => 'required|exists:academic_years,id',
        'semester' => 'required|string',
        'room_name' => 'required|string',
        'schedule_string' => 'required|string',
    ]);

    $courseBlock->update($validated);

    return redirect()->route('course_blocks.index')->with('success', 'Course Block updated successfully.');
}

public function destroy(CourseBlock $courseBlock)
{
    $courseBlock->delete();
    return redirect()->route('course_blocks.index')->with('success', 'Block deleted successfully!');
}


}
