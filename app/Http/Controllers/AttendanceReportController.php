<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRecord;
use App\Models\CourseBlock;

class AttendanceReportController extends Controller
{
    public function printRoster(Request $request)
    {
        $request->validate([
            'course_block_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $block = CourseBlock::with(['course', 'students', 'academicYear'])
            ->findOrFail($request->input('course_block_id'));

        if (!Auth::user()->employee || Auth::user()->employee->id !== $block->faculty_id) {
            abort(403, 'You are not assigned to this class.');
        }

        $date = $request->input('date');

        $records = AttendanceRecord::where('course_block_id', $block->id)
            ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        $roster = $block->students
            ->map(function ($student) use ($records) {
                $record = $records->get($student->id);

                return [
                    'student_number' => $student->student_id,
                    'name' => trim($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')),
                    'status' => $record?->status,
                    'checked_in_at' => $record?->checked_in_at,
                ];
            })
            ->sortBy('name')
            ->values();

        return view('attendance.print-roster', [
            'block' => $block,
            'date' => $date,
            'roster' => $roster,
        ]);
    }
}
