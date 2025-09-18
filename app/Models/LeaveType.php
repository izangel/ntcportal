<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LeaveType extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'default_credits'];

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }
}
