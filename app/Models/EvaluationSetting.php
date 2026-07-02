<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year',
        'semester',
        'is_active',
        'blocks_verified',
        'period_verified',
        'students_verified',
        'verified_at',
        'loading_verified',   // <--- ADD THIS
    'evaluations_opened', // <--- ADD THIS
     'shs_blocks_verified',   // <--- ADD THIS
    'college_blocks_verified', // <--- ADD THIS
    'shs_loading_verified',
    'college_loading_verified',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'blocks_verified' => 'boolean',
        'verified_at' => 'datetime',
        'students_verified' => 'boolean',
    ];

    // --- SCOPES ---

    /**
     * Scope a query to only include the currently active evaluation cycle.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // --- LOGIC HELPERS ---

    /**
     * Check if the Registrar is allowed to perform Subject Loading.
     * Logic: Must be an active cycle AND blocks must be verified by Program Head.
     */
    public static function canLoadSubjects(): bool
    {
        $current = self::where('is_active', true)->first();
        
        return $current ? $current->blocks_verified : false;
    }

    /**
     * Check if the Registrar can register students.
     * Logic: As long as an active academic year/semester is set, enrollment is open.
     */
    public static function isEnrollmentOpen(): bool
    {
        return self::where('is_active', true)->exists();
    }

    /**
     * Get the current active cycle details.
     */
    public static function current()
    {
        return self::where('is_active', true)->first();
    }

    public static function isPeriodConfirmed(): bool
{
    $current = self::where('is_active', true)->first();
    return $current ? $current->period_verified : false;
}
}