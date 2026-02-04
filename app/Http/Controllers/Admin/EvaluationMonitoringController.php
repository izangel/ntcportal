<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, Semester, SectionStudent, Enrollment, CourseBlock, Evaluation};
use Illuminate\Http\Request;

class EvaluationMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $activeSemester = Semester::where('is_active', 1)->first();
        $semesterName = $activeSemester ? $this->getSemesterName($activeSemester->name) : 'N/A';
        
        // 1. Fetch all students for the dropdown (Eager load user for names)
        $students = Student::with('user')->get()->sortBy('user.last_name');

        $selectedStudent = null;
        $enrolledCourses = collect([]);
        $completedCount = 0;
        $totalCount = 0;

        // 2. Logic for the selected student
        if ($request->filled('student_id')) {
            $selectedStudent = Student::with('user')->find($request->student_id);

            if ($selectedStudent && $activeSemester) {
                $studentSection = SectionStudent::where('student_id', $selectedStudent->id)
                    ->where('academic_year_id', $activeSemester->academic_year_id)
                    ->where('semester', $semesterName)
                    ->first();

                if ($studentSection) {
                    $enrolledCourseIds = Enrollment::where('student_id', $selectedStudent->id)
                        ->where('academic_year_id', $activeSemester->academic_year_id)
                        ->where('semester', $semesterName)
                        ->pluck('course_id');

                    $enrolledCourses = CourseBlock::with(['course', 'faculty'])
                        ->where('section_id', $studentSection->section_id)
                        ->where('academic_year_id', $activeSemester->academic_year_id)
                        ->where('semester', $semesterName)
                        ->whereIn('course_id', $enrolledCourseIds)
                        ->get()
                        ->map(function ($block) use ($selectedStudent, $activeSemester, $semesterName) {
                            $block->has_evaluated = Evaluation::where([
                                'evaluator_id'     => $selectedStudent->user_id,
                                'evaluator_type'   => 'student',
                                'course_id'        => $block->course_id,
                                'academic_year_id' => $activeSemester->academic_year_id,
                                'semester'         => $semesterName
                            ])->exists();
                            return $block;
                        });

                    $completedCount = $enrolledCourses->where('has_evaluated', true)->count();
                    $totalCount = $enrolledCourses->count();
                }
            }
        }

        return view('admin.monitoring.index', compact(
            'students', 'selectedStudent', 'enrolledCourses', 
            'activeSemester', 'semesterName', 'completedCount', 'totalCount'
        ));
    }

    private function getSemesterName($name) {
        return match (true) {
            str_contains($name, 'First')  => '1st',
            str_contains($name, 'Second') => '2nd',
            default                       => 'Summer',
        };
    }
}