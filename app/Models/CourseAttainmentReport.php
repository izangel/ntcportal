<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAttainmentReport extends Model
{
    protected $fillable = [
        'course_block_id',
        'status',
        'action_plans',
        'submitted_at',
    ];

    protected $casts = [
        'action_plans' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function courseBlock(): BelongsTo
    {
        return $this->belongsTo(CourseBlock::class);
    }
}
