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
     * Get the sections for the academic year.
     */
    public function sections()
    {
        return $this->hasMany(Section::class);
    }


    /**
     * Get the semesters for the academic year.
     */
    public function leavecredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }

    public static function getActiveAcademicYear()
    {
        // Find the active academic year first
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

       

        return  $activeAcademicYear; // No active academic year found
    }

    // AcademicYear.php
    public function getLabelAttribute() {
        return "{$this->start_year} - {$this->end_year}";
    }
}
