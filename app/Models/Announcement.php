<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'category',
        'is_pinned',
    ];

    // Define the categories as a constant for easy access
    public static $categories = [
        'OSA', 
        'ACADEMIC AFFAIRS', 
        'SHS', 
        'COLLEGE', 
        'ADMIN', 
        'HR'
    ];

    // Inside app/Models/Announcement.php

    public function getCategoryColorAttribute()
    {
        return match($this->category) {
            'OSA' => 'bg-blue-100 text-blue-800 border-blue-200',
            'ACADEMIC AFFAIRS' => 'bg-purple-100 text-purple-800 border-purple-200',
            'SHS' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'COLLEGE' => 'bg-pink-100 text-pink-800 border-pink-200',
            'ADMIN' => 'bg-gray-100 text-gray-800 border-gray-200',
            'HR' => 'bg-green-100 text-green-800 border-green-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_pinned' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user (Admin/Teacher) that authored the announcement.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include pinned announcements first.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('is_pinned', 'desc')->latest();
    }
}