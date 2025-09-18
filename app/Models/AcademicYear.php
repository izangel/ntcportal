<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_year',
        'end_year',
        'is_active',
    ];

    /**
     * Get the semesters for the academic year.
     */
    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    /**
     * Get the semesters for the academic year.
     */
    public function leavecredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }
}
