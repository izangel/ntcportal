<?php

namespace App\Models; // <--- MUST MATCH FOLDER PATH

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyEvaluation extends Model
{
    use HasFactory;

    // Point it to your new table name
    protected $table = 'faculty_evaluations';

    protected $fillable = [
        'student_id',
        'course_block_id',
        'ratings',
        'mean_score',
        'aspects_helped',
        'aspects_improved',
        'comments'
    ];

    protected $casts = [
        'ratings' => 'array',
        'mean_score' => 'decimal:2',
    ];

    

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function courseBlock()
    {
        return $this->belongsTo(CourseBlock::class);
    }
}