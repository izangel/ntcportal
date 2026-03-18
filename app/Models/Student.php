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
        'middle_name',
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
     * Get the section that the student directly belongs to.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
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
     * Relationship with Section (Many-to-Many)
     * This links to your new section_student pivot table
     */
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_student')
                    ->withPivot('academic_year_id', 'semester')
                    ->withTimestamps();
    }


   
public function courseBlocks() {
    return $this->belongsToMany(CourseBlock::class, 'student_courseblock', 'student_id', 'course_block_id');
}



    /**
     * Get the program that the student belongs to (through section).
     */
    public function program()
    {
        $section = $this->sections()->latest('pivot_created_at')->first();
        return $section ? $section->program : null;
    }

    /**
     * Get the program attribute (accessor for blade templates).
     */
    public function getProgramAttribute()
    {
        return $this->program();
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

    public function evaluations()
    {
        return $this->hasMany(CourseEvaluation::class, 'student_id');
    }

    /**
     * Get the candidacies for the student.
     */
    public function candidacies()
    {
        return $this->hasMany(Candidacy::class);
    }

    /**
     * Get the election votes submitted by the student.
     */
    public function electionVotes()
    {
        return $this->hasMany(ElectionVote::class);
    }
}