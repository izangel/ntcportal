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
        // Get the first user, or create one if none exists
        $user = User::first();
        if (!$user) {
            $user = User::factory()->firstOrCreate([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'role' => 'student', // Assign a default role
            ]);
        }

        // Create a student linked to the 'student' user
        Student::firstOrCreate([
            'user_id' => User::where('email', 'student@example.com')->first()->id ?? null, // Link to the 'student' user created by DatabaseSeeder
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