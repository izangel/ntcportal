<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Section;
use App\Models\Semester; // <-- ADD THIS LINE
use App\Models\AcademicYear; // <-- ADD THIS LINE for filtering
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the enrollments.
     */
    public function index(Request $request)
    {
        // Get all academic years and semesters for filter dropdowns
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $semestersList = Semester::orderBy('name')->get(); // Use a different name to avoid conflict with `semesters` variable below

        $enrollmentsQuery = Enrollment::with(['student', 'section', 'semester.academicYear']);

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $academicYearId = $request->input('academic_year_id');
            $enrollmentsQuery->whereHas('semester.academicYear', function ($query) use ($academicYearId) {
                $query->where('id', $academicYearId);
            });
        }

        if ($request->filled('semester_id')) {
            $semesterId = $request->input('semester_id');
            $enrollmentsQuery->where('semester_id', $semesterId);
        }

        $enrollments = $enrollmentsQuery->paginate(10);
        $enrollments->appends($request->only(['academic_year_id', 'semester_id'])); // Append filters to pagination links

        return view('enrollments.index', compact('enrollments', 'academicYears', 'semestersList'));
    }

    /**
     * Show the form for creating a new enrollment.
     */
    public function create()
    {
        $students = Student::orderBy('last_name')->get();
        $sections = Section::with('program')->orderBy('name')->get();
        $activeSemester = Semester::getActiveSemester(); // Get the currently active semester

        // If no active semester, prevent enrollment creation and inform the user
        if (!$activeSemester) {
            return redirect()->route('enrollments.index')->with('error', 'No active academic semester found. Please set one as active before enrolling students.');
        }

        return view('enrollments.create', compact('students', 'sections', 'activeSemester'));
    }

    /**
     * Store a newly created enrollment in storage.
     */
    public function store(Request $request)
    {
        $activeSemester = Semester::getActiveSemester();

        if (!$activeSemester) {
            return redirect()->route('enrollments.index')->with('error', 'No active academic semester found. Enrollment failed.');
        }

        $validatedData = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
                // This unique rule ensures a student is only enrolled in a section once per semester
                Rule::unique('enrollments')->where(function ($query) use ($request, $activeSemester) {
                    return $query->where('student_id', $request->student_id)
                                 ->where('section_id', $request->section_id)
                                 ->where('semester_id', $activeSemester->id);
                })
            ],
            'section_id' => 'required|exists:sections,id',
        ]);

        // Assign the active semester ID to the validated data
        $validatedData['semester_id'] = $activeSemester->id;

        // Determine if the student is new for this semester
        $student = Student::find($validatedData['student_id']);
        $validatedData['is_new_student'] = $student->isNewStudentForSemester($activeSemester); // <-- ADD THIS LINE

        Enrollment::create($validatedData);

        return redirect()->route('enrollments.index')->with('success', 'Enrollment created successfully.');
    }
    /**
     * Show the form for editing the specified enrollment.
     */
    public function edit(Enrollment $enrollment)
    {
        $students = Student::orderBy('last_name')->get();
        $sections = Section::with('program')->orderBy('name')->get();
        // For editing, allow selecting any semester, not just active ones
        $semesters = Semester::with('academicYear')->orderBy('academic_year_id', 'desc')->orderBy('name')->get();

        return view('enrollments.edit', compact('enrollment', 'students', 'sections', 'semesters'));
    }

    /**
     * Update the specified enrollment in storage.
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $validatedData = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
                // Ensure unique combination of student, section, semester, excluding current enrollment
                Rule::unique('enrollments')->where(function ($query) use ($request) {
                    return $query->where('student_id', $request->student_id)
                                 ->where('section_id', $request->section_id)
                                 ->where('semester_id', $request->semester_id); // Use selected semester ID
                })->ignore($enrollment->id)
            ],
            'section_id' => 'required|exists:sections,id',
            'semester_id' => 'required|exists:semesters,id', // Now required as it's manually selected for edit
        ]);

        $enrollment->update($validatedData);

        return redirect()->route('enrollments.index')->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Remove the specified enrollment from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()->route('enrollments.index')->with('success', 'Enrollment deleted successfully.');
    }
}