<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseMaterial extends Model
{
    use HasFactory;

    public const TYPE_LMS = 'lms';
    public const TYPE_COURSE_PACK = 'course_pack';
    public const TYPE_SYLLABUS = 'syllabus';

    public const TYPES = [
        self::TYPE_LMS,
        self::TYPE_COURSE_PACK,
        self::TYPE_SYLLABUS,
    ];

    protected $fillable = [
        'course_block_id',
        'type',
        'title',
        'url',
        'description',
    ];

    public function courseBlock(): BelongsTo
    {
        return $this->belongsTo(CourseBlock::class);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_LMS => 'LMS Link',
            self::TYPE_COURSE_PACK => 'Course Pack',
            self::TYPE_SYLLABUS => 'Syllabus',
            default => 'Other',
        };
    }

    public static function typeIcon(string $type): string
    {
        return match ($type) {
            self::TYPE_LMS => 'fa-graduation-cap',
            self::TYPE_COURSE_PACK => 'fa-folder-open',
            self::TYPE_SYLLABUS => 'fa-file-lines',
            default => 'fa-link',
        };
    }
}
