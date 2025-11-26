<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['academic_year_id','program_id', 'name'];

    /**
     * Get the program that owns the section.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the students for the section.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

     /**
     * Get the courses for the section.
     */
    public function courses()
    {
        // Eloquent assumes the pivot table is named 'course_section' 
        // (alphabetical order of model names).
        // Since your table is named 'course_to_sections', you must specify it 
        return $this->belongsToMany(Course::class, 'course_to_sections');
    }
     /**
     * Get the acadyear for the section.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    // Define hasMany relationship with Enrollment model
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Define hasMany relationship with Enrollment model
    public function coursetosections()
    {
        return $this->hasMany(CourseToSection::class);
    }
}