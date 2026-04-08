<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectionVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'candidacy_id',
        'voted_at',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    /**
     * Get the student that cast the vote.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the candidacy entry that received the vote.
     */
    public function candidacy()
    {
        return $this->belongsTo(Candidacy::class);
    }
}
