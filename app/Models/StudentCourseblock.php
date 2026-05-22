<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCourseBlock extends Model
{
    protected $table = 'student_courseblocks';

    protected $fillable = [
        'student_id',
        'course_block_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function courseBlock()
    {
        return $this->belongsTo(CourseBlock::class, 'course_block_id');
    }
}
