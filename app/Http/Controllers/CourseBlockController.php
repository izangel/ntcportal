<?php
// app/Http/Controllers/CourseBlockController.php

namespace App\Http\Controllers;

use App\Models\CourseBlock;
use App\Models\Section;
use App\Models\Course;
use App\Models\User; // or App\Models\Faculty
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class CourseBlockController extends Controller
{
    public function create()
    {
        // Fetch data for dropdowns
        $sections = Section::all();
        $courses = Course::all();
        $faculties = User::all();
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
