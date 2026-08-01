<?php

// app/Models/ProgramOutcome.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProgramOutcome extends Model
{
    protected $fillable = ['program_id', 'code', 'description', 'effective_batch_year'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function peos(): BelongsToMany
    {
        return $this->belongsToMany(Peo::class);
    }
    public function courseLearningOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(CourseLearningOutcome::class, 'clo_program_outcome')
                    ->withPivot('level')
                    ->withTimestamps();
    }
}