<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_block_id',
        'student_id',
        'attendance_date',
        'status',
        'checked_in_at',
        'token',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'checked_in_at' => 'datetime',
    ];

    public const STATUS_PRESENT = 'present';
    public const STATUS_LATE = 'late';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_EXCUSED = 'excused';

    public function courseBlock(): BelongsTo
    {
        return $this->belongsTo(CourseBlock::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
