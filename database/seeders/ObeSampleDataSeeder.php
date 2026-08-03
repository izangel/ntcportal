<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\Course;
use App\Models\Peo;
use App\Models\ProgramOutcome;
use App\Models\CourseLearningOutcome;
use App\Models\InstitutionalGoal;
use App\Models\BloomsTaxonomy;

/**
 * Seeds sample OBE structure (Institutional Goals, PEOs, POs, CLOs and
 * their mappings) for every batch cohort so the OBE setup, program matrix
 * and course dashboard have data to display. Idempotent.
 */
class ObeSampleDataSeeder extends Seeder
{
    private const INSTITUTIONAL_GOALS = [
        ['code' => 'IG-01', 'description' => 'Produce competent and value-driven professionals who contribute to national and regional development.'],
        ['code' => 'IG-02', 'description' => 'Foster innovation, research and entrepreneurship to address the needs of society.'],
        ['code' => 'IG-03', 'description' => 'Promote lifelong learning, ethical responsibility and meaningful community engagement.'],
    ];

    private const PEO_DESCRIPTIONS = [
        'PEO-01' => 'Graduates become competent professionals engaged in productive work or entrepreneurial endeavors relevant to their field of study.',
        'PEO-02' => 'Graduates demonstrate leadership, teamwork and effective communication in multidisciplinary environments.',
        'PEO-03' => 'Graduates pursue lifelong learning, continuous professional development and ethical service to society.',
    ];

    private const PO_DESCRIPTIONS = [
        'PO-01' => 'Apply knowledge of computing, mathematics and social sciences appropriate to the discipline.',
        'PO-02' => 'Analyze problems and identify computing requirements appropriate to their solution.',
        'PO-03' => 'Design, implement and evaluate computing-based solutions to meet desired needs.',
        'PO-04' => 'Function effectively on teams to accomplish a common goal.',
        'PO-05' => 'Communicate effectively with a range of audiences.',
        'PO-06' => 'Recognize professional, ethical, legal and social responsibilities in the practice of the profession.',
    ];

    private const PO_PEO_MAP = [
        'PO-01' => ['PEO-01', 'PEO-03'],
        'PO-02' => ['PEO-01'],
        'PO-03' => ['PEO-01', 'PEO-02'],
        'PO-04' => ['PEO-02'],
        'PO-05' => ['PEO-02', 'PEO-03'],
        'PO-06' => ['PEO-03'],
    ];

    private const IG_PEO_MAP = [
        'IG-01' => ['PEO-01', 'PEO-02'],
        'IG-02' => ['PEO-01', 'PEO-03'],
        'IG-03' => ['PEO-02', 'PEO-03'],
    ];

    private const COURSES = [
        ['code' => 'CC101', 'name' => 'Introduction to Computing'],
        ['code' => 'CC102', 'name' => 'Computer Programming 1'],
        ['code' => 'CC103', 'name' => 'Computer Programming 2'],
        ['code' => 'CC104', 'name' => 'Data Structures and Algorithms'],
        ['code' => 'CC105', 'name' => 'Information Management'],
        ['code' => 'CC106', 'name' => 'Web Systems and Technologies'],
    ];

    private const CLO_DESCRIPTIONS = [
        1 => 'Explain the fundamental concepts, principles and terminology of the subject.',
        2 => 'Apply core methods and techniques to solve representative problems.',
        3 => 'Analyze case scenarios and evaluate solutions using appropriate tools.',
    ];

    public function run(): void
    {
        $this->call(BloomsTaxonomySeeder::class);

        $this->seedInstitutionalGoals();

        $bloomIds = BloomsTaxonomy::pluck('id', 'code')->all();

        $batchYears = $this->batchYears();

        foreach ($batchYears as $year) {
            $this->command->info("Seeding sample PEOs / POs / CLOs for batch {$year}...");
            $this->seedBatch((int) $year, $bloomIds);
        }
    }

    private function batchYears(): array
    {
        return AcademicYear::query()
            ->whereNotNull('start_year')
            ->orderBy('start_year')
            ->pluck('start_year')
            ->unique()
            ->values()
            ->all();
    }

    private function seedInstitutionalGoals(): void
    {
        foreach (self::INSTITUTIONAL_GOALS as $goal) {
            InstitutionalGoal::firstOrCreate(['code' => $goal['code']], $goal);
        }
    }

    private function seedBatch(int $year, array $bloomIds): void
    {
        foreach (Program::orderBy('name')->get() as $program) {
            $peos = $this->ensurePeos($program, $year);
            $pos = $this->ensureProgramOutcomes($program, $year);

            $this->mapPosToPeos($pos, $peos);
            $this->mapPeosToInstitutionalGoals($peos);

            if (strtoupper($program->name) === 'BSIS') {
                $this->seedCourseClos($program, $year, $pos, $bloomIds);
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, Peo>
     */
    private function ensurePeos(Program $program, int $year)
    {
        return collect(self::PEO_DESCRIPTIONS)->map(function ($desc, $code) use ($program, $year) {
            return Peo::firstOrCreate(
                ['program_id' => $program->id, 'code' => $code, 'effective_batch_year' => (string) $year],
                ['description' => $desc]
            );
        })->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, ProgramOutcome>
     */
    private function ensureProgramOutcomes(Program $program, int $year)
    {
        return collect(self::PO_DESCRIPTIONS)->map(function ($desc, $code) use ($program, $year) {
            return ProgramOutcome::firstOrCreate(
                ['program_id' => $program->id, 'code' => $code, 'effective_batch_year' => (string) $year],
                ['description' => $desc]
            );
        })->values();
    }

    private function mapPosToPeos($pos, $peos): void
    {
        $peosByCode = $peos->keyBy('code');

        foreach (self::PO_PEO_MAP as $poCode => $peoCodes) {
            $po = $pos->firstWhere('code', $poCode);
            if (!$po) {
                continue;
            }

            $ids = collect($peoCodes)
                ->map(fn ($peoCode) => $peosByCode->get($peoCode)?->id)
                ->filter();

            $po->peos()->syncWithoutDetaching($ids->all());
        }
    }

    private function mapPeosToInstitutionalGoals($peos): void
    {
        $peosByCode = $peos->keyBy('code');
        $goals = InstitutionalGoal::pluck('id', 'code')->all();

        foreach (self::IG_PEO_MAP as $igCode => $peoCodes) {
            $goalId = $goals[$igCode] ?? null;
            if (!$goalId) {
                continue;
            }

            $ids = collect($peoCodes)
                ->map(fn ($peoCode) => $peosByCode->get($peoCode)?->id)
                ->filter();

            InstitutionalGoal::find($goalId)->peos()->syncWithoutDetaching($ids->all());
        }
    }

    private function seedCourseClos(Program $program, int $year, $pos, array $bloomIds): void
    {
        $courseModels = collect(self::COURSES)->map(function ($c) {
            return Course::firstOrCreate(
                ['code' => $c['code']],
                ['name' => $c['name'], 'description' => $c['name'], 'units' => 3]
            );
        });

        foreach ($courseModels as $course) {
            DB::table('course_program')->updateOrInsert(
                [
                    'course_id' => $course->id,
                    'program_id' => $program->id,
                    'effective_batch_year' => (string) $year,
                ],
                ['created_at' => now(), 'updated_at' => now()]
            );

            for ($i = 1; $i <= 3; $i++) {
                $clo = CourseLearningOutcome::firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'code' => 'CLO' . $i,
                        'effective_batch_year' => (string) $year,
                    ],
                    [
                        'description' => self::CLO_DESCRIPTIONS[$i],
                        'blooms_taxonomy_id' => $bloomIds[($i % 6 === 0 ? 'C6' : 'C' . $i)] ?? null,
                        'is_active' => true,
                    ]
                );

                $po = $pos->get(($i - 1) % $pos->count());
                if ($po) {
                    $clo->programOutcomes()->syncWithoutDetaching([
                        $po->id => ['level' => $i === 1 ? 'I' : ($i === 2 ? 'G' : 'A')],
                    ]);
                }
            }
        }
    }
}
