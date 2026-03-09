<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CourseBlock;
use App\Models\Enrollment;
use App\Models\StudentCourseblock;

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
                ->with(['course', 'faculty']) // Eager load relationships
                ->get();

            // 2. Loop through and create enrollment records
            foreach ($blocks as $block) {
                // Use firstOrCreate to prevent duplicates
                Enrollment::firstOrCreate([
                    'student_id'       => $membership->student_id,
                    'course_id'        => $block->course_id,
                    'section_id'       => $membership->section_id,
                    'academic_year_id' => $membership->academic_year_id,
                    'semester'         => $membership->semester,
                ]);

                // 3. Also create a record in the student_courseblock table
                StudentCourseblock::firstOrCreate(
                    [
                        // Keys to find existing record
                        'student_id' => $membership->student_id,
                        'course_code' => $block->course->code,
                    ],
                    [
                        // Values to create if not found
                        'course_title' => $block->course->name,
                        'faculty' => $block->faculty ? ($block->faculty->last_name . ', ' . $block->faculty->first_name) : 'TBA',
                        'rooms' => $block->room_name,
                        'schedule' => $block->schedule_string,
                        'status' => 'Enrolled',
                    ]
                );
            }
        });
    }
}
