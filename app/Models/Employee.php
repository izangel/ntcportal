<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    // Fetch the single LeaveCredit model for the employee.
    // The `first()` method ensures you get a single model, not a collection.
    $leavecredit = $this->leaveCredits()->first(); 

    

    if (!$leavecredit) {
        return 'No leave credits found for this employee. Please contact HR.'; 
    }
    
    $remainingCredits = [];
    $leaveTypes = LeaveType::all();

    foreach ($leaveTypes as $leaveType) {
        $taken = $this->leaveApplications()
            ->where('leave_type_id', $leaveType->id)
            ->where('approval_status', 'approved_with_pay')
            ->sum('total_days');

        // Dynamically create the key based on the leave type name
        $key = strtolower(str_replace(' ', '_', $leaveType->name));
        
        // This line now works because $leavecredit is a single model instance.
        $remainingCredits[$key] = $leavecredit->{$key} - $taken;
    }

    
    return $remainingCredits;
}

    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }

}