<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\Course;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\Employee;
use App\Models\CourseBlock;
use App\Models\CourseLearningOutcome;
use App\Models\AssessmentTask;
use App\Models\AssessmentItem;
use App\Models\StudentAssessmentMark;
use App\Models\ProgramOutcome;
use App\Models\Peo;
use App\Models\SectionStudent;
use App\Models\BloomsTaxonomy;

class CourseDashboardSeeder extends Seeder
{
    private const BATCHES = [2024, 2025, 2026];

    private const STUDENTS_PER_BATCH = 18;

    private const COURSES = [
        ['code' => 'CC101', 'name' => 'Introduction to Computing'],
        ['code' => 'CC102', 'name' => 'Computer Programming 1'],
        ['code' => 'CC103', 'name' => 'Computer Programming 2'],
        ['code' => 'CC104', 'name' => 'Data Structures and Algorithms'],
        ['code' => 'CC105', 'name' => 'Information Management'],
        ['code' => 'CC106', 'name' => 'Web Systems and Technologies'],
    ];

    private const TASKS = [
        ['title' => 'Prelim Quiz', 'type' => 'Quiz', 'weight_percentage' => 20, 'total_marks' => 50],
        ['title' => 'Midterm Exam', 'type' => 'Exam', 'weight_percentage' => 30, 'total_marks' => 100],
        ['title' => 'Final Project', 'type' => 'Project', 'weight_percentage' => 50, 'total_marks' => 100],
    ];

    private const CLO_DESCRIPTIONS = [
        1 => 'Explain the fundamental concepts, principles and terminology of the subject.',
        2 => 'Apply core methods and techniques to solve representative problems.',
        3 => 'Analyze case scenarios and evaluate solutions using appropriate tools.',
    ];

    private const PO_DESCRIPTIONS = [
        'PO1' => 'Apply knowledge of computing, mathematics and social sciences appropriate to the discipline.',
        'PO2' => 'Analyze problems and identify computing requirements appropriate to their solution.',
        'PO3' => 'Design, implement and evaluate computing-based solutions to meet desired needs.',
        'PO4' => 'Function effectively on teams to accomplish a common goal.',
        'PO5' => 'Communicate effectively with a range of audiences.',
        'PO6' => 'Recognize professional, ethical, legal and social responsibilities.',
    ];

    private const PEO_DESCRIPTIONS = [
        'PEO1' => 'Exhibit technical competence and pursue careers in the field.',
        'PEO2' => 'Demonstrate leadership and collaboration in multidisciplinary teams.',
        'PEO3' => 'Engage in lifelong learning and professional development.',
    ];

    public function run(): void
    {
        $program = Program::firstOrCreate(['name' => 'BSIS']);

        $facultyIds = Employee::whereNull('deleted_at')->pluck('id')->values();
        if ($facultyIds->isEmpty()) {
            $this->command->warn('No employees found; course blocks will use faculty_id null.');
        }

        $courseModels = collect(self::COURSES)->map(function ($c) {
            return Course::firstOrCreate(
                ['code' => $c['code']],
                ['name' => $c['name'], 'description' => $c['name'], 'units' => 3]
            );
        });

        $bloomIds = BloomsTaxonomy::pluck('id')->values()->all();

        foreach (self::BATCHES as $year) {
            $this->command->info("Seeding batch {$year}...");
            $this->seedBatch($program, $courseModels, $facultyIds, $year, $bloomIds);
        }
    }

    private function seedBatch(Program $program, $courseModels, $facultyIds, int $year, array $bloomIds): void
    {
        $ay = AcademicYear::firstOrCreate(
            ['start_year' => $year],
            ['end_year' => $year + 1, 'is_active' => false]
        );

        $sections = collect(['1A', '1B', '2A', '2B'])
            ->map(fn ($name) => Section::firstOrCreate(
                ['program_id' => $program->id, 'name' => $name, 'academic_year_id' => $ay->id],
                ['name' => $name]
            ));

        $pos = $this->ensureProgramOutcomes($program, $year);
        $this->ensurePeos($program, $year);

        $students = $this->ensureStudents($ay, $sections, $year);

        $facultyId = $facultyIds->isEmpty() ? null : $facultyIds->random();

        foreach ($courseModels as $index => $course) {
            $this->seedCourseBatch($course, $program, $ay, $year, $pos, $students, $sections, $facultyId, $bloomIds, $index);
        }
    }

    private function ensureProgramOutcomes(Program $program, int $year)
    {
        return collect(self::PO_DESCRIPTIONS)->map(function ($desc, $code) use ($program, $year) {
            return ProgramOutcome::firstOrCreate(
                ['program_id' => $program->id, 'code' => $code, 'effective_batch_year' => (string) $year],
                ['description' => $desc]
            );
        })->values();
    }

    private function ensurePeos(Program $program, int $year): void
    {
        foreach (self::PEO_DESCRIPTIONS as $code => $desc) {
            Peo::firstOrCreate(
                ['program_id' => $program->id, 'code' => $code, 'effective_batch_year' => (string) $year],
                ['description' => $desc]
            );
        }
    }

    private function ensureStudents(AcademicYear $ay, $sections, int $year)
    {
        $students = collect();

        for ($i = 1; $i <= self::STUDENTS_PER_BATCH; $i++) {
            $studentNumber = sprintf('%04d-%03d', $year, $i);
            $firstName = 'Batch' . $year;
            $lastName = 'Student ' . $i;
            $email = 'cb' . $year . '.' . $i . '@example.com';

            $student = Student::where('student_id', $studentNumber)->first();

            if (!$student) {
                $user = User::forceCreate([
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'role' => 'student',
                ]);

                $student = Student::forceCreate([
                    'id' => $user->id,
                    'user_id' => $user->id,
                    'student_id' => $studentNumber,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'enrollment_status' => 'Enrolled',
                    'is_fully_enrolled' => true,
                ]);
            } else {
                if (!User::whereKey($student->id)->exists()) {
                    User::forceCreate([
                        'id' => $student->id,
                        'name' => $firstName . ' ' . $lastName,
                        'email' => $email,
                        'password' => bcrypt('password'),
                        'role' => 'student',
                    ]);
                }
                $student->update(['user_id' => $student->id]);
            }

            if ((int) $student->batch_year !== $year) {
                DB::table('students')->where('id', $student->id)->update(['batch_year' => $year]);
            }

            $section = $sections->get(($i - 1) % $sections->count());

            foreach (['1st', '2nd'] as $semester) {
                SectionStudent::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'section_id' => $section->id,
                        'academic_year_id' => $ay->id,
                        'semester' => $semester,
                    ],
                    ['status' => 'Enrolled']
                );
            }

            $students->push($student);
        }

        return $students;
    }

    private function seedCourseBatch(
        Course $course,
        Program $program,
        AcademicYear $ay,
        int $year,
        $pos,
        $students,
        $sections,
        $facultyId,
        array $bloomIds,
        int $courseIndex
    ): void {
        DB::table('course_program')->updateOrInsert(
            [
                'course_id' => $course->id,
                'program_id' => $program->id,
                'effective_batch_year' => (string) $year,
            ],
            ['updated_at' => now(), 'created_at' => now()]
        );

        $clos = collect();
        for ($i = 1; $i <= 3; $i++) {
            $clo = CourseLearningOutcome::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'code' => 'CLO' . $i,
                    'effective_batch_year' => (string) $year,
                ],
                [
                    'description' => self::CLO_DESCRIPTIONS[$i],
                    'blooms_taxonomy_id' => $bloomIds[($i - 1) % count($bloomIds)],
                    'is_active' => true,
                ]
            );

            $po = $pos->get(($i - 1) % $pos->count());
            $clo->programOutcomes()->syncWithoutDetaching([
                $po->id => ['level' => $i === 1 ? 'I' : ($i === 2 ? 'G' : 'A')],
            ]);

            $clos->push($clo);
        }

        $items = collect();
        foreach (self::TASKS as $taskIndex => $taskSpec) {
            $task = AssessmentTask::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => $taskSpec['title'],
                    'effective_batch_year' => (string) $year,
                ],
                [
                    'type' => $taskSpec['type'],
                    'weight_percentage' => $taskSpec['weight_percentage'],
                    'total_marks' => $taskSpec['total_marks'],
                ]
            );

            for ($part = 1; $part <= 2; $part++) {
                $clo = $clos->get(($taskIndex + $part - 1) % $clos->count());
                $item = AssessmentItem::firstOrCreate(
                    [
                        'assessment_task_id' => $task->id,
                        'item_name' => 'Part ' . chr(64 + $part),
                        'course_learning_outcome_id' => $clo->id,
                        'effective_batch_year' => (string) $year,
                    ],
                    [
                        'max_marks' => round($taskSpec['total_marks'] / 2, 2),
                    ]
                );
                $items->push($item);
            }
        }

        $blockStudents = $students;

        foreach (['1st', '2nd'] as $blockIndex => $semester) {
            $section = $sections->get($blockIndex % $sections->count());
            $blockFacultyId = $facultyId ?? $courseIndex + $blockIndex;

            $block = CourseBlock::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'academic_year_id' => $ay->id,
                    'semester' => $semester,
                    'section_id' => $section->id,
                ],
                [
                    'faculty_id' => $blockFacultyId,
                    'room_name' => 'R' . (100 + $courseIndex * 10 + $blockIndex),
                    'schedule_string' => ($blockIndex % 2 === 0 ? 'MW' : 'TH') . ' 1:00 PM - 3:00 PM',
                    'finalized' => true,
                ]
            );

            DB::table('course_block_section')->updateOrInsert(
                [
                    'section_id' => $section->id,
                    'course_block_id' => $block->id,
                    'academic_year_id' => $ay->id,
                    'semester' => $semester,
                ],
                ['created_at' => now(), 'updated_at' => now()]
            );

            foreach ($blockStudents as $student) {
                DB::table('student_courseblock')->updateOrInsert(
                    [
                        'student_id' => $student->id,
                        'course_block_id' => $block->id,
                    ],
                    [
                        'grade' => null,
                        'remarks' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            foreach ($blockStudents as $student) {
                foreach ($items as $item) {
                    $ratio = mt_rand(40, 100) / 100;
                    $marks = round($item->max_marks * $ratio, 2);

                    StudentAssessmentMark::firstOrCreate(
                        [
                            'student_id' => $student->id,
                            'assessment_item_id' => $item->id,
                        ],
                        ['marks_obtained' => $marks]
                    );
                }
            }
        }
    }
}
