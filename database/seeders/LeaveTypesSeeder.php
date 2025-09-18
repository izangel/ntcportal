<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         LeaveType::create([
            'name' => 'Sick Leave',
            'default_credits' => 15,
        ]);


         LeaveType::create([
            'name' => 'Service Incentive Leave',
            'default_credits' => 15,
        ]);

        LeaveType::create([
            'name' => 'Vacation Leave',
            'default_credits' => 15,
        ]);
    }
}
