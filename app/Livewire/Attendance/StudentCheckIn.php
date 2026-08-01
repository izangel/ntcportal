<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceRecord;
use App\Models\CourseBlock;
use App\Support\AttendanceToken;

class StudentCheckIn extends Component
{
    public $token;
    public $result = 'invalid';
    public $message;
    public $block = null;
    public $checkedInAt = null;

    public function mount($token)
    {
        $this->token = $token;

        $data = AttendanceToken::validate($token);

        if (!$data) {
            $this->result = 'invalid';
            $this->message = 'This QR code is invalid or has already expired. Please ask your instructor to refresh the QR code.';
            return;
        }

        $block = CourseBlock::with('course')->find($data['block']);

        if (!$block) {
            $this->result = 'invalid';
            $this->message = 'The class linked to this QR code could not be found.';
            return;
        }

        $student = Auth::user()->student;

        if (!$student) {
            $this->result = 'invalid';
            $this->message = 'Only enrolled students can check in through this QR code.';
            return;
        }

        $enrolled = DB::table('student_courseblock')
            ->where('student_id', $student->id)
            ->where('course_block_id', $block->id)
            ->exists();

        if (!$enrolled) {
            $this->result = 'invalid';
            $this->message = 'You are not enrolled in this class (' . $block->course?->code . '), so you cannot check in here.';
            return;
        }

        $this->block = [
            'course_code' => $block->course?->code,
            'course_name' => $block->course?->name,
            'schedule_string' => $block->schedule_string,
            'room_name' => $block->room_name,
            'faculty_name' => $block->faculty ? trim($block->faculty->first_name . ' ' . $block->faculty->last_name) : 'TBA',
        ];

        $record = AttendanceRecord::updateOrCreate(
            [
                'course_block_id' => $block->id,
                'student_id' => $student->id,
                'attendance_date' => today()->toDateString(),
            ],
            [
                'status' => AttendanceRecord::STATUS_PRESENT,
                'checked_in_at' => now(),
                'token' => $token,
            ]
        );

        $this->checkedInAt = $record->checked_in_at;
        $this->result = 'success';
        $this->message = 'You have been marked present for ' . ($block->course?->code ?? 'this class') . '.';
    }

    public function render()
    {
        return view('livewire.attendance.student-check-in')
            ->extends('layouts.admin')
            ->section('content');
    }
}
