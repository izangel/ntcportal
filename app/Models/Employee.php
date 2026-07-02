<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes; // Enables soft delete framework automation mechanics

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'email',
        'phone',
        'address',
        'role',
        'department_id', 
        'user_id',
    ];

    protected $dates = ['deleted_at'];

    // /**
    //  * Get the user associated with the Employee.
    //  * An employee can have one user account.
    //  */
    // public function user()
    // {
    //     return $this->hasOne(User::class);
    // }

     public function user()
    {
        // This assumes 'user_id' is the foreign key on the 'employees' table
        return $this->belongsTo(User::class); // <-- CORRECTED THIS LINE
    }


    /**
     * 
     * 
     * Get the leave applications for the employee.
     */
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    /**
     * Get the department that the employee belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class); // <-- ADD THIS METHOD
    }


   public function getRemainingLeaveCredits($academicYearId = null)
    {
        // If no explicit ID is passed, fall back to finding the configured active year
        if (!$academicYearId) {
            $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
            $academicYearId = $activeYear ? $activeYear->id : null;
        }

        // If there is still no academic year configuration found, return an empty array
        if (!$academicYearId) {
            return [];
        }

        // 1. Fetch the specific leave credit allocation tied to this academic year
        // Adjust 'academic_year_id' if your LeaveCredit table uses a different column name (e.g., 'school_year_id')
        $leavecredit = $this->leaveCredits()
            ->where('academic_year_id', $academicYearId)
            ->first(); 

        if (!$leavecredit) {
            return []; 
        }
        
        $remainingCredits = [];
        $leaveTypes = LeaveType::all();

        foreach ($leaveTypes as $leaveType) {
            $key = strtolower(str_replace(' ', '_', $leaveType->name));
            
            // 2. Only sum applications filed *within* this specific academic year
            $taken = $this->leaveApplications()
                ->where('school_year_id', $academicYearId) // Scopes to the active year
                ->where('leave_type_id', $leaveType->id)
                ->where('approval_status', 'approved_with_pay')
                ->sum('total_days');

            // Prevent negative balances if data gets messy
            $remaining = $leavecredit->{$key} - $taken;
            $remainingCredits[$key] = max(0, $remaining);
        }

        return $remainingCredits;
    }


    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }

    public function courses(): BelongsToMany
    {
        
        return $this->belongsToMany(
            Course::class,
            'faculty_loadings', // Pivot table name (assuming 'course_loading' from your image)
            'faculty_id',     // Foreign key on the pivot table pointing to the User/Faculty ID
            'course_id'       // Foreign key on the pivot table pointing to the Course ID
        )->withPivot('academic_year_id', 'semester', 'section_id', 'room', 'schedule');
    }

    public function loadings()
    {
        // This links the faculty user to all their loading records
        return $this->hasMany(FacultyLoading::class, 'faculty_id');
    }


    public function courseBlocks()
    {
        return $this->hasMany(CourseBlock::class, 'faculty_id');
    }

    public function receivedEvaluations()
{
    return $this->hasMany(Evaluation::class, 'teacher_id');
}

/**
 * Evaluations this user has performed on others (as a Peer or Supervisor)
 */
public function performedEvaluations()
{
    return $this->hasMany(Evaluation::class, 'evaluator_id');
}

public function CourseBlock() {
    return $this->belongsTo(CourseBlock::class, 'course_session_id');
}

}