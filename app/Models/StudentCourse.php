<?php

// app/Models/StudentCourse.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourse extends Model
{
    use HasFactory;

    protected $table = 'student_course';
    
    // Use fillable for bulk assignment
    protected $fillable = [
        'student_id',
        'course_id',
        'section_id',
        'academic_year_id',
        'semester',
        'validated',
        'validated_by',
    ];

    protected $casts = [
        'validated' => 'boolean',
    ];
    
    // Relationships (optional but highly recommended for Eloquent)
    public function student() { return $this->belongsTo(Student::class,); }
     public function section() { return $this->belongsTo(Section::class); }
      public function acadYear() { return $this->belongsTo(AcademicYear::class, 'academic_year_id'); }
       public function validatedby() { return $this->belongsTo(User::class, 'validated_by'); }
    public function course() { return $this->belongsTo(Course::class); }
    // ... other relationships (section, academicYear, validatorUser)
}