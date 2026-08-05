<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CourseBlock;
use App\Models\Enrollment;
use App\Models\Student;

class SectionStudent extends Model
{
    protected $table = 'section_student';
    protected $fillable = ['student_id', 'section_id', 'academic_year_id', 'semester'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    protected static function booted()
    {
        static::created(function ($membership) {
            
            $blocks = CourseBlock::whereHas('sections', function ($q) use ($membership) {
                $q->where('sections.id', $membership->section_id)
                  ->where('course_block_section.academic_year_id', $membership->academic_year_id)
                  ->where('course_block_section.semester', $membership->semester);
            })->get();
        }); 
    }
}