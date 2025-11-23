<?php
 
namespace App\Http\Controllers;

use App\Models\CourseToSection;
use App\Models\Course;
use App\Models\Section;
use App\Models\Student;
use App\Models\Semester;

use App\Models\AcademicYear; // <-- ADD THIS LINE for filtering
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 

class CourseToSectionController extends Controller
{
    /**
     * Display a listing of the coursetosections.
     */
    public function index(Request $request)
    {
        // Get all academic years and semesters for filter dropdowns
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        
        $coursetosectionsQuery = CourseToSection::with(['course', 'academicYear', 'section']);

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $academicYearId = $request->input('academic_year_id');
            $coursetosectionsQuery->whereHas('academicYear', function ($query) use ($academicYearId) {
                $query->where('id', $academicYearId);
            });
        }

        if ($request->filled('semester')) {
            $semester = $request->input('semester');
            $coursetosectionsQuery->where('semester', $semester);
        }

        $coursetosections = $coursetosectionsQuery->paginate(10);
        $coursetosections->appends($request->only(['academic_year_id', 'semester'])); // Append filters to pagination links

        return view('coursetosections.index', compact('coursetosections', 'academicYears'));
    }

    /**
     * Show the form for creating a new coursetosection.
     */
    public function create()
    {
         $academicYears = AcademicYear::orderBy('start_year')->get();
        $courses = Course::orderBy('code')->get();
        $sections = Section::with('program')->orderBy('name')->get();
        

        return view('coursetosections.create', compact('academicYears', 'courses', 'sections'));
    }

    /**
     * Store a newly created coursetosection in storage.
     */
    public function store(Request $request)
    {
       

       
        $validatedData = $request->validate([

            'academic_year_id' => 'required|exists:academic_years,id',
             'semester' => 'required|string|max:50',
            'course_id' => 'required|exists:courses,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        

        CourseToSection::create($validatedData);

        return redirect()->route('coursetosections.index')->with('success', 'Course assigned to Section successfully.');
    }
    /**
     * Show the form for editing the specified coursetosection.
     */
    public function edit(coursetosection $coursetosection)
    {
        $students = Student::orderBy('last_name')->get();
        $sections = Section::with('program')->orderBy('name')->get();
        $courses = Course::orderBy('code')->get();
        // For editing, allow selecting any semester, not just active ones
        $semesters = Semester::with('academicYear')->orderBy('academic_year_id', 'desc')->orderBy('name')->get();

        return view('coursetosections.edit', compact('coursetosection', 'students', 'sections', 'semesters','courses'));
    }

    /**
     * Update the specified coursetosection in storage.
     */
    public function update(Request $request, coursetosection $coursetosection)
    {
        $validatedData = $request->validate([
                 'student_id' => [
                'academic_year_id' => 'required|exists:academic_years,id',
                'semester_id' => 'required|exists:semesters,id',
                'course_id' => 'required|exists:courses,id',
                'section_id' => 'required|exists:sections,id',
                // Ensure unique combination of student, section, semester, excluding current coursetosection
                Rule::unique('coursetosections')->where(function ($query) use ($request) {
                    return $query->where('student_id', $request->student_id)
                                 ->where('section_id', $request->section_id)
                                 ->where('semester_id', $request->semester_id); // Use selected semester ID
                })->ignore($coursetosection->id)
            ],
            'section_id' => 'required|exists:sections,id',
            'semester_id' => 'required|exists:semesters,id', // Now required as it's manually selected for edit
        ]);

        $coursetosection->update($validatedData);

        return redirect()->route('coursetosections.index')->with('success', 'coursetosection updated successfully.');
    }

    /**
     * Remove the specified coursetosection from storage.
     */
    public function destroy(coursetosection $coursetosection)
    {
        $coursetosection->delete();

        return redirect()->route('coursetosections.index')->with('success', 'coursetosection deleted successfully.');
    }
}