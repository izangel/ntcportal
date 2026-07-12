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

    /**
     * Determine if this program is SHS or College.
     */
    public function getProgramTypeAttribute(): string
    {
        return str_starts_with($this->name, 'SHS-') ? 'shs' : 'college';
    }
}