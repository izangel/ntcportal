<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'course_code',
        'course_title',
        'faculty',
        'room',
        'schedule',
    ];

    // Relationships
    public function section()
    {
        return $this->belongsTo(Section::class);
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
            // Eager load relationships for efficiency
            $courseBlock->load(['course', 'faculty']);

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

                // 3. Also create a record in the student_courseblock table
                \App\Models\StudentCourseblock::firstOrCreate(
                    [
                        // Keys to find existing record
                        'student_id' => $reg->student_id,
                        'course_code' => $courseBlock->course->code,
                    ],
                    [
                        // Values to create if not found
                        'course_title' => $courseBlock->course->name,
                        'faculty' => $courseBlock->faculty ? ($courseBlock->faculty->last_name . ', ' . $courseBlock->faculty->first_name) : 'TBA',
                        'rooms' => $courseBlock->room_name,
                        'schedule' => $courseBlock->schedule_string,
                        'status' => 'Enrolled',
                    ]
                );
            }
        });
    }
}
