<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemoAdvisory extends Model
{
    /**
     * Explicitly specify the table name if it does not strictly 
     * match the snake_case plural form of the model name.
     */
    protected $table = 'memo_advisories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'advisory_no',
        'to',
        'specific_personnel', // Added to capture individual employee choices
        'from',
        'date',
        'subject',
        'body',
    ];

    /**
     * The attributes that should be cast to native types.
     * Casting 'to' and 'specific_personnel' to array handles the JSON mapping automatically.
     */
    protected $casts = [
        'date'               => 'date',
        'to'                 => 'array', // Automatically turns JSON string to PHP array
        'specific_personnel' => 'array', // Automatically turns JSON string to PHP array
    ];

   public function acknowledgements()
{
    return $this->hasMany(Acknowledgement::class, 'advisory_no', 'id');
}

public function canBeViewedBy($user)
{
    // Admin, HR and Academic Head can always view all advisories
    if (
        $user->hasRole('admin') ||
        $user->hasRole('hr') ||
        $user->hasRole('academic_head')
    ) {
        return true;
    }

    $employee = $user->employee;

    if (!$employee) {
        return false;
    }

    $targets = $this->to ?? [];

    // Specific Personnel
    if (
        in_array('specific_personnel', $targets) &&
        !empty($this->specific_personnel) &&
        in_array($employee->id, $this->specific_personnel)
    ) {
        return true;
    }

    // All Staff
    if (
        in_array('all_staff', $targets) &&
        $employee->role === 'staff'
    ) {
        return true;
    }

    // Admin Personnel
    if (
        in_array('admin_personnel', $targets) &&
        $employee->role === 'admin_personnel'
    ) {
        return true;
    }

    // SHS Faculty
    if (
        in_array('all_shs_faculty', $targets) &&
        $employee->role === 'teacher' &&
        $employee->faculty_type === 'SHS'
    ) {
        return true;
    }

    // College Faculty
    if (
        in_array('all_college_faculty', $targets) &&
        $employee->role === 'teacher' &&
        $employee->faculty_type === 'College'
    ) {
        return true;
    }

    return false;
}
}