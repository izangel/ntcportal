<?php

namespace App\Http\Controllers;

use App\Models\{Student, Section, AcademicYear, SectionStudent};
use Illuminate\Http\Request;

class SectionAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::all();
        $sections = Section::all();
        $semesters = ['1st Semester', '2nd Semester', 'Summer'];

        // Filter assignments based on dropdowns
        $assignedStudents = SectionStudent::where('academic_year_id', $request->academic_year_id)
            ->where('semester', $request->semester)
            ->where('section_id', $request->section_id)
            ->with('student') 
            ->get();

        $allStudents = Student::orderBy('last_name')->get();

        return view('sections.assign', compact('academicYears', 'sections', 'semesters', 'assignedStudents', 'allStudents'));
    }

public function store(Request $request) 
{
    // Validate the incoming data
    $request->validate([
        'student_id' => 'required',
        'section_id' => 'required',
        'academic_year_id' => 'required',
        'semester' => 'required',
    ]);

    // Use except('_token') so Laravel doesn't try to find a '_token' column in your DB
    SectionStudent::firstOrCreate($request->except('_token'));

    return back()->with('success', 'Student assigned successfully!');
}

    public function destroy(SectionStudent $sectionStudent) {
        $sectionStudent->delete();
        return back()->with('success', 'Student removed!');
    }
}