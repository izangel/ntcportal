<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClearanceShs extends Model
{
    protected $table = 'clearance_shs';
    protected $fillable = [
        'student_id',
        'registrar_status', 'registrar_approved_by', 'registrar_approved_at', 'registrar_remarks',
        'guidance_status', 'guidance_approved_by', 'guidance_approved_at', 'guidance_remarks',
        'adviser_status', 'adviser_approved_by', 'adviser_approved_at', 'adviser_remarks',
        'sao_status', 'sao_approved_by', 'sao_approved_at', 'sao_remarks',
        'lab_status', 'lab_approved_by', 'lab_approved_at', 'lab_remarks',
        'org_status', 'org_approved_by', 'org_approved_at', 'org_remarks',
        'ssg_status', 'ssg_approved_by', 'ssg_approved_at', 'ssg_remarks',
        'librarian_status', 'librarian_approved_by', 'librarian_approved_at', 'librarian_remarks',
        'pod_status', 'pod_approved_by', 'pod_approved_at', 'pod_remarks',
        'coordinator_status', 'coordinator_approved_by', 'coordinator_approved_at', 'coordinator_remarks',
        'dsas_status', 'dsas_approved_by', 'dsas_approved_at', 'dsas_remarks',
        'ah_status', 'ah_approved_by', 'ah_approved_at', 'ah_remarks',
        'admin_status', 'admin_approved_by', 'admin_approved_at', 'admin_remarks',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
