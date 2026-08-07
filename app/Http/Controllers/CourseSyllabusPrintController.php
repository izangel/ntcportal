<?php

namespace App\Http\Controllers;

use App\Models\CourseBlock;
use App\Models\CourseSyllabus;
use App\Models\Program;
use App\Services\CourseSyllabusData;
use Illuminate\Support\Facades\Auth;

class CourseSyllabusPrintController extends Controller
{
    public function show(CourseBlock $courseBlock, $program = null)
    {
        $user = Auth::user();

        if (!$user?->employee) {
            abort(403, 'You are not assigned to this class.');
        }

        $isOwner = $user->employee->id === $courseBlock->faculty_id;
        $isAcademicHead = $user->hasRole('academic_head');
        $isProgramHead = $user->hasRole('program_head') || $user->hasRole('program_head_college') || $user->hasRole('program_head_shs');

        if (!$isOwner && !$isAcademicHead && !$isProgramHead) {
            abort(403, 'You are not assigned to this class.');
        }

        $svc = new CourseSyllabusData($courseBlock);

        $programs = $svc->programs();
        $programModel = $program ? Program::find($program) : null;

        if (!$programModel) {
            if ($programs->count() === 1) {
                $programModel = $programs->first();
            } else {
                $programModel = $svc->program();
            }
        }

        if (!$programModel) {
            abort(404, 'No program could be resolved for this course block.');
        }

        $data = new CourseSyllabusData($courseBlock, $programModel);
        $syllabus = CourseSyllabus::with(['learningPlanItems', 'gradingComponents'])
            ->where('course_block_id', $courseBlock->id)
            ->where('program_id', $programModel->id)
            ->first();

        return view('faculty.syllabus.print', [
            'data' => $data,
            'syllabus' => $syllabus,
        ]);
    }
}