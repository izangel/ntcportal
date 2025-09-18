<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Database\Seeders\ProgramSeeder;
use Database\Seeders\AcademicYearSeeder; // <-- ADD THIS LINE
use Database\Seeders\SemesterSeeder;     // <-- ADD THIS LINE
use Database\Seeders\StudentSeeder;
use Database\Seeders\CourseSeeder;
use Database\Seeders\EnrollmentSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher User',
                'password' => bcrypt('password'),
                'role' => 'teacher',
            ]
        );
        User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student User',
                'password' => bcrypt('password'),
                'role' => 'student',
            ]
        );

        // Call your custom seeders here
        $this->call([
            ProgramSeeder::class,
            AcademicYearSeeder::class, // <-- ADD THIS LINE (Order matters for SemesterSeeder)
            SemesterSeeder::class,     // <-- ADD THIS LINE
            StudentSeeder::class,
            CourseSeeder::class,
            EnrollmentSeeder::class,
            EmployeeSeeder::class,
            LeaveTypesSeeder::class,
            DatabaseSeeder::class,
             
        ]);
    }
}