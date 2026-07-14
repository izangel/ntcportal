<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'program_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
