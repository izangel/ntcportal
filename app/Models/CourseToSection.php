<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseToSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'semester',
        'course_id',
        'section_id',
        
    ];

    public function course() { return $this->belongsTo(Course::class); }
    public function section() { return $this->belongsTo(Section::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    
   
}