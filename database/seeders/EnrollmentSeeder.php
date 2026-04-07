<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use App\Models\Section; // <--- Make sure to import this!

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get sample students
        $student1 = Student::where('student_id', 'S001')->first();
        $student2 = Student::where('student_id', 'S002')->first();

        // 2. Get sample courses
        $course1 = Course::where('code', 'CS101')->first();
        $course2 = Course::where('code', 'MA101')->first();
        $course3 = Course::where('code', 'MK201')->first();

        // 3. Get sample sections (FIX: You were missing this part!)
        // Adjust 'name' or 'id' to match whatever data you actually have in your sections table.
        // If you don't have specific names, you can just grab the first few:
        $section1 = Section::first();
        $section2 = Section::skip(1)->first() ?? $section1; // Fallback to section1 if section2 doesn't exist
        $section3 = Section::skip(2)->first() ?? $section1;

        // Safety check: Ensure we actually found the necessary data before trying to create enrollments
        if (!$section1) {
            $this->command->info('Skipping EnrollmentSeeder: No sections found. Run SectionSeeder first.');
            return;
        }

        // 4. Create Enrollments
        if ($student1 && $course1) {
            Enrollment::firstOrCreate([
                'student_id' => $student1->id,
                'course_id'  => $course1->id,
                'section_id' => $section2->id, // Now $section2 is defined
            ], [
                // Attributes to set if the record is created (not found)
                'grade'            => 'A',
                'academic_year_id' => 1,
                'semester'         => 'Fall',
                'is_new_student'   => false,
            ]);
        }

        if ($student1 && $course2) {
            Enrollment::firstOrCreate([
                'student_id' => $student1->id,
                'course_id'  => $course2->id,
                'section_id' => $section2->id,
            ], [
                'grade'            => 'B+',
                'academic_year_id' => 1,
                'semester'         => 'Fall',
                'is_new_student'   => true,
            ]);
        }

        if ($student2 && $course1) {
            Enrollment::firstOrCreate([
                'student_id' => $student2->id,
                'course_id'  => $course1->id,
                'section_id' => $section2->id,
            ], [
                'grade'            => 'A-',
                'academic_year_id' => 1,
                'semester'         => 'Fall',
                'is_new_student'   => false,
            ]);
        }

        if ($student2 && $course3) {
            Enrollment::firstOrCreate([
                'student_id' => $student2->id,
                'course_id'  => $course3->id,
                'section_id' => $section3->id,
            ], [
                'grade'            => null,
                'academic_year_id' => 1,
                'semester'         => 'Fall',
                'is_new_student'   => true,
            ]);
        }
    }
}