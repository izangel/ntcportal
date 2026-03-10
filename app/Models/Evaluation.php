<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This stops the mass assignment crash.
     */
    protected $fillable = [
        'teacher_id',
        'evaluator_type',
        'evaluator_id',
        'course_id',
        'academic_year_id',
        'semester',
        'ratings',
        'mean_score',
        'aspects_helped',
        'aspects_improved',
        'comments',
    ];

    /**
     * The attributes that should be cast.
     * This stops the "Array to string conversion" crash for the JSON column.
     */
    protected $casts = [
        'ratings' => 'array',
        'mean_score' => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Relationships (Optional but helpful for displaying data later)
    // -------------------------------------------------------------------------

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id'); // Assuming teachers are in users table
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}