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
        'classroom_policies',
    ];

    public function courseBlock(): BelongsTo
    {
        return $this->belongsTo(CourseBlock::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function learningPlanItems(): HasMany
    {
        return $this->hasMany(SyllabusLearningPlanItem::class)
            ->orderBy('sort_order');
    }
}
