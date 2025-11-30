<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_name',
        'first_name',
        'mid_name',
        'name',
        'email',
        'phone',
        'address',
        'role',
        'department_id', 
        'user_id',
        'employee_id',
    ];

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


   public function getRemainingLeaveCredits()
{
    $leavecredit = $this->leaveCredits()->first(); 

    // If no leave credits, return an empty array
    if (!$leavecredit) {
        return []; 
    }
    
    $remainingCredits = [];
    $leaveTypes = LeaveType::all();

    foreach ($leaveTypes as $leaveType) {
        $taken = $this->leaveApplications()
            ->where('leave_type_id', $leaveType->id)
            ->where('approval_status', 'approved_with_pay')
            ->sum('total_days');

        $key = strtolower(str_replace(' ', '_', $leaveType->name));

        $remainingCredits[$key] = $leavecredit->{$key} - $taken;
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

}