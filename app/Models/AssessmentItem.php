<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentItem extends Model
{
    protected $fillable = ['assessment_task_id', 'course_learning_outcome_id', 'item_name', 'max_marks', 'effective_batch_year'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AssessmentTask::class, 'assessment_task_id');
    }

    public function clo(): BelongsTo
    {
        return $this->belongsTo(CourseLearningOutcome::class, 'course_learning_outcome_id');
    }
}