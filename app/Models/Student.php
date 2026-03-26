<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        // Existing fields
        'user_id',
        'student_id',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'date_of_birth',
        'section_id',

        // New Student Personal Data
        'gender',
        'civil_status',
        'card_number',
        'place_birth',
        'current_address',
        'nationality',
        'religion',
        'mobile_number',
        'profile_photo',

        // Parents Data
        'father_name',
        'father_occupation',
        'mother_name',
        'mother_occupation',
        'parent_address',
        'parent_tel',

        // Guardian & Admission Data
        'guardian_name',
        'guardian_address',
        'guardian_tel',
        'basis_of_admission',
        'date_of_admission',
        'encoded_by',
        'last_updated_by',
    ];

    // --- Relationships ---

    /**
     * Relationship for Educational Attainment (Last graduated info).
     * Fixes: RelationNotFoundException [education]
     */
    public function education()
    {
        return $this->hasMany(StudentEducation::class);
    }

    /**
     * Relationship for many document photo uploads.
     * Fixes: RelationNotFoundException [documents]
     */
    public function documents()
    {
        return $this->hasMany(StudentDocument::class);
    }

    /**
     * Relationship to track who encoded the record.
     */
    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    /**
     * Relationship to track who last updated the record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
                    ->withPivot('grade', 'created_at')
                    ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_student')
                    ->withPivot('academic_year_id', 'semester')
                    ->withTimestamps();
    }

    public function evaluations()
    {
        return $this->hasMany(CourseEvaluation::class, 'student_id');
    }

    public function candidacies()
    {
        return $this->hasMany(Candidacy::class);
    }

    // --- Accessors & Logic ---

    public function program()
    {
        $section = $this->sections()->latest('pivot_created_at')->first();
        return $section ? $section->program : null;
    }

    public function getProgramAttribute()
    {
        return $this->program();
    }

    public function isNewStudentForSemester(Semester $currentSemester)
    {
        $previousEnrollments = $this->enrollments()
                                    ->whereHas('semester', function ($query) use ($currentSemester) {
                                        $query->where('end_date', '<', $currentSemester->start_date);
                                    })
                                    ->count();

        return $previousEnrollments === 0;
    }
}
