<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEvaluation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'student_id',
        'academic_year_id',
        'semester',
        'rating',            // Stores the calculated overall average (decimal)
        'ratings',           // Stores the JSON array of 15 specific question scores
        'aspects_helped',    // Qualitative: What helped most
        'aspects_improved',  // Qualitative: What needs improvement
        'comments',          // Qualitative: Additional suggestions
    ];

    /**
     * The attributes that should be cast.
     * * IMPORTANT: This ensures the JSON in the database is 
     * automatically converted into a PHP array when accessed.
     */
    protected $casts = [
        'ratings' => 'array',
        'rating'  => 'decimal:2',
    ];

    // --- Relationships ---

    /**
     * The course being evaluated.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * The student who submitted the evaluation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * The academic year this evaluation belongs to.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // --- Helper Methods ---

    /**
     * Optional: Get the label for the overall rating.
     * Returns "Excellent", "Good", etc., based on the average.
     */
    public function getRatingLabelAttribute(): string
    {
        return match (true) {
            $this->rating >= 4.5 => 'Excellent',
            $this->rating >= 3.5 => 'Very Good',
            $this->rating >= 2.5 => 'Good',
            $this->rating >= 1.5 => 'Fair',
            default => 'Poor',
        };
    }
}