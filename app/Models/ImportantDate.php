<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ImportantDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date', // updated
        'end_date',   // added
        'user_id',
    ];

    /**
     * Cast attributes to native types.
     */
    protected $casts = [
    'start_date' => 'date',
    'end_date'   => 'date',
];

    /**
     * The categories that belong to the important date.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the user (Admin/Teacher) who created the date.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFormattedDateAttribute()
{
    // If end_date is null or the same as start_date, just show start_date
    if (is_null($this->end_date) || $this->start_date->equalTo($this->end_date)) {
        return $this->start_date->format('M d, Y');
    }

    // Otherwise, show the range
    return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
}
}