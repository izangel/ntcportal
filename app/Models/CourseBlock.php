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
    ];

    // Relationships
public function course() {
    return $this->belongsTo(Course::class);
}

public function section() {
    return $this->belongsTo(Section::class);
}

public function faculty() {
    return $this->belongsTo(Employee::class, 'faculty_id');
}

public function academicYear() {
    return $this->belongsTo(AcademicYear::class, 'academic_year_id');
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
}