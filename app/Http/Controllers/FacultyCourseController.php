<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\FacultyLoading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacultyCourseController extends Controller
{
    /**
     * Show the selection form for Academic Year and Semester.
     */
    public function index()
    {
        // Fetch all available academic years for the dropdown filter
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        // The view starts with no courses displayed
        return view('faculty.course-load', [
            'academicYears' => $academicYears,
            'loadings' => collect(), // Empty collection initially
            'selectedYear' => null,
            'selectedSemester' => null,
        ]);
    }

    /**
     * Show the filtered course load based on user selection.
     */
    public function showLoad(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|string|in:1st,2nd,Summer', // Adjust semesters as needed
        ]);

        $facultyId = Auth::user()->employee->id;
        $selectedYearId = $request->input('academic_year_id');
        $selectedSemester = $request->input('semester');

        // Fetch the course loadings based on filters
        $loadings = FacultyLoading::where('faculty_id', $facultyId)
            ->where('academic_year_id', $selectedYearId)
            ->where('semester', $selectedSemester)
            // Nested eager load Course, Section, and AcademicYear details
            ->with(['course', 'section', 'academicYear'])
            ->get();
        
        
            
        // Fetch all academic years again to populate the filter dropdown
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        return view('faculty.course-load', [
            'academicYears' => $academicYears,
            'loadings' => $loadings,
            'selectedYear' => $selectedYearId,
            'selectedSemester' => $selectedSemester,
        ]);
    }
}