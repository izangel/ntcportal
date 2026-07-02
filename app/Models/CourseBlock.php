<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseBlock extends Model
{
    protected $fillable = [
        'section_id',
        'course_id',
        'faculty_id',
        'academic_year_id',
        'semester',
        'room_name',
        'schedule_string',
        'finalized',
    ];

    // Relationships
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function sections()
{
    // Ensure the table name 'course_block_section' matches your DB exactly
    return $this->belongsToMany(Section::class, 'course_block_section', 'course_block_id', 'section_id');
}

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Employee::class, 'faculty_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    protected static function booted()
    {
        static::created(function ($courseBlock) {
            // 1. Find all students already registered in this section
            $registrations = \App\Models\SectionStudent::where('section_id', $courseBlock->section_id)
                ->where('academic_year_id', $courseBlock->academic_year_id)
                ->where('semester', $courseBlock->semester)
                ->get();

            // 2. Enroll them in this specific newly created course block
            foreach ($registrations as $reg) {
                \App\Models\Enrollment::firstOrCreate([
                    'student_id'       => $reg->student_id,
                    'course_id'        => $courseBlock->course_id,
                    'section_id'       => $courseBlock->section_id,
                    'academic_year_id' => $courseBlock->academic_year_id,
                    'semester'         => $courseBlock->semester,
                ]);
            }
        });
    }

    public function attainment(): HasOne
    {
        // 'course_session_id' is the foreign key in your course_attainments table
        return $this->hasOne(CourseAttainment::class, 'course_session_id');
    }
}