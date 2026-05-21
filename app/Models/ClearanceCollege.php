<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClearanceCollege extends Model
{
    protected $table = 'clearance_college';
    protected $fillable = [
        'student_id',
        'registrar_status', 'registrar_approved_by', 'registrar_approved_at', 'registrar_remarks',
        'guidance_status', 'guidance_approved_by', 'guidance_approved_at', 'guidance_remarks',
        'sao_status', 'sao_approved_by', 'sao_approved_at', 'sao_remarks',
        'lab_status', 'lab_approved_by', 'lab_approved_at', 'lab_remarks',
        'ssc_status', 'ssc_approved_by', 'ssc_approved_at', 'ssc_remarks',
        'librarian_status', 'librarian_approved_by', 'librarian_approved_at', 'librarian_remarks',
        'pod_status', 'pod_approved_by', 'pod_approved_at', 'pod_remarks',
        'dsas_status', 'dsas_approved_by', 'dsas_approved_at', 'dsas_remarks',
        'ah_status', 'ah_approved_by', 'ah_approved_at', 'ah_remarks',
        'admin_status', 'admin_approved_by', 'admin_approved_at', 'admin_remarks',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
