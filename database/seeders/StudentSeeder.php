<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User; // Don't forget to import User

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a specific Student User for testing
        $user = User::firstOrCreate([
            'email' => 'student@example.com',
        ], [
            'name' => 'Test Student',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a student linked to the 'student' user
        Student::firstOrCreate([
            'user_id' => $user->id,
            'student_id' => 'S001',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice.smith@example.com',
            'date_of_birth' => '2000-01-15',
        ]);

        Student::firstOrCreate([
            'user_id' => null, // This student is not linked to a user yet
            'student_id' => 'S002',
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'email' => 'bob.johnson@example.com',
            'date_of_birth' => '1999-05-20',
        ]);

        Student::firstOrCreate([
            'user_id' => null, // This student is not linked to a user yet
            'student_id' => 'S003',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'email' => 'charlie.brown@example.com',
            'date_of_birth' => '2001-11-01',
        ]);
    }
}
