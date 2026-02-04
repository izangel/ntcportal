<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * The important dates that belong to the category.
     */
    public function importantDates(): BelongsToMany
    {
        return $this->belongsToMany(ImportantDate::class);
    }
}