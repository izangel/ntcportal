<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyllabusGradingComponent extends Model
{
    protected $fillable = [
        'course_syllabus_id',
        'assessment_type',
        'percentage',
        'sort_order',
    ];

    public function courseSyllabus(): BelongsTo
    {
        return $this->belongsTo(CourseSyllabus::class);
    }
}