<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('student_educations') || ! Schema::hasColumn('student_educations', 'year_graduated')) {
            return;
        }

        DB::statement('ALTER TABLE `student_educations` MODIFY `year_graduated` VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('student_educations') || ! Schema::hasColumn('student_educations', 'year_graduated')) {
            return;
        }

        DB::statement("UPDATE `student_educations` SET `year_graduated` = '' WHERE `year_graduated` IS NULL");
        DB::statement('ALTER TABLE `student_educations` MODIFY `year_graduated` VARCHAR(255) NOT NULL');
    }
};
