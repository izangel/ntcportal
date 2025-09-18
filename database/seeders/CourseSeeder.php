<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::firstOrCreate([
            'name' => 'Introduction to Computer Science',
            'code' => 'CS101',
            'description' => 'A foundational course covering basic computer science concepts.',
        ]);

        Course::firstOrCreate([
            'name' => 'Calculus I',
            'code' => 'MA101',
            'description' => 'First course in the calculus sequence, covering limits, derivatives, and integrals.',
        ]);

        Course::firstOrCreate([
            'name' => 'Principles of Marketing',
            'code' => 'MK201',
            'description' => 'An introduction to the fundamental principles and practices of marketing.',
        ]);
    }
}