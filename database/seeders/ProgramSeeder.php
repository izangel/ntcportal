<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            'BSIS', 'ACT-MM', 'ACT-NET', 'ACT-DE', 'ACT-APP',
            'BTVTED-VGD', 'BTVTED-CP', 'DIT', 'DHRT',
            'SHS-TVLHE', 'SHS-TVLICT', 'SHS-ABM', 'SHS-STEM', 'SHS-GAS', 'SHS-HUMSS'
        ];

        foreach ($programs as $programName) {
            Program::firstOrCreate(['name' => $programName]);
        }
    }
}
