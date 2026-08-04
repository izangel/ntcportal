<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyllabusLearningPlanItem extends Model
{
    protected $fillable = [
        'course_syllabus_id',
        'learning_outcomes',
        'topics_readings',
        'schedule',
        'learning_activities',
        'assessment_tools',
        'sort_order',
    ];

    public function courseSyllabus(): BelongsTo
    {
        return $this->belongsTo(CourseSyllabus::class);
    }
}
