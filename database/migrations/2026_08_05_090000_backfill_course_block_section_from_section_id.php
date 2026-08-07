<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('course_blocks')
            ->whereNotNull('section_id')
            ->get(['id', 'section_id', 'academic_year_id', 'semester']);

        $inserted = 0;
        foreach ($rows as $block) {
            $exists = DB::table('course_block_section')
                ->where('course_block_id', $block->id)
                ->where('section_id', $block->section_id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('course_block_section')->insert([
                'course_block_id' => $block->id,
                'section_id' => $block->section_id,
                'academic_year_id' => $block->academic_year_id,
                'semester' => $block->semester,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $inserted++;
        }

        echo "Backfilled {$inserted} course_block_section pivot rows from legacy course_blocks.section_id.\n";
    }

    public function down(): void
    {
        // No data restoration; the source column is dropped in a later migration.
    }
};