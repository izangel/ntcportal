<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample students and courses (ensure they exist from previous seeders)
        $student1 = Student::where('student_id', 'S001')->first();
        $student2 = Student::where('student_id', 'S002')->first();
        $course1 = Course::where('code', 'CS101')->first();
        $course2 = Course::where('code', 'MA101')->first();
        $course3 = Course::where('code', 'MK201')->first();

        if ($student1 && $course1) {
            Enrollment::firstOrCreate([
                'student_id' => $student1->id,
                'course_id' => $course1->id,
                'grade' => 'A',
            ]);
        }
        if ($student1 && $course2) {
            Enrollment::firstOrCreate([
                'student_id' => $student1->id,
                'course_id' => $course2->id,
                'grade' => 'B+',
            ]);
        }
        if ($student2 && $course1) {
            Enrollment::firstOrCreate([
                'student_id' => $student2->id,
                'course_id' => $course1->id,
                'grade' => 'A-',
            ]);
        }
        if ($student2 && $course3) {
            Enrollment::firstOrCreate([
                'student_id' => $student2->id,
                'course_id' => $course3->id,
                'grade' => null, // Example of a pending grade
            ]);
        }
    }
}