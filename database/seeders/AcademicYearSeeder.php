<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicYear; // Import the model

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: Create a current academic year and mark it active
        AcademicYear::firstOrCreate(
            ['start_year' => 2024],
            [
                'end_year' => 2025,
                'is_active' => true,
            ]
        );

        // You can add more historical academic years if needed, marking them inactive
        AcademicYear::firstOrCreate(
            ['start_year' => 2023],
            [
                'end_year' => 2024,
                'is_active' => false,
            ]
        );
    }
}