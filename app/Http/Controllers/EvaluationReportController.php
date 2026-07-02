<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Evaluation; 
use App\Models\FacultyEvaluation; 
use Illuminate\Support\Facades\Auth;

class EvaluationReportController extends Controller
{
    /**
     * Show the 360 Degree Report Selection Index
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        return view('faculty.reports.index', compact('academicYears'));
    }

    /**
     * Compile and Show the Detailed 360 Report
     */
   public function show360Report(Request $request)
{
    // 1. Validation
    $request->validate([
        'academic_year_id' => 'required|exists:academic_years,id',
        'semester' => 'required|string',
        'faculty_id' => 'nullable|exists:employees,id' 
    ]);

    $user = Auth::user();
    $userEmployee = $user->employee; 

    // 2. Authorization
    $privilegedRoles = ['admin', 'hr', 'academic_head'];
    $isPrivileged = $userEmployee && in_array($userEmployee->role, $privilegedRoles);

    if ($isPrivileged && $request->filled('faculty_id')) {
        $employee = Employee::find($request->faculty_id);
    } else {
        $employee = $userEmployee;
    }
    
    if (!$employee) {
        return back()->with('error', 'Faculty profile not found.');
    }

    $ayId = $request->academic_year_id;
    $semesterInput = $request->semester; // e.g., "First Semester"
    $activeSem = Semester::where('academic_year_id', $ayId)->first();

    // 3. THE BRIDGE: Normalize "First Semester" to "1st" for legacy queries
    $shortSemester = str_contains($semesterInput, 'First') ? '1st' : (str_contains($semesterInput, 'Second') ? '2nd' : $semesterInput);

    // 4. Data Collection
    // Legacy Evals: Use the short version (1st/2nd)
    $legacyEvals = Evaluation::where([
        'teacher_id' => $employee->id, 
        'academic_year_id' => $ayId, 
        'semester' => $shortSemester 
    ])->get();

    // Student Evals: Use LIKE with the short version to catch "2nd" or "2nd semester"
    $studentEvals = FacultyEvaluation::whereHas('courseBlock', function($q) use ($employee, $ayId, $shortSemester) {
        $q->where('faculty_id', $employee->id)
          ->where('academic_year_id', $ayId)
          ->where('semester', 'LIKE', $shortSemester . '%');
    })->get()->map(function($e) {
        $e->evaluator_type = 'student';
        return $e;
    });

    $allEvals = $legacyEvals->concat($studentEvals);

    if ($allEvals->isEmpty()) {
        return back()->with('warning', 'No evaluation data found for ' . $semesterInput);
    }

    // 5. Calculations (Identical logic)
    $groupScores = [
        'student'    => (float) ($allEvals->where('evaluator_type', 'student')->avg('mean_score') ?? 0),
        'peer'       => (float) ($allEvals->where('evaluator_type', 'peer')->avg('mean_score') ?? 0),
        'self'       => (float) ($allEvals->where('evaluator_type', 'self')->avg('mean_score') ?? 0),
        'supervisor' => (float) ($allEvals->where('evaluator_type', 'supervisor')->avg('mean_score') ?? 0),
    ];

    $finalScore = (float) (array_sum($groupScores) * 0.25);
    $allQuestions = config('evaluation_questions');

    return view('faculty.reports.detailed_360', compact(
        'groupScores', 
        'finalScore', 
        'allQuestions', 
        'allEvals', 
        'semesterInput', 
        'employee', 
        'activeSem'
    ));
}

    public function summary(Request $request)
{
    // 1. Fetch the Active Semester from the DB
    $activeSemester = Semester::where('is_active', 1)->first();
    
    // 2. Set AY and Semester (Prioritize Request > Database Active > Default)
    $ayId = $request->academic_year_id ?? ($activeSemester->academic_year_id ?? null);
    $semesterInput = $request->semester ?? ($activeSemester->name ?? 'First Semester');
    
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

    // 3. Helper: Convert "First Semester" to "1st" for the legacy evaluations table
    $shortSemester = str_contains($semesterInput, 'First') ? '1st' : (str_contains($semesterInput, 'Second') ? '2nd' : $semesterInput);

    $faculties = Employee::with('department')
        ->orderBy('last_name', 'asc')
        ->get()
        ->map(function($employee) use ($ayId, $semesterInput, $shortSemester) {
            
            // Query Legacy Evals (using "1st" or "2nd")
            $legacyEvals = Evaluation::where([
                'teacher_id' => $employee->id, 
                'academic_year_id' => $ayId, 
                'semester' => $shortSemester 
            ])->get();

            // Query Student Evals (using LIKE to catch "2nd" or "2nd semester")
            $studentEvals = FacultyEvaluation::whereHas('courseBlock', function($q) use ($employee, $ayId, $shortSemester) {
                $q->where('faculty_id', $employee->id)
                  ->where('academic_year_id', $ayId)
                  ->where('semester', 'LIKE', $shortSemester . '%');
            })->get()->map(function($e) {
                $e->evaluator_type = 'student'; 
                return $e;
            });

            $allEvals = $legacyEvals->concat($studentEvals);

            // 4. Calculations
            $scores = [
                'student'    => (float) ($allEvals->where('evaluator_type', 'student')->avg('mean_score') ?? 0),
                'peer'       => (float) ($allEvals->where('evaluator_type', 'peer')->avg('mean_score') ?? 0),
                'self'       => (float) ($allEvals->where('evaluator_type', 'self')->avg('mean_score') ?? 0),
                'supervisor' => (float) ($allEvals->where('evaluator_type', 'supervisor')->avg('mean_score') ?? 0),
            ];

            $employee->group_scores = $scores;
            $employee->final_mean = (float) (array_sum($scores) * 0.25);

            return $employee;
        });

    return view('faculty.reports.summary', compact('faculties', 'academicYears', 'ayId', 'semesterInput'));
}

public function studentCompliance(Request $request)
{
    // 1. Setup Period Context
    $activeSemester = Semester::where('is_active', 1)->first();
    $ayId = $request->academic_year_id ?? ($activeSemester->academic_year_id ?? null);
    $semesterInput = $request->semester ?? ($activeSemester->name ?? 'First Semester');
    $sectionFilter = $request->section_id;

    // Normalize to "1st", "2nd" for database LIKE queries
    $shortSemester = str_contains($semesterInput, 'First') ? '1st' : (str_contains($semesterInput, 'Second') ? '2nd' : 'Summer');
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

    // 2. Fetch Available Sections for the Dropdown
    $availableSections = \App\Models\Section::join('section_student', 'sections.id', '=', 'section_student.section_id')
        ->where('section_student.academic_year_id', $ayId)
        ->where('section_student.semester', 'LIKE', $shortSemester . '%')
        ->select('sections.id', 'sections.name')
        ->distinct()
        ->orderBy('sections.name', 'asc')
        ->get();

    // 3. Build Student Compliance Query
    $query = Student::query()
        ->leftJoin('section_student', function($join) use ($ayId, $shortSemester) {
            $join->on('students.id', '=', 'section_student.student_id')
                 ->where('section_student.academic_year_id', $ayId)
                 ->where('section_student.semester', 'LIKE', $shortSemester . '%');
        })
        ->leftJoin('sections', 'section_student.section_id', '=', 'sections.id')
        ->select('students.*', 'sections.name as current_section_name', 'sections.id as current_section_id');

    // Apply server-side Section filter
    if ($request->filled('section_id')) {
        $query->where('sections.id', $request->section_id);
    }

    $students = $query->orderBy('sections.name', 'asc')
        ->orderBy('students.last_name', 'asc')
        ->get()
        ->map(function($student) use ($ayId, $shortSemester) {
            
            // Fetch the specific course load for this student
            $courseLoad = \App\Models\CourseBlock::with(['course', 'faculty'])
                ->join('student_courseblock', 'course_blocks.id', '=', 'student_courseblock.course_block_id')
                ->where('student_courseblock.student_id', $student->id)
                ->where('course_blocks.academic_year_id', $ayId)
                ->where('course_blocks.semester', 'LIKE', $shortSemester . '%')
                ->select('course_blocks.*')
                ->get();

            // Cross-reference with FacultyEvaluations
            $student->subjects = $courseLoad->map(function($block) use ($student) {
                $block->has_been_evaluated = \App\Models\FacultyEvaluation::where([
                    'student_id' => $student->id,
                    'course_block_id' => $block->id
                ])->exists();
                return $block;
            });

            $student->total_subjects = $student->subjects->count();
            $student->completed_count = $student->subjects->where('has_been_evaluated', true)->count();
            $student->is_complete = ($student->total_subjects > 0 && $student->total_subjects === $student->completed_count);
            
            return $student;
        });

    return view('faculty.reports.student_compliance', compact(
        'students', 'academicYears', 'availableSections', 'ayId', 'semesterInput', 'sectionFilter'
    ));
}

}