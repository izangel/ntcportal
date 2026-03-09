<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourseblock extends Model
{
    use HasFactory;

    protected $table = 'student_courseblock';

    protected $fillable = [
        'student_id',
        'course_code',
        'course_title',
        'faculty',
        'rooms',
        'schedule',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
