<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // For raw DB queries if needed

class ReportController extends Controller
{
    /**
     * Display the reports index page.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Display report: Number of students per course (section) per semester.
     */
    public function studentsPerCourse(Request $request)
    {
        // Fetch all academic years for the filter dropdown
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        // Get selected filter values from the request
        $selectedAcademicYearId = $request->input('academic_year_id');
        $selectedSemesterId = $request->input('semester_id');

        // Initialize semestersList for the filter dropdown
        $semestersList = collect();
        if ($selectedAcademicYearId) {
            $semestersList = Semester::where('academic_year_id', $selectedAcademicYearId)
                                     ->orderBy('name')
                                     ->get();
        } else {
             // If no academic year is selected, populate with all semesters for initial load/no filter
            $semestersList = Semester::with('academicYear')->orderBy('academic_year_id', 'desc')->orderBy('name')->get();
        }


        $reportData = collect(); // Initialize an empty collection for report results

        // Only generate report data if a semester is selected
        if ($selectedSemesterId) {
            $reportData = Enrollment::select(
                    'courses.name as course_name',
                    'courses.code as course_code', // Include course code
                    'semesters.name as semester_name',
                    'academic_years.start_year',
                    'academic_years.end_year',
                    DB::raw('COUNT(DISTINCT enrollments.student_id) as student_count') // Count unique students
                )
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->join('semesters', 'enrollments.semester_id', '=', 'semesters.id')
                ->join('academic_years', 'semesters.academic_year_id', '=', 'academic_years.id')
                ->where('enrollments.semester_id', $selectedSemesterId) // Filter by selected semester
                ->groupBy(
                    'courses.id', // Group by course ID to ensure unique courses
                    'semesters.id',
                    'academic_years.id',
                    'courses.name',
                    'courses.code',
                    'semesters.name',
                    'academic_years.start_year',
                    'academic_years.end_year'
                )
                ->orderBy('academic_years.start_year', 'desc')
                ->orderBy('semesters.start_date', 'desc')
                ->orderBy('courses.name')
                ->get();
        }

        return view('reports.students_per_course', compact('reportData', 'academicYears', 'semestersList', 'selectedAcademicYearId', 'selectedSemesterId'));
    }

    /**
     * Display report: Number of new vs. old students per semester.
     */
    public function studentTypes(Request $request)
    {
        // Fetch all academic years for the filter dropdown
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        // Get selected filter values from the request
        $selectedAcademicYearId = $request->input('academic_year_id');
        $selectedSemesterId = $request->input('semester_id');

        // Initialize semestersList for the filter dropdown
        $semestersList = collect();
        if ($selectedAcademicYearId) {
            $semestersList = Semester::where('academic_year_id', $selectedAcademicYearId)
                                     ->orderBy('name')
                                     ->get();
        } else {
             // If no academic year is selected, populate with all semesters for initial load/no filter
            $semestersList = Semester::with('academicYear')->orderBy('academic_year_id', 'desc')->orderBy('name')->get();
        }

        $reportData = collect(); // Initialize empty collection for report results

        // Only generate report data if a semester is selected
        if ($selectedSemesterId) {
            $reportData = Enrollment::select(
                    'semesters.name as semester_name',
                    'academic_years.start_year',
                    'academic_years.end_year',
                    DB::raw('SUM(CASE WHEN enrollments.is_new_student = TRUE THEN 1 ELSE 0 END) as new_student_count'),
                    DB::raw('SUM(CASE WHEN enrollments.is_new_student = FALSE THEN 1 ELSE 0 END) as old_student_count'),
                    DB::raw('COUNT(DISTINCT enrollments.student_id) as total_unique_students_enrolled') // Total unique students per semester/AY
                )
                ->join('semesters', 'enrollments.semester_id', '=', 'semesters.id')
                ->join('academic_years', 'semesters.academic_year_id', '=', 'academic_years.id')
                ->where('enrollments.semester_id', $selectedSemesterId) // Filter by selected semester
                ->groupBy(
                    'semesters.id',
                    'academic_years.id',
                    'semesters.name',
                    'academic_years.start_year',
                    'academic_years.end_year'
                )
                ->orderBy('academic_years.start_year', 'desc')
                ->orderBy('semesters.start_date', 'desc')
                ->get();
        }


        return view('reports.student_types', compact('reportData', 'academicYears', 'semestersList', 'selectedAcademicYearId', 'selectedSemesterId'));
    }
}