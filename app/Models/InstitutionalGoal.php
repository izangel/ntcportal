<?php

// app/Models/InstitutionalGoal.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InstitutionalGoal extends Model
{
    protected $fillable = ['code', 'description', 'is_active'];

    public function peos(): BelongsToMany
    {
        return $this->belongsToMany(Peo::class);
    }
}