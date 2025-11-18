<?php

namespace App\Http\Controllers;

use App\Models\FacultyLoading;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Employee; // Assuming 'User' model represents faculty
use App\Models\Section; 
use Illuminate\Http\Request;

class FacultyLoadingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch all loadings with their related data for display
        $loadings = FacultyLoading::with(['academicYear', 'course', 'faculty', 'section'])
            ->latest()
            ->paginate(15); // Use pagination for large datasets

        return view('faculty_loadings.index', compact('loadings'));
    }

    // --- Create ---

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Data needed for dropdowns in the form
        $academicYears = AcademicYear::all();
        $sections = Section::all();
        $courses = Course::orderBy('code')->get();
        
        // Fetch users who are faculty (adjust the condition based on your User model's setup)
        $facultyMembers = Employee::where('role', 'teacher')->orderBy('last_name')->get(); 

        return view('faculty_loadings.create', compact('academicYears', 'sections', 'courses', 'facultyMembers'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|string|max:50',
            'course_id' => 'required|exists:courses,id',
            'faculty_id' => 'required|exists:employees,id',
            'section_id' => 'required|exists:sections,id',
            'room' => 'required|string|max:50',
            'schedule' => 'required|string|max:100',
        ]);

        // 2. Create the record
        FacultyLoading::create($validated);

        // 3. Redirect with success message
        return redirect()->route('faculty-loadings.index')->with('success', 'Faculty loading successfully added!');
    }

    // --- Read (Show) ---
    
    /**
     * Display the specified resource (optional, but good practice).
     * @param  \App\Models\FacultyLoading  $facultyLoading
     * @return \Illuminate\View\View
     */
    public function show(FacultyLoading $facultyLoading)
    {
        // Eager load relationships for a detailed view
        $facultyLoading->load(['academicYear', 'section', 'course', 'faculty']);
        
        return view('faculty-loadings.show', compact('facultyLoading'));
    }

    // --- Update ---

    /**
     * Show the form for editing the specified resource.
     * @param  \App\Models\FacultyLoading  $facultyLoading
     * @return \Illuminate\View\View
     */
    // public function edit(FacultyLoading $facultyLoading)
    // {
    //     // Same data needed as the create form, plus the existing record
    //     $academicYears = AcademicYear::orderBy('name', 'desc')->get();
    //     $semesters = Semester::all();
    //     $courses = Course::orderBy('code')->get();
    //     $facultyMembers = User::where('role', 'faculty')->orderBy('name')->get(); 

    //     return view('faculty-loadings.edit', compact('facultyLoading', 'academicYears', 'semesters', 'courses', 'facultyMembers'));
    // }

    // /**
    //  * Update the specified resource in storage.
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Models\FacultyLoading  $facultyLoading
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function update(Request $request, FacultyLoading $facultyLoading)
    // {
    //     // 1. Validation
    //     $validated = $request->validate([
    //         'academic_year_id' => 'required|exists:academic_years,id',
    //         'semester_id' => 'required|exists:semesters,id',
    //         'course_id' => 'required|exists:courses,id',
    //         'faculty_id' => 'required|exists:users,id',
    //         'section' => 'required|string|max:50',
    //         'room' => 'required|string|max:50',
    //         'schedule' => 'required|string|max:100',
    //     ]);

    //     // 2. Update the record
    //     $facultyLoading->update($validated);

    //     // 3. Redirect with success message
    //     return redirect()->route('faculty-loadings.index')->with('success', 'Faculty loading successfully updated!');
    // }

    // // --- Delete ---

    // /**
    //  * Remove the specified resource from storage.
    //  * @param  \App\Models\FacultyLoading  $facultyLoading
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function destroy(FacultyLoading $facultyLoading)
    // {
    //     $facultyLoading->delete();

    //     return redirect()->route('faculty-loadings.index')->with('success', 'Faculty loading successfully deleted!');
    // }
}