<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee; // Import the Employee model

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::create([
            'last_name' => 'Doe',
            'first_name' => 'John',
            'email' => 'john.doe@example.com',
            'phone' => '09123456789',
            'address' => '123 Main St, Anytown',
            'role' => 'teacher',
        ]);

        Employee::create([
            'last_name' => 'Smith',
            'first_name' => 'Jane',
            'email' => 'jane.smith@example.com',
            'phone' => '09987654321',
            'address' => '456 Oak Ave, Anytown',
            'role' => 'staff',
        ]);

        Employee::create([
            'last_name' => 'Brown',
            'first_name' => 'Alice',
            'email' => 'alice.brown@example.com',
            'phone' => '09001112222',
            'address' => '789 Pine Rd, Anytown',
            'role' => 'academic_head',
        ]);

        Employee::create([
            'last_name' => 'Davis',
            'first_name' => 'Robert',
            'email' => 'robert.davis@example.com',
            'phone' => '09334445555',
            'address' => '101 Cedar Ln, Anytown',
            'role' => 'hr',
        ]);

        Employee::create([
            'last_name' => 'White',
            'first_name' => 'Emily',
            'email' => 'emily.white@example.com',
            'phone' => '09667778888',
            'address' => '202 Birch Blvd, Anytown',
            'role' => 'admin',
        ]);
    }
}