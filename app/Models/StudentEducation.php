<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEducation extends Model
{
    // This line is CRITICAL to fix your error
    protected $table = 'student_educations';

    protected $fillable = [
        'student_id',
        'education_group',
        'level',
        'school_name',
        'inclusive_dates',
        'date_entered',
        'date_graduated',
        'honors_awards',
        'course_major',
        'so_number',
        'thesis',
        'year_graduated',
    ];

    protected $casts = [
        'date_entered' => 'date',
        'date_graduated' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
