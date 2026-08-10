<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUpdate extends Model
{
    protected $fillable = [
        'category',
        'title',
        'description',
        'release_date',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public const CATEGORIES = [
        'New Feature',
        'Bug Fix',
        'Improvement',
    ];
}