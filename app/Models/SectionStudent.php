<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// These imports are required for the automated enrollment logic
use App\Models\CourseBlock;
use App\Models\Enrollment;
use App\Models\Student;

class SectionStudent extends Model
{
    protected $table = 'section_student';
    protected $fillable = ['student_id', 'section_id', 'academic_year_id', 'semester'];

    /**
     * This relationship is REQUIRED for the "Assign Students" table to load.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    protected static function booted()
    {
        // Whenever a new record is created in this table (a student is assigned to a section)...
        static::created(function ($membership) {
            
            // 1. Find all subjects (CourseBlocks) assigned to this section for this specific term
            $blocks = CourseBlock::where('section_id', $membership->section_id)
                ->where('academic_year_id', $membership->academic_year_id)
                ->where('semester', $membership->semester)
                ->get();

            // 2. Automatically enroll the student into every subject in that section
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