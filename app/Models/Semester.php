<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the academic year that owns the semester.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the enrollments for the semester.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the currently active semester.
     * Assumes only one semester can be active per academic year,
     * and only one academic year is active at a time.
     */
    public static function getActiveSemester()
    {
        // Find the active academic year first
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        if ($activeAcademicYear) {
            // Then find the active semester within that academic year
            return self::where('academic_year_id', $activeAcademicYear->id)
                       ->where('is_active', true)
                       ->first();
        }

        return null; // No active academic year found
    }
}