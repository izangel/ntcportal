<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add versioning to Program Educational Objectives (PEOs)
        if (Schema::hasTable('peos')) {
            Schema::table('peos', function (Blueprint $table) {
                $table->string('effective_batch_year', 10)->nullable()->after('program_id')
                    ->comment('Initial batch cohort year this PEO takes effect (e.g., 2024, 2026)');
            });
        }

        // 2. Add versioning to Program Outcomes / PILOs
        if (Schema::hasTable('program_outcomes')) {
            Schema::table('program_outcomes', function (Blueprint $table) {
                $table->string('effective_batch_year', 10)->nullable()->after('program_id')
                    ->comment('Batch cohort year this PO/PILO applies to (e.g., 2024, 2026)');
            });
        }

        // 3. Add versioning to Program-Course pivot (Curriculum Structure)
        if (Schema::hasTable('course_program')) {
            Schema::table('course_program', function (Blueprint $table) {
                $table->string('effective_batch_year', 10)->nullable()->after('course_id')
                    ->comment('Batch cohort year this curriculum mapping applies to');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('peos')) {
            Schema::table('peos', function (Blueprint $table) {
                $table->dropColumn('effective_batch_year');
            });
        }

        if (Schema::hasTable('program_outcomes')) {
            Schema::table('program_outcomes', function (Blueprint $table) {
                $table->dropColumn('effective_batch_year');
            });
        }

        if (Schema::hasTable('course_program')) {
            Schema::table('course_program', function (Blueprint $table) {
                $table->dropColumn('effective_batch_year');
            });
        }
    }
};