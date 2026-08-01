<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_tasks') && !Schema::hasColumn('assessment_tasks', 'effective_batch_year')) {
            Schema::table('assessment_tasks', function (Blueprint $table) {
                $table->string('effective_batch_year', 10)->nullable()->after('course_id');
            });
        }

        if (Schema::hasTable('assessment_items') && !Schema::hasColumn('assessment_items', 'effective_batch_year')) {
            Schema::table('assessment_items', function (Blueprint $table) {
                $table->string('effective_batch_year', 10)->nullable()->after('assessment_task_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_items') && Schema::hasColumn('assessment_items', 'effective_batch_year')) {
            Schema::table('assessment_items', function (Blueprint $table) {
                $table->dropColumn('effective_batch_year');
            });
        }

        if (Schema::hasTable('assessment_tasks') && Schema::hasColumn('assessment_tasks', 'effective_batch_year')) {
            Schema::table('assessment_tasks', function (Blueprint $table) {
                $table->dropColumn('effective_batch_year');
            });
        }
    }
};
