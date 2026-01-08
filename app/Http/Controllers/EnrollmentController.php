<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Section;
use App\Models\Course;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $selectedStudent = $request->query('student_id');

        $assignments = Enrollment::with(['student', 'course', 'section.program', 'academicYear'])
            ->when($selectedStudent, function ($query, $selectedStudent) {
                $query->where('student_id', $selectedStudent);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('enrollments.index', [
            'assignments'   => $assignments,
            'students'      => Student::orderBy('last_name')->get(),
            'courses'       => Course::orderBy('name')->get(),
            'sections'      => Section::with('program')->get(),
            'academicYears' => AcademicYear::orderBy('start_year', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'        => 'required|exists:students,id',
            'course_id'         => 'required|exists:courses,id',
            'section_id'        => 'required|exists:sections,id',
            'academic_year_id'  => 'required|exists:academic_years,id',
            'semester'          => 'required|string',
        ]);

        // Prevent duplicate enrollment for the same course/year/semester
        $exists = Enrollment::where([
            ['student_id', $request->student_id],
            ['course_id', $request->course_id],
            ['academic_year_id', $request->academic_year_id],
            ['semester', $request->semester],
        ])->exists();

        if ($exists) {
            return back()->withErrors(['student_id' => 'Student is already enrolled in this course for the selected term.'])->withInput();
        }

        Enrollment::create($validated);

        return back()->with('success', 'Student enrolled successfully!');
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return back()->with('success', 'Enrollment record has been removed.');
    }
}