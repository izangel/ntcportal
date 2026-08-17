<?php

// app/Http/Controllers/CourseBlockController.php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseBlock;
use App\Models\Employee;
// or App\Models\Faculty
use App\Models\Section;
use App\Models\Semester;
use Illuminate\Http\Request;

class CourseBlockController extends Controller
{
    public function create()
    {
        // Fetch data for dropdowns
        $sections = Section::with('program')->get();
        // Sort courses by 'code' in ascending order
        $courses = Course::orderBy('code', 'asc')->get();

        // Sort employees (faculties) by 'last_name' in ascending order
        $faculties = Employee::orderBy('last_name', 'asc')->get();
        $academicYears = AcademicYear::all();
        $activeAyId = AcademicYear::where('is_active', true)->value('id');

        return view('course_blocks.create', compact('sections', 'courses', 'faculties', 'academicYears', 'activeAyId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_ids' => 'required|array|min:1',
            'section_ids.*' => 'exists:sections,id',
            'course_id' => 'required',
            'faculty_id' => 'required',
            'academic_year_id' => 'required',
            'semester' => 'required',
            'room_name' => 'required',
            'schedule_string' => 'required',
        ]);

        $sectionIds = array_map('intval', $validated['section_ids']);
        unset($validated['section_ids']);

        $block = CourseBlock::create($validated);

        foreach ($sectionIds as $sectionId) {
            $block->sections()->attach($sectionId, [
                'academic_year_id' => $block->academic_year_id,
                'semester' => $block->semester,
            ]);
        }

        return redirect()->route('course_blocks.index')->with('Success', 'Course Block created Successfully!');
    }

    // Add this method inside the class
    public function index(Request $request)
    {
        // Determine effective term: use the ACTIVE academic year & semester when
        // no explicit AY/semester filter is supplied.
        $activeSemester = Semester::getActiveSemester();
        $defaultSemKey = null;
        if ($activeSemester) {
            $activeSemName = strtolower((string) $activeSemester->name);
            $defaultSemKey = match (true) {
                str_contains($activeSemName, 'first') => '1st',
                str_contains($activeSemName, 'second') => '2nd',
                str_contains($activeSemName, 'summer') => 'Summer',
                default => null,
            };
        }
        $defaultAyId = $activeSemester?->academic_year_id;

        $selAy = $request->filled('ay') ? $request->ay : $defaultAyId;
        $selSem = $request->filled('sem') ? $request->sem : $defaultSemKey;

        $viewBy = $request->filled('view_by') ? $request->view_by : 'faculty';
        $viewBy = in_array($viewBy, ['section', 'faculty']) ? $viewBy : 'faculty';

        $query = CourseBlock::with(['sections.program', 'course', 'faculty', 'academicYear'])
            ->join('courses', 'course_blocks.course_id', '=', 'courses.id')
            ->join('academic_years', 'course_blocks.academic_year_id', '=', 'academic_years.id')
            ->join('employees', 'course_blocks.faculty_id', '=', 'employees.id');

        // Filters (Level, AY, Sem)
        if ($request->filled('level')) {
            if ($request->level === 'SHS') {
                $query->whereHas('sections.program', fn ($q) => $q->where('programs.name', 'LIKE', 'SHS%'));
            } else {
                $query->whereDoesntHave('sections.program', fn ($q) => $q->where('programs.name', 'LIKE', 'SHS%'));
            }
        }
        if ($selAy) {
            $query->where('course_blocks.academic_year_id', $selAy);
        }
        if ($selSem) {
            $map = ['1st' => ['1st', '1st Semester'], '2nd' => ['2nd', '2nd Semester'], 'Summer' => ['Sum', 'Summer']];
            if (isset($map[$selSem])) {
                $query->whereIn('course_blocks.semester', $map[$selSem]);
            }
        }

        // Grouping order: by Program-Section or by Teacher
        if ($viewBy === 'section') {
            $query->orderBy('employees.last_name', 'asc');
        } else {
            $query->orderBy('employees.last_name', 'asc');
        }

        // Sort by MWF, TTH, SAT
        $query->orderByRaw("CASE
            WHEN schedule_string LIKE '%MWF%' THEN 1
            WHEN schedule_string LIKE '%TTH%' THEN 2
            WHEN schedule_string LIKE '%SAT%' THEN 3
            WHEN schedule_string LIKE '%Monthly PE%' OR schedule_string LIKE '%MPE%' THEN 5
            ELSE 4 END ASC");

        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        $groupedBlocks = collect();

        if ($viewBy === 'section') {
            // Group every block under EACH of its sections, e.g. all blocks of
            // BSIS-1A first, then BSIS-1B (if any), and so on.
            $blocks = $query->select('course_blocks.*')->get();

            $groups = [];
            foreach ($blocks as $block) {
                $sections = $block->sections->sortBy(function ($s) {
                    preg_match('/(\d+)(.*)$/u', (string) $s->name, $m);

                    return ($s->program->name ?? 'N/A').'|'.str_pad($m[1] ?? 'zz', 2, '0', STR_PAD_LEFT).'|'.strtoupper($m[2] ?? '');
                });

                if ($sections->isEmpty()) {
                    $label = 'No Section';
                    $groups[$label]['label'] = $label;
                    $groups[$label]['sort'] = 'ZZZZ';
                    $groups[$label]['blocks'][] = $block;

                    continue;
                }

                foreach ($sections as $s) {
                    preg_match('/(\d+)(.*)$/u', (string) $s->name, $m);
                    $program = $s->program->name ?? 'N/A';
                    $label = $program.'-'.$s->name;
                    $sort = $program.'|'.str_pad($m[1] ?? 'zz', 2, '0', STR_PAD_LEFT).'|'.strtoupper($m[2] ?? '');

                    $groups[$label]['label'] = $label;
                    $groups[$label]['sort'] = $sort;
                    $groups[$label]['blocks'][] = $block;
                }
            }

            uksort($groups, fn ($a, $b) => strcmp($groups[$a]['sort'], $groups[$b]['sort']));

            $groupedBlocks = collect(array_values($groups));
            $courseBlocks = null;
        } else {
            $courseBlocks = $query->select('course_blocks.*')
                ->paginate(100)
                ->withQueryString();
        }

        return view('course_blocks.index', compact('courseBlocks', 'groupedBlocks', 'academicYears', 'selAy', 'selSem', 'viewBy', 'defaultAyId', 'defaultSemKey'));
    }

    public function edit(CourseBlock $courseBlock)
    {
        // Fetch dropdown data
        $sections = Section::with('program')->get();
        $courses = Course::all();
        $employees = Employee::all();
        $academicYears = AcademicYear::all();

        return view('course_blocks.edit', compact('courseBlock', 'sections', 'courses', 'employees', 'academicYears'));
    }

    public function update(Request $request, CourseBlock $courseBlock)
    {
        $validated = $request->validate([
            'section_ids' => 'required|array|min:1',
            'section_ids.*' => 'exists:sections,id',
            'course_id' => 'required|exists:courses,id',
            'faculty_id' => 'required|exists:employees,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|string',
            'room_name' => 'required|string',
            'schedule_string' => 'required|string',
        ]);

        $sectionIds = array_map('intval', $validated['section_ids']);
        unset($validated['section_ids']);

        $courseBlock->update($validated);

        $courseBlock->sections()->syncWithPivotValues($sectionIds, [
            'academic_year_id' => $courseBlock->academic_year_id,
            'semester' => $courseBlock->semester,
        ]);

        return redirect()->route('course_blocks.index')->with('success', 'Course Block updated successfully.');
    }

    public function destroy(CourseBlock $courseBlock)
    {
        $courseBlock->delete();

        return redirect()->route('course_blocks.index')->with('success', 'Block deleted successfully!');
    }

    public function verify(Request $request)
    {
        EvaluationSetting::where('is_active', true)->update([
            'blocks_verified' => true,
        ]);

        return back()->with('success', 'Blocks verified! Subject loading is now unlocked for the Registrar.');
    }
}
