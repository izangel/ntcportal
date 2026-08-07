<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'units',
        'prerequisite',
        'program_id',
        'description',
    ];

    // Define many-to-many relationship with Student through Enrollment
    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments')
                    ->withPivot('grade', 'created_at')
                    ->withTimestamps();
    }

    // Define hasMany relationship with Enrollment model
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function courseBlocks()
    {
        return $this->hasMany(CourseBlock::class);
    }

    // Define hasMany relationship with Enrollment model
    public function coursetosections()
    {
        return $this->hasMany(CourseToSection::class);
    }

    /**
     * Relationship to Course Learning Outcomes (CLOs)
     */
    public function learningOutcomes(): HasMany
    {
        return $this->hasMany(CourseLearningOutcome::class);
    }

    /**
     * Alias if you also use clos() elsewhere in your project
     */
    public function clos(): HasMany
    {
        return $this->hasMany(CourseLearningOutcome::class);
    }

    public function assessmentTasks(): HasMany
    {
        return $this->hasMany(AssessmentTask::class);
    }

   


    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function getTotalEnrolledStudentsAttribute()
    {
        return StudentAssessmentMark::whereHas('assessmentItem.task', function ($query) {
            $query->where('course_id', $this->id);
        })->distinct('student_id')->count('student_id');
    }

    /**
     * Get overall completion/success rate across all CLOs for this course.
     */ 
    public function getCompletionRateAttribute()
    {
        $clos = $this->learningOutcomes;
        if ($clos->isEmpty()) {
            return 0;
        }

        $scores = $clos->map(fn($clo) => $clo->attainment)->filter();

        return $scores->count() > 0 ? round($scores->avg(), 1) : 0;
    }

    /**
     * Get CLO Attainment for the course (alias for completion rate).
     */
    public function getCloAttainmentAttribute()
    {
        return $this->completion_rate;
    }

}