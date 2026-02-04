<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PeerAssignment extends Model
{
    protected $fillable = ['teacher_id', 'peer_id', 'academic_year_id', 'assignment_type','semester', 'is_completed', 'completed_at'];

    public function teacher() {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }

    public function peer() {
        return $this->belongsTo(Employee::class, 'peer_id');
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Scope a query to only include peer assignments.
     */
    public function scopePeer(Builder $query): Builder
    {
        return $query->where('assignment_type', 'peer');
    }

    /**
     * Scope a query to only include supervisor assignments.
     */
    public function scopeSupervisor(Builder $query): Builder
    {
        return $query->where('assignment_type', 'supervisor');
    }

    /**
     * Scope a query to only include pending assignments.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_completed', false);
    }
    
}