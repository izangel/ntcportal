<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSyllabus extends Model
{
    protected $fillable = [
        'course_block_id',
        'program_id',
        'grading_system',
        'textbooks_references',
        'course_requirements',
        'classroom_policies',
        'submitted_at',
        'program_head_reviewed_at',
        'program_head_reviewed_by_id',
        'program_head_reviewed_by_name',
        'academic_head_approved_at',
        'academic_head_approved_by_id',
        'academic_head_approved_by_name',
        'revision_requested_at',
        'revision_requested_by_id',
        'revision_requested_by_name',
        'revision_remarks',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'program_head_reviewed_at' => 'datetime',
        'academic_head_approved_at' => 'datetime',
        'revision_requested_at' => 'datetime',
    ];

    public function isSubmitted(): bool
    {
        return ! is_null($this->submitted_at);
    }

    public function isUnderRevision(): bool
    {
        return ! is_null($this->revision_requested_at);
    }

    public function isReviewedByProgramHead(): bool
    {
        return ! is_null($this->program_head_reviewed_at);
    }

    public function isApprovedByAcademicHead(): bool
    {
        return ! is_null($this->academic_head_approved_at);
    }

    public function courseBlock(): BelongsTo
    {
        return $this->belongsTo(CourseBlock::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function programHeadReviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'program_head_reviewed_by_id');
    }

    public function academicHeadApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'academic_head_approved_by_id');
    }

    public function learningPlanItems(): HasMany
    {
        return $this->hasMany(SyllabusLearningPlanItem::class)
            ->orderBy('sort_order');
    }

    public function gradingComponents(): HasMany
    {
        return $this->hasMany(SyllabusGradingComponent::class)
            ->orderBy('sort_order');
    }
}