<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUpdate extends Model
{
    protected $fillable = [
        'version_number',
        'category',
        'title',
        'release_date',
        'description',
    ];
}
