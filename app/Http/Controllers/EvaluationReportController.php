<?php

namespace App\Http\Controllers;

use App\Models\{CourseEvaluation, Semester, CourseBlock, Employee};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class EvaluationReportController extends Controller
{
    // --- FACULTY VIEW (Personal Results) ---
    public function facultyView()
    {
        $user = Auth::user();
        $activeSem = \App\Models\Semester::where('is_active', 1)->first();
        
        // Find the Employee record linked to this User
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Faculty profile not found.');
        }

        if (!$activeSem) return back()->with('error', 'No active semester found.');

        
        // This converts "First Semester" -> "1st", "Second Semester" -> "2nd", else "Summer"
        $semesterName = match (true) {
            str_contains($activeSem->name, 'First')  => '1st',
            str_contains($activeSem->name, 'Second') => '2nd',
            default                                        => 'Summer',
        };

        // 1. Get only this teacher's course IDs for the current term
        $myCourseIds = \App\Models\CourseBlock::where('faculty_id', $employee->id)
            ->where('academic_year_id', $activeSem->academic_year_id)
            ->where('semester', $semesterName)
            ->pluck('course_id');

        // 2. Aggregate ratings for these specific courses
        $reports = \App\Models\CourseEvaluation::with('course')
            ->select('course_id', 
                \DB::raw('AVG(rating) as average_rating'), 
                \DB::raw('COUNT(*) as total_responses')
            )
            ->whereIn('course_id', $myCourseIds)
            ->where('academic_year_id', $activeSem->academic_year_id)
            ->groupBy('course_id')
            ->get();

        // 3. Get recent qualitative feedback for these courses
        $comments = \App\Models\CourseEvaluation::with('course')
            ->whereIn('course_id', $myCourseIds)
            ->where('academic_year_id', $activeSem->academic_year_id)
            ->whereNotNull('comments')
            ->latest()
            ->take(20)
            ->get();

        return view('reports.faculty-individual', compact('reports', 'comments', 'activeSem', 'employee'));
    }
    public function index()
{
    $facultyId = auth()->user()->employee->id; // Assuming faculty relationship exists

    $academicYears = \App\Models\AcademicYear::orderBy('start_year', 'desc')->get();
    
    // Fetch only courses assigned to this faculty member via CourseBlocks
    $courses = \App\Models\Course::whereHas('courseBlocks', function($q) use ($facultyId) {
        $q->where('faculty_id', $facultyId);
    })->get();

    return view('faculty.evaluations.index', compact('academicYears', 'courses'));
}



    public function facultyCourseReport(Request $request) // This is what you need
{
    // Now you get the ID from the request like this:
    $courseId = $request->course_id;

    $activeSem = \App\Models\Semester::where('is_active', 1)->first();

     // This converts "First Semester" -> "1st", "Second Semester" -> "2nd", else "Summer"
        $semesterName = match (true) {
            str_contains($activeSem->name, 'First')  => '1st',
            str_contains($activeSem->name, 'Second') => '2nd',
            default                                        => 'Summer',
        };
    
    // Fetch all evaluations for this specific course and semester
    $evaluations = \App\Models\CourseEvaluation::where('course_id', $courseId)
        ->where('academic_year_id', $activeSem->academic_year_id)
        ->where('semester', $semesterName)
        ->get();

    if ($evaluations->isEmpty()) {
        return back()->with('error', 'No evaluations found for this course yet.');
    }

    // Mapping questions to categories
    $categories = [
        'A' => ['name' => 'Course Design & Content', 'keys' => ['q1', 'q2', 'q3', 'q4']],
        'B' => ['name' => 'Teaching Effectiveness', 'keys' => ['q5', 'q6', 'q7', 'q8']],
        'C' => ['name' => 'Assessment & Feedback', 'keys' => ['q9', 'q10', 'q11']],
        'D' => ['name' => 'Learning Resources', 'keys' => ['q12', 'q13']],
        'E' => ['name' => 'Learning Outcomes', 'keys' => ['q14', 'q15']],
        'F' => ['name' => 'Overall Evaluation', 'keys' => ['q16', 'q17']],
    ];

    $reportData = [];
    foreach ($categories as $key => $cat) {
        $scores = [];
        foreach ($evaluations as $eval) {
            foreach ($cat['keys'] as $qKey) {
                if (isset($eval->ratings[$qKey])) {
                    $scores[] = $eval->ratings[$qKey];
                }
            }
        }
        $reportData[$key] = [
            'name' => $cat['name'],
            'avg' => count($scores) > 0 ? array_sum($scores) / count($scores) : 0
        ];
    }

    $course = \App\Models\Course::find($courseId);

    return view('reports.faculty-course', compact('reportData', 'evaluations', 'course', 'activeSem'));
}

    // --- ADMIN VIEW (Global Results) ---

    public function adminView()
{
    $activeSem = \App\Models\Semester::where('is_active', 1)->first();
    $ayId = $activeSem->academic_year_id;
    if (!$activeSem) return back()->with('error', 'No active semester found.');

        
        // This converts "First Semester" -> "1st", "Second Semester" -> "2nd", else "Summer"
        $semShort = match (true) {
            str_contains($activeSem->name, 'First')  => '1st',
            str_contains($activeSem->name, 'Second') => '2nd',
            default                                        => 'Summer',
        };

    

    // 1. Get Aggregated Ratings joined with Faculty/Employee names
    // We join CourseBlocks to know which teacher handled which course
    $reports = \App\Models\CourseEvaluation::query()
        ->join('courses', 'course_evaluations.course_id', '=', 'courses.id')
        // We link to course_blocks to find the faculty assigned
        ->join('course_blocks', function($join) use ($ayId, $semShort) {
            $join->on('course_evaluations.course_id', '=', 'course_blocks.course_id')
                 ->where('course_blocks.academic_year_id', '=', $ayId)
                 ->where('course_blocks.semester', '=', $semShort);
        })
        ->join('employees', 'course_blocks.faculty_id', '=', 'employees.id')
        ->select(
            'courses.code as course_code',
            'courses.name as course_name',
            'employees.name as faculty_name',
            \DB::raw('AVG(course_evaluations.rating) as average_rating'),
            \DB::raw('COUNT(course_evaluations.id) as total_responses')
        )
        ->where('course_evaluations.academic_year_id', $ayId)
        ->where('course_evaluations.semester', $semShort)
        ->groupBy('courses.code', 'courses.name', 'employees.name')
        ->orderBy('average_rating', 'desc')
        ->get();

    // 2. Get all recent comments across the school
    $recentComments = \App\Models\CourseEvaluation::with(['course'])
        ->where('academic_year_id', $ayId)
        ->where('semester', $semShort)
        ->whereNotNull('comments')
        ->latest()
        ->take(30)
        ->get();

    return view('reports.admin-global', compact('reports', 'recentComments', 'activeSem'));
}
    
}