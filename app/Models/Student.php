<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'first_name',
        'last_name',
        'email',
        'date_of_birth',
        'section_id',
    ];

    // Define relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define many-to-many relationship with Course through Enrollment
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
                    ->withPivot('grade', 'created_at') // Include grade and created_at from pivot table
                    ->withTimestamps(); // If you want to automatically manage timestamps on the pivot
    }

    // Define hasMany relationship with Enrollment model
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the section that the student belongs to.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the program that the student belongs to (through section).
     */
    public function program()
    {
        return $this->hasOneThrough(Program::class, Section::class);
    }

    /**
     * Determine if the student is 'new' for a given semester.
     * A student is 'new' if they have no enrollments in semesters that ended before
     * the start date of the given semester.
     */
    public function isNewStudentForSemester(Semester $currentSemester)
    {
        // Get enrollments that occurred in semesters that ended *before* the current semester's start date.
        $previousEnrollments = $this->enrollments()
                                    ->whereHas('semester', function ($query) use ($currentSemester) {
                                        // Ensure the semester has an end_date before the current semester's start_date
                                        $query->where('end_date', '<', $currentSemester->start_date);
                                    })
                                    ->count();

        return $previousEnrollments === 0; // If count is 0, they are new.
    }
}