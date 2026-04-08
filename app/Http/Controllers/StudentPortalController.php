<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
   // Add this at the top of your controller
use App\Models\Section;
use Illuminate\Support\Facades\DB;


class StudentPortalController extends Controller
{
    public function index(Request $request)
{
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
    $sections = Section::orderBy('name', 'asc')->get();

    $selectedYear = $request->input('academic_year_id');
    $selectedSemester = $request->input('semester');
    $selectedSection = $request->input('section_id');
    $searchTerm = $request->input('search'); // Capture the search string

    $students = Student::query()
        ->when($selectedYear && $selectedSemester, function ($query) use ($selectedYear, $selectedSemester, $selectedSection, $searchTerm) {
            $query->whereHas('sections', function ($q) use ($selectedYear, $selectedSemester, $selectedSection) {
                $q->where('section_student.academic_year_id', $selectedYear)
                  ->where('section_student.semester', $selectedSemester);
                
                if ($selectedSection) {
                    $q->where('section_student.section_id', $selectedSection);
                }
            });

            // Added: Search by First Name or Last Name
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('first_name', 'like', "%{$searchTerm}%")
                      ->orWhere('last_name', 'like', "%{$searchTerm}%")
                      ->orWhere('student_id', 'like', "%{$searchTerm}%");
                });
            }
        })
        ->with(['sections' => function ($q) use ($selectedYear, $selectedSemester) {
            $q->wherePivot('academic_year_id', $selectedYear)
              ->wherePivot('semester', $selectedSemester);
        }])
        ->orderBy('last_name', 'asc')
        ->get();

    return view('students.studentportal', compact(
        'students', 'academicYears', 'sections', 
        'selectedYear', 'selectedSemester', 'selectedSection', 'searchTerm'
    ));
}

// Add this method to handle the section change
public function updateSection(Request $request, Student $student)
{
    $request->validate([
        'section_id' => 'required|exists:sections,id',
        'academic_year_id' => 'required',
        'semester' => 'required'
    ]);

    // Update the pivot table record for this specific year/semester
    DB::table('section_student')
        ->where('student_id', $student->id)
        ->where('academic_year_id', $request->academic_year_id)
        ->where('semester', $request->semester)
        ->update(['section_id' => $request->section_id]);

    return back()->with('success', 'Section updated successfully!');
}
}