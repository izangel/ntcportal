<?php
// app/Http/Controllers/CourseBlockController.php

namespace App\Http\Controllers;

use App\Models\CourseBlock;
use App\Models\Section;
use App\Models\Course;
use App\Models\Employee;
use App\Models\User; // or App\Models\Faculty
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class CourseBlockController extends Controller
{
    public function create()
    {
        // Fetch data for dropdowns
        $sections = Section::all();
       // Sort courses by 'code' in ascending order
        $courses = Course::orderBy('code', 'asc')->get();
        
        // Sort employees (faculties) by 'last_name' in ascending order
        $faculties = Employee::orderBy('last_name', 'asc')->get();
        $academicYears = AcademicYear::all();

        return view('course_blocks.create', compact('sections', 'courses', 'faculties', 'academicYears'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'section_id' => 'required',
        'course_id' => 'required',
        'faculty_id' => 'required',
        'academic_year_id' => 'required',
        'semester' => 'required',
        'room_name' => 'required',
        'schedule_string' => 'required',
    ]);
        CourseBlock::create($validated);

        return redirect()->route('course_blocks.index')->with('Success', 'Course Block created Successfully!');
    }

    // Add this method inside the class
public function index(Request $request)
{
    $query = CourseBlock::with(['section.program', 'course', 'faculty', 'academicYear'])
        ->join('sections', 'course_blocks.section_id', '=', 'sections.id')
        ->join('programs', 'sections.program_id', '=', 'programs.id')
        ->join('courses', 'course_blocks.course_id', '=', 'courses.id')
        ->join('academic_years', 'course_blocks.academic_year_id', '=', 'academic_years.id')
        ->join('employees', 'course_blocks.faculty_id', '=', 'employees.id');

    // Filters (Level, AY, Sem)
    if ($request->filled('level')) {
        $request->level === 'SHS' 
            ? $query->where('programs.name', 'LIKE', 'SHS%') 
            : $query->where('programs.name', 'NOT LIKE', 'SHS%');
    }
    if ($request->filled('ay')) $query->where('course_blocks.academic_year_id', $request->ay);
    if ($request->filled('sem')) {
        $map = ['1st' => ['1st', '1st Semester'], '2nd' => ['2nd', '2nd Semester'], 'Summer' => ['Sum', 'Summer']];
        if (isset($map[$request->sem])) $query->whereIn('course_blocks.semester', $map[$request->sem]);
    }

    // 1. Sort by Faculty Last Name First
    $query->orderBy('employees.last_name', 'asc');

    // 2. Sort by MWF, TTH, SAT
    $query->orderByRaw("CASE 
            WHEN schedule_string LIKE '%MWF%' THEN 1 
            WHEN schedule_string LIKE '%TTH%' THEN 2 
            WHEN schedule_string LIKE '%SAT%' THEN 3 
            WHEN schedule_string LIKE '%Monthly PE%' OR schedule_string LIKE '%MPE%' THEN 5
            ELSE 4 END ASC");

    $courseBlocks = $query->select('course_blocks.*')
        ->paginate(100)
        ->withQueryString();

    $academicYears = \App\Models\AcademicYear::orderBy('start_year', 'desc')->get();
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


public function verify(Request $request)
{
    EvaluationSetting::where('is_active', true)->update([
        'blocks_verified' => true
    ]);

    return back()->with('success', 'Blocks verified! Subject loading is now unlocked for the Registrar.');
}


}
