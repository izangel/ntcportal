<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    protected $fillable = [
        'teacher_id', 'evaluator_type', 'evaluator_id', 
        'course_id', 'academic_year_id', 'semester', 
        'ratings', 'mean_score', 'aspects_helped', 
        'aspects_improved', 'comments'
    ];

    // Automatically convert the JSON ratings to a PHP array
    protected $casts = [
        'ratings' => 'array',
        'mean_score' => 'float'
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}