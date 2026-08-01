<?php

// app/Models/Peo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Peo extends Model
{
    protected $fillable = ['program_id', 'code', 'description', 'effective_batch_year'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function programOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(ProgramOutcome::class);
    }

    public function institutionalGoals(): BelongsToMany
    {
        return $this->belongsToMany(InstitutionalGoal::class);
    }
}