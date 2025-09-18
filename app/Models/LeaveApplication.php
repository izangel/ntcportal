<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Add this import
use Illuminate\Database\Eloquent\Relations\HasMany;   // Add this import

class LeaveApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'reason',
        'start_date',
        'end_date',
        'total_days',
        'date_filed',
        'academic_head_noted_at',
        'hr_recommended_at',
        'administrator_approved_at',
        'approval_status',
        'comments',
        
        'acknowledgement_subject_teacher',
        'tasks_endorsed',
        'status', 
        'personnel_to_take_over_id',
        'acknowledgement_personnel_take_over_signature', 

        'ah_status',        // NEW: Academic Head specific status
        'ah_approved_at',   // NEW: AH approval timestamp
        'ah_approved_by',   // NEW: AH approver employee ID
        'ah_remarks',       // NEW: AH remarks


        'hr_status',        // NEW
        'hr_approved_at',   // NEW
        'hr_approved_by',   // NEW
        'hr_remarks',       // NEW

        'admin_status',        // NEW: Admin specific status
        'admin_approved_at',   // NEW: Admin approval timestamp
        'admin_approved_by',   // NEW: Admin approver employee ID
        'admin_remarks',       // NEW: Admin remarks

    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'date_filed' => 'datetime',
        'academic_head_noted_at' => 'datetime',
        'hr_recommended_at' => 'datetime',
        'administrator_approved_at' => 'datetime',
        
        //'leave_type' => 'string', // Ensure enum is treated as string
        'approval_status' => 'string', // Ensure enum is treated as string

         'ah_approved_at' => 'datetime', // NEW

        'hr_approved_at' => 'datetime',
        'admin_approved_at' => 'datetime', // NEW
    ];

    /**
     * Get the employee that owns the leave application.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the classes associated with the leave application.
     */
    public function classesToMiss() // Changed method name for clarity
    {
        
        return $this->hasMany(LeaveApplicationClass::class);
      
    }

    // Helper to check if the employee is a teacher
    public function isTeacher()
    {
        // Define an array of roles that are considered "teacher" roles.
        $teacherRoles = ['teacher', 'academic_head', 'hr', 'admin', 'registrar'];

        // Check if the employee's role is in the defined array.
        return in_array($this->employee->role, $teacherRoles);
    }

    // Helper to check if the employee is staff
    public function isStaff()
    {
        return $this->employee->role === 'staff';
    }


    public function substituteTeacher()
    {
        // The second argument specifies the foreign key, which is your substitute_teacher_id
        return $this->belongsTo(Employee::class);
    }


     // NEW: Relationship for the personnel taking over
    public function personnelToTakeOver()
    {
        return $this->belongsTo(Employee::class, 'personnel_to_take_over_id');
    }

    public function ahApprover() // NEW Relationship: Employee who approved (Academic Head)
    {
        return $this->belongsTo(Employee::class, 'ah_approved_by');
    }

    public function hrApprover() // NEW Relationship
    {
        return $this->belongsTo(Employee::class, 'hr_approved_by');
    }

    public function adminApprover() // NEW Relationship: Employee who approved (Admin)
    {
        return $this->belongsTo(Employee::class, 'admin_approved_by');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

}