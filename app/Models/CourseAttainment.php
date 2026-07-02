<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAttainment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_session_id', // Links to the ID in your screenshot
        'google_sheet_url',
        'status',
        'remarks',
    ];

    /**
     * Relationship: Get the course session/offering details 
     * associated with this attainment report.
     */
    public function CourseBlock(): BelongsTo
    {
        // Replace 'course_sessions' with the actual name of the table in your screenshot
        return $this->belongsTo(CourseBlock::class, 'course_session_id');
    }

    /**
     * Helper to check if the report is already approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Scope to filter by status for the Academic Head's dashboard.
     */
    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'reviewed']);
    }
}