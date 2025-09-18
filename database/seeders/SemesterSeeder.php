<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicYear; // Import AcademicYear to link to it
use App\Models\Semester;     // Import Semester model
use Carbon\Carbon;           // For handling dates

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the active academic year
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        if ($activeAcademicYear) {
            // First Semester for the active year
            Semester::firstOrCreate(
                [
                    'academic_year_id' => $activeAcademicYear->id,
                    'name' => 'First Semester',
                ],
                [
                    'start_date' => Carbon::create($activeAcademicYear->start_year, 8, 1), // Aug 1
                    'end_date' => Carbon::create($activeAcademicYear->start_year, 12, 15), // Dec 15
                    'is_active' => true, // Mark active for enrollment
                ]
            );

            // Second Semester for the active year
            Semester::firstOrCreate(
                [
                    'academic_year_id' => $activeAcademicYear->id,
                    'name' => 'Second Semester',
                ],
                [
                    'start_date' => Carbon::create($activeAcademicYear->end_year, 1, 15), // Jan 15
                    'end_date' => Carbon::create($activeAcademicYear->end_year, 5, 30), // May 30
                    'is_active' => false, // Not active for current enrollment unless specified
                ]
            );

            // Summer Term for the active year
            Semester::firstOrCreate(
                [
                    'academic_year_id' => $activeAcademicYear->id,
                    'name' => 'Summer',
                ],
                [
                    'start_date' => Carbon::create($activeAcademicYear->end_year, 6, 10), // June 10
                    'end_date' => Carbon::create($activeAcademicYear->end_year, 7, 20), // July 20
                    'is_active' => false,
                ]
            );
        }
    }
}