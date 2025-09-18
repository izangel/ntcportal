<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}