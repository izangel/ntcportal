<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplicationClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_application_id',
        'course_code',
        'title',
        'day_time_room',
        'topics',
        'substitute_teacher_id',
        'acknowledgement_signature',
        'sub_ack_at', // NEW: Timestamp of substitute's acknowledgment
        'sub_ack_by', // NEW: ID of the employee who acknowledged (the substitute)
    ];

    protected $casts = [
        'sub_ack_at' => 'datetime', // Cast this new timestamp column
    ];

    public function leaveApplication()
    {
        return $this->belongsTo(LeaveApplication::class);
    }

    public function substituteTeacher()
    {
        return $this->belongsTo(Employee::class, 'substitute_teacher_id');
    }

    // NEW: Relationship to the Employee who acknowledged this assignment
    public function acknowledgedBy()
    {
        return $this->belongsTo(Employee::class, 'sub_ack_by');
    }
}