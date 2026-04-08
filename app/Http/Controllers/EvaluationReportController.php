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
    // 1. Get Active Period (or filtered)
    $activeSemester = Semester::where('is_active', 1)->first();
    $ayId = $request->academic_year_id ?? ($activeSemester->academic_year_id ?? null);
    $semesterInput = $request->semester ?? ($activeSemester->name ?? 'First Semester');

    // 2. Resolve short name for CourseBlock matching (1st, 2nd, etc.)
    $shortSemester = str_contains($semesterInput, 'First') ? '1st' : (str_contains($semesterInput, 'Second') ? '2nd' : 'Summer');

    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

    // 3. Fetch all students and map their progress
    $students = Student::orderBy('last_name', 'asc')->get()->map(function($student) use ($ayId, $shortSemester) {
        
        // Find enrolled course blocks for this specific period
        // Logic matches your StudentEvaluationController@index
        $courseLoad = \App\Models\CourseBlock::join('student_courseblock', 'course_blocks.id', '=', 'student_courseblock.course_block_id')
            ->where('student_courseblock.student_id', $student->id)
            ->where('course_blocks.academic_year_id', $ayId)
            ->where('course_blocks.semester', 'LIKE', $shortSemester . '%')
            ->select('course_blocks.id')
            ->get();

        $totalSubjects = $courseLoad->count();
        $courseBlockIds = $courseLoad->pluck('id');

        // Check how many of those IDs exist in the FacultyEvaluation table for this student
        $completedCount = \App\Models\FacultyEvaluation::where('student_id', $student->id)
            ->whereIn('course_block_id', $courseBlockIds)
            ->count();

        $student->total_subjects = $totalSubjects;
        $student->completed_count = $completedCount;
        $student->is_complete = ($totalSubjects > 0 && $totalSubjects === $completedCount);
        
        return $student;
    });

    return view('faculty.reports.student_compliance', compact('students', 'academicYears', 'ayId', 'semesterInput'));
}


}