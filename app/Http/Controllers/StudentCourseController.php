<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseBlock;
use App\Models\SectionStudent;
use App\Models\Enrollment;
use App\Models\AcademicYear; // Assuming you have a model for term management
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\CourseEvaluation;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;


class StudentCourseController extends Controller
{
    /**
     * Display the student's enrolled courses for the current term.
     */
    public function index()
{
    $user = Auth::user();

    // 1. Resolve Student profile
    $student = \App\Models\Student::where('user_id', $user->id)->first();
    if (!$student) return back()->with('error', 'Student profile not found.');

    // 2. Get the Active Semester
    $activeSemester = \App\Models\Semester::where('is_active', 1)->first();
    if (!$activeSemester) return back()->with('error', 'No active semester found.');

    // 3. Transform Semester Name for Querying
    // This converts "First Semester" -> "1st", "Second Semester" -> "2nd", else "Summer"
    $semesterName = match (true) {
        str_contains($activeSemester->name, 'First')  => '1st',
        str_contains($activeSemester->name, 'Second') => '2nd',
        default                                        => 'Summer',
    };

    $currentAYId = $activeSemester->academic_year_id;

    // 4. Find Section Assignment
    $studentSection = SectionStudent::where('student_id', $student->id)
        ->where('academic_year_id', $currentAYId)
        ->where('semester', $semesterName)
        ->first();

    if (!$studentSection) {
        return view('student.courses.index', [
            'enrolledCourses' => [],
            'activeSemester' => $activeSemester,
            'currentAY' => $activeSemester->academicYear // Assuming relationship exists
        ]);
    }

    // 5. Get officially enrolled course IDs
    $enrolledCourseIds = Enrollment::where('student_id', $student->id)
        ->where('academic_year_id', $currentAYId)
        ->where('semester', $semesterName)
        ->pluck('course_id');

    // 6. Fetch Course Blocks (Schedule & Faculty)
    $enrolledCourses = CourseBlock::with(['course', 'faculty'])
        ->where('section_id', $studentSection->section_id)
        ->where('academic_year_id', $currentAYId)
        ->where('semester', $semesterName)
        ->whereIn('course_id', $enrolledCourseIds)
        ->get()
        ->map(function ($block) use ($student, $currentAYId, $semesterName) {
            // Check if student already evaluated this course
            $block->has_evaluated = \App\Models\CourseEvaluation::where('student_id', $student->id)
                ->where('course_id', $block->course_id)
                ->where('academic_year_id', $currentAYId)
                ->where('semester', $semesterName)
                ->exists();
            return $block;
        });

    return view('student.courses.index', compact('enrolledCourses', 'activeSemester', 'semesterName'));
}

    public function storeEvaluation(Request $request)
    {
        // 1. Validation Logic
        // We require exactly 15 ratings corresponding to your survey questions
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'ratings' => 'required|array|size:17',
            'ratings.*' => 'required|integer|min:1|max:5',
            'aspects_helped' => 'nullable|string|max:2000',
            'aspects_improved' => 'nullable|string|max:2000',
            'comments' => 'nullable|string|max:2000',
        ]);

        // dd('Passed Validation');

        $user = Auth::user();
        
        if (!$user->student) {
            // CHECKPOINT 3: Does this user have a student profile?
           
            return back()->with('error', 'Only students can submit evaluations.');
        }

        // 2. Fetch Active Academic Term
        $activeSem = Semester::where('is_active', 1)->first();
        
         if (!$activeSem) return back()->with('error', 'No active semester found.');

        
        // This converts "First Semester" -> "1st", "Second Semester" -> "2nd", else "Summer"
        $semesterName = match (true) {
            str_contains($activeSem->name, 'First')  => '1st',
            str_contains($activeSem->name, 'Second') => '2nd',
            default                                        => 'Summer',
        };

        if (!$activeSem) {
            return back()->with('error', 'Evaluation failed: No active semester found.');
        }

        // 3. Prevent Duplicate Submissions
        // Ensures a student only evaluates a specific course once per term
        $alreadyEvaluated = CourseEvaluation::where([
            'student_id' => $user->student->id,
            'course_id' => $request->course_id,
            'academic_year_id' => $activeSem->academic_year_id,
            'semester' => $semesterName,
        ])->exists();

        if ($alreadyEvaluated) {
            return back()->with('error', 'You have already submitted an evaluation for this course.');
        }

        // 4. Calculate Mathematical Mean (Average)
        // This average is used for the high-level "Star" reports
        $ratings = $request->input('ratings');
        $averageRating = collect($ratings)->avg();

        // Identify the Teacher for the PES Report
        $teacherId = null;
        $enrollment = Enrollment::where('student_id', $user->student->id)
            ->where('course_id', $request->course_id)
            ->where('academic_year_id', $activeSem->academic_year_id)
            ->where('semester', $semesterName)
            ->first();

        if ($enrollment) {
            $block = CourseBlock::where('section_id', $enrollment->section_id)
                ->where('course_id', $request->course_id)
                ->where('academic_year_id', $activeSem->academic_year_id)
                ->where('semester', $semesterName)
                ->first();
            $teacherId = $block ? $block->faculty_id : null;
        }

        // 5. Database Persistence
        try {
            DB::beginTransaction();

            CourseEvaluation::create([
                'student_id'       => $user->student->id,
                'course_id'        => $request->course_id,
                'academic_year_id' => $activeSem->academic_year_id,
                'semester'         => $semesterName,
                'rating'           => $averageRating, // The decimal average (e.g., 4.67)
                'ratings'          => $ratings,       // The raw JSON array of 15 questions
                'aspects_helped'   => $request->aspects_helped,
                'aspects_improved' => $request->aspects_improved,
                'comments'         => $request->comments,
            ]);

            // Also save to the main Evaluation table for the Teacher's PES Report
            if ($teacherId) {
                \App\Models\Evaluation::create([
                    'teacher_id'       => $teacherId,
                    'evaluator_id'     => $user->student->id,
                    'evaluator_type'   => 'student',
                    'academic_year_id' => $activeSem->academic_year_id,
                    'semester'         => $semesterName,
                    'ratings'          => $ratings,
                    'mean_score'       => $averageRating,
                    'aspects_helped'   => $request->aspects_helped,
                    'aspects_improved' => $request->aspects_improved,
                    'comments'         => $request->comments,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Evaluation submitted! Thank you for your feedback.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for the admin, show a clean message to the student
            \Log::error('Evaluation Store Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while saving your evaluation. Please try again.');
        }
    }
}