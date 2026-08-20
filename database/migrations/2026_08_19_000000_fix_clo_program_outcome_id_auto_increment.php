<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The pivot table's id column was created without AUTO_INCREMENT on some
        // environments, causing "Field 'id' doesn't have a default value" on insert.
        DB::statement('ALTER TABLE `clo_program_outcome` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `clo_program_outcome` MODIFY `id` BIGINT UNSIGNED NOT NULL');
    }
};