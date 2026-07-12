<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'President', 'slug' => 'president', 'program_type' => 'both', 'sort_order' => 1],
            ['name' => 'Vice President', 'slug' => 'vice_president', 'program_type' => 'both', 'sort_order' => 2],
            ['name' => 'Secretary', 'slug' => 'secretary', 'program_type' => 'both', 'sort_order' => 3],
            ['name' => 'Treasurer', 'slug' => 'treasurer', 'program_type' => 'both', 'sort_order' => 4],
            ['name' => 'Auditor', 'slug' => 'auditor', 'program_type' => 'both', 'sort_order' => 5],
            ['name' => 'PIO', 'slug' => 'pio', 'program_type' => 'both', 'sort_order' => 6],
            ['name' => 'Business Manager', 'slug' => 'business_manager', 'program_type' => 'both', 'sort_order' => 7],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
