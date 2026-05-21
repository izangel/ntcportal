<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Role;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacherRoleId = Role::where('type', 'teacher')->value('id');
        $staffRoleId = Role::where('type', 'staff')->value('id');
        $academicHeadRoleId = Role::where('type', 'academic_head')->value('id');
        $hrRoleId = Role::where('type', 'hr')->value('id');
        $adminRoleId = Role::where('type', 'admin')->value('id');

        Employee::create([
            'last_name' => 'Doe',
            'first_name' => 'John',
            'email' => 'john.doe@example.com',
            'phone' => '09123456789',
            'address' => '123 Main St, Anytown',
            'roles' => $teacherRoleId,
        ]);

        Employee::create([
            'last_name' => 'Smith',
            'first_name' => 'Jane',
            'email' => 'jane.smith@example.com',
            'phone' => '09987654321',
            'address' => '456 Oak Ave, Anytown',
            'roles' => $staffRoleId,
        ]);

        Employee::create([
            'last_name' => 'Brown',
            'first_name' => 'Alice',
            'email' => 'alice.brown@example.com',
            'phone' => '09001112222',
            'address' => '789 Pine Rd, Anytown',
            'roles' => $academicHeadRoleId,
        ]);

        Employee::create([
            'last_name' => 'Davis',
            'first_name' => 'Robert',
            'email' => 'robert.davis@example.com',
            'phone' => '09334445555',
            'address' => '101 Cedar Ln, Anytown',
            'roles' => $hrRoleId,
        ]);

        Employee::create([
            'last_name' => 'White',
            'first_name' => 'Emily',
            'email' => 'emily.white@example.com',
            'phone' => '09667778888',
            'address' => '202 Birch Blvd, Anytown',
            'roles' => $adminRoleId,
        ]);
    }
}