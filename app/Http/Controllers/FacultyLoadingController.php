<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FacultyLoading;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Section;

class FacultyLoadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loadings = FacultyLoading::with(['academicYear', 'course', 'section.program', 'faculty'])->get();

        return view('faculty_loadings.index', compact('loadings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $courses = Course::orderBy('code')->get();
        $facultyMembers = Employee::orderBy('last_name')->get();
        $sections = Section::with('program')->orderBy('name')->get();

        return view('faculty_loadings.create', compact('academicYears', 'courses', 'facultyMembers', 'sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|string|max:50',
            'course_id' => 'required|exists:courses,id',
            'faculty_id' => 'required|exists:employees,id',
            'section_id' => 'required|exists:sections,id',
            'room' => 'nullable|string|max:255',
            'schedule' => 'nullable|string|max:255',
        ]);

        FacultyLoading::create($data);

        return redirect()->route('faculty-loadings.index')->with('success', 'Faculty loading created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $loading = FacultyLoading::findOrFail($id);

        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $courses = Course::orderBy('code')->get();
        $facultyMembers = Employee::orderBy('last_name')->get();
        $sections = Section::with('program')->orderBy('name')->get(); 

        return view('faculty_loadings.edit', compact('loading', 'academicYears', 'courses', 'facultyMembers', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $loading = FacultyLoading::findOrFail($id);

        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|string|max:50',
            'course_id' => 'required|exists:courses,id',
            'faculty_id' => 'required|exists:employees,id',
            'section_id' => 'required|exists:sections,id',
            'room' => 'nullable|string|max:255',
            'schedule' => 'nullable|string|max:255',
        ]);

        $loading->update($data);

        return redirect()->route('faculty-loadings.index')->with('success', 'Faculty loading updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $loading = FacultyLoading::with(['academicYear', 'course', 'faculty', 'section.program'])->findOrFail($id);

        return view('faculty_loadings.delete', compact('loading'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $loading = FacultyLoading::findOrFail($id);
        $loading->delete();

        return redirect()->route('faculty-loadings.index')->with('success', 'Faculty loading deleted.');
    }
}
