<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['program_id', 'name'];

    /**
     * Get the program that owns the section.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the students for the section.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}