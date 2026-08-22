<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
   // Add this at the top of your controller
use App\Models\Section;
use Illuminate\Support\Facades\DB;


class StudentPortalController extends Controller
{
    public function index(Request $request)
{
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

    // Default filters to the active academic year + active semester
    $activeSemester = Semester::with('academicYear')->where('is_active', true)->first();
    $activeAyId = $activeSemester?->academic_year_id ?? AcademicYear::where('is_active', true)->value('id');
    $activeSemesterValue = $activeSemester ? $this->semesterValue($activeSemester->name) : null;

    $selectedYear = $request->input('academic_year_id') ?: $activeAyId;
    $selectedSemester = $request->filled('semester') ? $request->input('semester') : $activeSemesterValue;
    $selectedSection = $request->input('section_id');
    $searchTerm = $request->input('search'); // Capture the search string

    // Filter sections to the selected year
    $sections = Section::orderBy('name', 'asc')
        ->when($selectedYear, fn ($q, $ay) => $q->where('academic_year_id', $ay))
        ->get();

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

    /**
     * Map an active semester name to the section_student / dropdown value
     * convention: "First Semester" -> "1st", "Second Semester" -> "2nd Semester".
     */
    private function semesterValue(string $name): string
    {
        return str_contains($name, 'First') || $name === '1st'
            ? '1st'
            : (str_contains($name, 'Second') || $name === '2nd' ? '2nd Semester' : 'Summer');
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