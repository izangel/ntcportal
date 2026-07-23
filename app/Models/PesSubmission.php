<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesSubmission extends Model
{
    protected $table = 'pes_submissions';

    // Ensure these exact database column fields are mass-assignable
    protected $fillable = [
        'employee_id',
        'academic_year_id',
        'semester',
        'is_submitted',
        'submitted_at',
        'actioned_by_user_id'
    ];
    
    protected $casts = [
        'is_submitted' => 'boolean',
        'submitted_at' => 'datetime',
    ];
}