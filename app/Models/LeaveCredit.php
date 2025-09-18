<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveCredit extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'sick_leave',
        'vacation_leave',
        'service_incentive_leave',
        'academic_year_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

     /**
     * Get the academic year that owns the semester.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

}
