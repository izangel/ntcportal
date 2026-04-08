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
public function index()
{
    // Eager load relationships to prevent N+1 query issues
    $courseBlocks = CourseBlock::with(['section', 'course', 'faculty', 'academicYear'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10); // Show 10 items per page

    return view('course_blocks.index', compact('courseBlocks'));
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
