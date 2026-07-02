<?php

namespace App\Http\Controllers;

use App\Models\CourseBlock;
use App\Models\AcademicYear;
use App\Models\CourseAttainment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CourseAttainmentController extends Controller
{
    // Faculty View: List only their courses
   public function index(Request $request)
{
    // 1. Get Academic Years for the dropdown
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
    
    // 2. Start the query for the specific faculty member
    $query = CourseBlock::where('faculty_id', auth()->user()->employee->id)
        ->with(['attainment', 'course', 'sections.program', 'academicYear']);

    // 3. Apply Filters if they exist in the request
    if ($request->filled('academic_year_id')) {
        $query->where('academic_year_id', $request->academic_year_id);
    }
    
    if ($request->filled('semester')) {
        $query->where('semester', $request->semester);
    }

    $courses = $query->get();

    return view('attainment.faculty-index', compact('courses', 'academicYears'));
}

    // Academic Head View: See everything
    public function adminIndex(Request $request)
{
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
    
    $query = CourseBlock::select('course_blocks.*')
        ->join('employees', 'course_blocks.faculty_id', '=', 'employees.id')
        ->with([
            'attainment', 
            'faculty', 
            'course', 
            'sections.program', 
            'academicYear'
        ]);

    // --- NEW: Filter for College only (Exclude SHS Programs) ---
    $query->whereHas('sections.program', function($q) {
        $q->where('name', 'not like', 'SHS%');
    });

    // Apply standard filters
    if ($request->filled('academic_year_id')) {
        $query->where('course_blocks.academic_year_id', $request->academic_year_id);
    }
    if ($request->filled('semester')) {
        $query->where('course_blocks.semester', $request->semester);
    }

    $submissions = $query->orderBy('employees.last_name', 'asc')
                         ->orderBy('employees.first_name', 'asc')
                         ->get();

    return view('attainment.admin-index', compact('submissions', 'academicYears'));
}

    // Store or Update the Link
    public function store(Request $request)
    {
        $request->validate([
            'course_block_id' => 'required|exists:course_blocks,id',
            'google_sheet_url' => ['required', 'url', 'regex:/docs\.google\.com\/spreadsheets/'],
        ], [
            'google_sheet_url.regex' => 'The link must be a valid Google Sheets URL.'
        ]);

        $session = CourseBlock::findOrFail($request->course_block_id);

        // Security check: Ensure faculty owns this course
        if ($session->faculty_id !== Auth::id()) {
            abort(403);
        }

        CourseAttainment::updateOrCreate(
            ['course_session_id' => $request->course_block_id],
            [
                'google_sheet_url' => $request->google_sheet_url,
                'status' => 'submitted'
            ]
        );

        return back()->with('success', 'Attainment Report submitted successfully!');
    }

    
}