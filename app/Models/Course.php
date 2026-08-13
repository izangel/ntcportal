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

    /**
     * Courses that must be completed before this course (many-to-many via
     * the course_prerequisite pivot).
     */
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_prerequisite', 'course_id', 'prerequisite_course_id');
    }

    /**
     * Courses that require this course as a prerequisite (inverse of
     * prerequisites()).
     */
    public function dependentCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_prerequisite', 'prerequisite_course_id', 'course_id');
    }

    /**
     * Comma-separated codes of this course's prerequisites, falling back to
     * the legacy free-text column.
     */
    public function getPrerequisiteLabelAttribute(): string
    {
        $codes = $this->prerequisites->pluck('code')->filter();

        if ($codes->isNotEmpty()) {
            return $codes->implode(', ');
        }

        return $this->prerequisite ?: '';
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

        $scores = $clos->map(fn ($clo) => $clo->attainment)->filter();

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
