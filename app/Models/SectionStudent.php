<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionStudent extends Model
{
    protected $table = 'section_student';
    protected $fillable = ['student_id', 'section_id', 'academic_year_id', 'semester'];

    protected static function booted()
    {
        // Whenever a new record is created in this table...
        static::created(function ($membership) {
            // 1. Find all course blocks assigned to this section for this term
            $blocks = CourseBlock::where('section_id', $membership->section_id)
                ->where('academic_year_id', $membership->academic_year_id)
                ->where('semester', $membership->semester)
                ->get();

            // 2. Loop through and create enrollment records
            foreach ($blocks as $block) {
                Enrollment::create([
                    'student_id'       => $membership->student_id,
                    'course_id'        => $block->course_id,
                    'section_id'       => $membership->section_id,
                    'academic_year_id' => $membership->academic_year_id,
                    'semester'         => $membership->semester,
                ]);
            }
        });
    }
}