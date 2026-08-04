<?php

namespace App\Http\Controllers;

use App\Models\CourseBlock;
use App\Models\CourseSyllabus;
use App\Services\CourseSyllabusData;
use Illuminate\Support\Facades\Auth;

class CourseSyllabusPrintController extends Controller
{
    public function show(CourseBlock $courseBlock)
    {
        if (!Auth::user()?->employee || Auth::user()->employee->id !== $courseBlock->faculty_id) {
            abort(403, 'You are not assigned to this class.');
        }

        $data = new CourseSyllabusData($courseBlock);
        $syllabus = CourseSyllabus::with('learningPlanItems')
            ->where('course_block_id', $courseBlock->id)
            ->first();

        return view('faculty.syllabus.print', [
            'data' => $data,
            'syllabus' => $syllabus,
        ]);
    }
}