<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',     // The School ID (e.g., 2024-0001)
        'first_name',
        'middle_name',    // ADDED: To ensure middle names can be saved/displayed
        'last_name',
        'email',
        'date_of_birth',
        'section_id',
    ];

    /**
     * Relationship with the User account
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Section (Many-to-Many)
     * This links to your new section_student pivot table
     */
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_student')
                    ->withPivot('academic_year_id', 'semester')
                    ->withTimestamps();
    }

    /**
     * Relationship with Enrollments
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Relationship with Course Evaluations
     */
    public function evaluations()
    {
        return $this->hasMany(CourseEvaluation::class, 'student_id');
    }
}