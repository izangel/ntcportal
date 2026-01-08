<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Student, Semester, SectionStudent, Enrollment, CourseBlock, CourseEvaluation};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentEvaluationController extends Controller
{
    /**
     * Display the list of courses and their evaluation status.
     */
    // app/Http/Controllers/Student/StudentEvaluationController.php

public function index()
{
    $user = auth()->user();
    $student = \App\Models\Student::where('user_id', $user->id)->first();
    $activeSemester = \App\Models\Semester::where('is_active', 1)->first();

    $semesterName = match (true) {
        str_contains($activeSemester->name, 'First')  => '1st',
        str_contains($activeSemester->name, 'Second') => '2nd',
        default                                        => 'Summer',
    };

    $studentSection = \App\Models\SectionStudent::where('student_id', $student->id)
        ->where('academic_year_id', $activeSemester->academic_year_id)
        ->where('semester', $semesterName)
        ->first();


    if (!$studentSection) return view('student.evaluations.index', ['enrolledCourses' => collect([])]);

    $enrolledCourseIds = \App\Models\Enrollment::where('student_id', $student->id)
        ->where('academic_year_id', $activeSemester->academic_year_id)
        ->where('semester', $semesterName)
        ->pluck('course_id');

    $enrolledCourses = \App\Models\CourseBlock::with(['course', 'faculty'])
        ->where('section_id', $studentSection->section_id)
        ->where('academic_year_id', $activeSemester->academic_year_id)
        ->where('semester', $semesterName)
        ->whereIn('course_id', $enrolledCourseIds)
        ->get()
        ->map(function ($block) use ($user, $activeSemester, $semesterName) {
            // CHECKING THE UNIFIED EVALUATIONS TABLE
            $block->has_evaluated = \App\Models\Evaluation::where([
                'evaluator_id'     => $user->id,
                'evaluator_type'   => 'student',
                'course_id'        => $block->course_id,
                'academic_year_id' => $activeSemester->academic_year_id,
                'semester'         => $semesterName
            ])->exists();
            return $block;
        });

    return view('student.evaluations.index', [
        'enrolledCourses' => $enrolledCourses,
        'activeSemester'  => $activeSemester,
        'semesterName'    => $semesterName,
        'completedCount'  => $enrolledCourses->where('has_evaluated', true)->count(),
        'totalCount'      => $enrolledCourses->count()
    ]);
}

public function store(Request $request, CourseBlock $courseBlock)
{
    $request->validate([
        'ratings' => 'required|array',
        'aspects_helped' => 'nullable|string|max:2000',
        'aspects_improved' => 'nullable|string|max:2000',
        'comments' => 'nullable|string|max:1000',
    ]);

    $activeSemester = \App\Models\Semester::where('is_active', 1)->first();
    $semesterName = match (true) {
        str_contains($activeSemester->name, 'First')  => '1st',
        str_contains($activeSemester->name, 'Second') => '2nd',
        default                                        => 'Summer',
    };

    // Calculate the mean_score
    $meanScore = array_sum($request->ratings) / count($request->ratings);

    \App\Models\Evaluation::create([
        'teacher_id'       => $courseBlock->faculty_id,
        'evaluator_type'   => 'student',
        'evaluator_id'     => auth()->id(),
        'course_id'        => $courseBlock->course_id,
        'academic_year_id' => $activeSemester->academic_year_id,
        'semester'         => $semesterName,
        'ratings'          => $request->ratings,
        'mean_score'       => $meanScore,
        'aspects_helped'   => $request->aspects_helped,   // NEW
        'aspects_improved' => $request->aspects_improved, // NEW
        'comments'         => $request->comments,
    ]);

    return redirect()->route('student.evaluations.index')
        ->with('success', 'Thank you! Your feedback has been recorded.');
}

    /**
     * Show the evaluation form for a specific course block.
     */
    // app/Http/Controllers/Student/StudentEvaluationController.php

public function create(CourseBlock $courseBlock)
{
    // 1. Eager load relationships to prevent the form from crashing
    $courseBlock->load(['course', 'faculty']);

    // 2. Critical Check: If faculty is missing, the form will fail to render names
    if (!$courseBlock->faculty) {
        return redirect()->route('student.evaluations.index')
            ->with('error', 'Instructor information is missing for this course.');
    }

    // 3. Sync Semester Logic with your Index method
    $activeSemester = \App\Models\Semester::with('academicYear')->where('is_active', 1)->first();
    
    $semesterName = match (true) {
        str_contains($activeSemester->name, 'First')  => '1st',
        str_contains($activeSemester->name, 'Second') => '2nd',
        default                                        => 'Summer',
    };

    return view('student.evaluations.form', compact('courseBlock', 'activeSemester', 'semesterName'));
}

   
}