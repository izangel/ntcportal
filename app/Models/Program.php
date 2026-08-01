<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get the sections for the program.
     */
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }

    /**
     * Get the PEOs for the program.
     * Adjust 'ProgramEducationalObjective::class' to your actual model name if different (e.g., Peo::class).
     */
    public function programEducationalObjectives(): HasMany
    {
        return $this->hasMany(Peo::class);
    }

    /**
     * Get the PLOs/POs for the program.
     */
    public function programOutcomes(): HasMany
    {
        return $this->hasMany(ProgramOutcome::class);
    }
}