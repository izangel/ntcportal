<?php

namespace App\Http\Controllers;

use App\Models\{Student, Semester, CourseBlock, FacultyEvaluation}; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Add this for safety

class StudentEvaluationController extends Controller
{
    private function resolveSemesterName(?Semester $semester): string
    {
        if (!$semester) return 'N/A';
        return match (true) {
            str_contains($semester->name, 'First')  => '1st',
            str_contains($semester->name, 'Second') => '2nd',
            default                                 => 'Summer',
        };
    }

    public function index()
{
    try {
        $user = auth()->user();
        
        // 1. Get Student Record
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        
        // 2. Get Active Semester
        $activeSemester = \App\Models\Semester::with('academicYear')
            ->where('is_active', 1)
            ->first();

        // 3. Resolve Display Name (e.g., "2nd")
        $semesterDisplayName = $this->resolveSemesterName($activeSemester);

        // 4. Handle Missing Data
        if (!$student || !$activeSemester) {
            return view('student.evaluations.index', [
                'enrolledCourses' => collect([]),
                'activeSemester'  => $activeSemester,
                'semesterName'    => 'N/A',
                'completedCount'  => 0,
                'totalCount'      => 0,
            ]);
        }

        $activeSemester = \App\Models\Semester::with('academicYear')->where('is_active', 1)->first();
//dd($activeSemester->academicYear); // If this is null, the relationship is broken.

        // 5. Query Course Blocks using the new student_courseblock table
        $enrolledCourses = \App\Models\CourseBlock::with(['course', 'faculty'])
            ->join('student_courseblock', 'course_blocks.id', '=', 'student_courseblock.course_block_id')
            ->where('student_courseblock.student_id', $student->id)
            ->where('course_blocks.academic_year_id', $activeSemester->academic_year_id)
            // Use the "2nd%" wildcard to match "2nd Semester" in the DB
            ->where('course_blocks.semester', 'LIKE', $semesterDisplayName . '%')
            ->select('course_blocks.*') // Ensure we don't get student_courseblock.id
            ->get()
            ->map(function ($block) use ($student) {
                // Check against the NEW evaluation table
                $block->has_evaluated = \App\Models\FacultyEvaluation::where([
                    'student_id'      => $student->id,
                    'course_block_id' => $block->id,
                ])->exists();
                
                return $block;
            });

        return view('student.evaluations.index', [
            'student' => $student,
            'enrolledCourses' => $enrolledCourses,
            'activeSemester'  => $activeSemester,
            'semesterName'    => $semesterDisplayName,
            'completedCount'  => $enrolledCourses->where('has_evaluated', true)->count(),
            'totalCount'      => $enrolledCourses->count()
        ]);

    } catch (\Exception $e) {
        // If the page is blank, this will force it to show the error
        return "Error in Evaluation Index: " . $e->getMessage() . " at line " . $e->getLine();
    }
}

    public function store(Request $request, CourseBlock $courseBlock)
    {
        $request->validate([
            'ratings' => 'required|array',
            'aspects_helped' => 'nullable|string|max:2000',
            'aspects_improved' => 'nullable|string|max:2000',
            'comments' => 'nullable|string|max:1000',
        ]);

        $student = \App\Models\Student::where('user_id', auth()->id())->first();

        // Calculate the mean_score from the ratings array
        $meanScore = array_sum($request->ratings) / count($request->ratings);

        // Save to the NEW faculty_evaluations table
        \App\Models\FacultyEvaluation::create([
            'student_id'       => $student->id,
            'course_block_id'  => $courseBlock->id, // This is the key link
            'ratings'          => $request->ratings,
            'mean_score'       => $meanScore,
            'aspects_helped'   => $request->aspects_helped,
            'aspects_improved' => $request->aspects_improved,
            'comments'         => $request->comments,
        ]);

        return redirect()->route('student.evaluations.index')
            ->with('success', 'Thank you! Your feedback has been recorded.');
    }

    public function create(CourseBlock $courseBlock)
    {
        // Eager load relations to show Course Name and Faculty Name on the form
        $courseBlock->load(['course', 'faculty']);

        if (!$courseBlock->faculty) {
            return redirect()->route('student.evaluations.index')
                ->with('error', 'Instructor information is missing for this course.');
        }

        $activeSemester = \App\Models\Semester::with('academicYear')->where('is_active', 1)->first();
        $semesterName = $this->resolveSemesterName($activeSemester);

        return view('student.evaluations.form', compact('courseBlock', 'activeSemester', 'semesterName'));
    }
}