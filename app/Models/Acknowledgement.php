<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acknowledgement extends Model
{
    // Double-check your spelling: your migration used 'acknowledgments' (no 'e' in the middle)
    protected $table = 'acknowledgments'; 

    // Disables updated_at and created_at if you only use 'acknowledged_at' 
    public $timestamps = false; 

    protected $fillable = [
        'employee_id',
        'advisory_no',
        'acknowledged_at', // Fixed typo to match 'acknowledged_at' in migration
        
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        
    ];

    /**
     * Get the employee that made the acknowledgment.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the memo advisory being acknowledged.
     */
    public function memoAdvisory(): BelongsTo
    {
        // 'advisory_no' is the foreign key on this table.
        // Change 'id' to 'advisory_no' if that is the primary key of your memo_advisories table.
        return $this->belongsTo(MemoAdvisory::class, 'advisory_no', 'id'); 
    }
}