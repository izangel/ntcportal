<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'section_id',
        'grade',
        'academic_year_id',
        'semester',
        'is_new_student',

        // New audit fields
        'original_grade', 
        'resolution_date',
        'resolved_by_user_id',
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
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the semester associated with the enrollment.
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

     public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}