<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\LeaveCredit;

class LeaveCreditSeeder extends Seeder
{
    public function run()
    {
        
        $employees = Employee::all();

        foreach ($employees as $employee) {
            LeaveCredit::updateOrCreate(
                ['employee_id' => $employee->id, 'academic_year' => '2025-2026'],
                ['sick_leave' => 15, 'vacation_leave' => 15, 'service_incentive_leave' => 15]
            );
        }
    }
}
