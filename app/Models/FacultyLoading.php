<?php

// In app/Models/FacultyLoading.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyLoading extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'semester',
        'course_id',
        'faculty_id',
        'section_id',
        'room',
        'schedule',
    ];

    public function course() { return $this->belongsTo(Course::class); }
    public function faculty() { return $this->belongsTo(Employee::class, 'faculty_id'); } // Assuming User model for faculty
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function section() { return $this->belongsTo(Section::class); }

   
}