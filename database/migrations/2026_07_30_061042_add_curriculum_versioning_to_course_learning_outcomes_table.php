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
        if (Schema::hasTable('course_learning_outcomes')) {
            Schema::table('course_learning_outcomes', function (Blueprint $table) {
                $table->string('effective_batch_year', 10)->nullable()->after('course_id')
                    ->comment('Batch cohort year this CLO applies to');
                    
                $table->boolean('is_active')->default(true)->after('effective_batch_year')
                    ->comment('Soft flag to archive older CLOs from dropdowns');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('course_learning_outcomes')) {
            Schema::table('course_learning_outcomes', function (Blueprint $table) {
                $table->dropColumn(['effective_batch_year', 'is_active']);
            });
        }
    }
};