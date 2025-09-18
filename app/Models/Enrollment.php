<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'grade',
        'semester_id',
        'is_new_student',
    ];

    protected $casts = [
        'is_new_student' => 'boolean', // <-- ADD THIS LINE
    ];

    // Define relationship with Student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Define relationship with Course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the semester associated with the enrollment.
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}