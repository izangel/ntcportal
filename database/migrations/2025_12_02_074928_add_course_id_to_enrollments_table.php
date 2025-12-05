<?php

// database/migrations/..._add_course_id_to_enrollments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Check if the column exists before attempting to add it
            if (!Schema::hasColumn('enrollments', 'course_id')) {
                $table->foreignId('course_id')
                      ->nullable() 
                      ->constrained('courses')
                      ->after('section_id'); 
            } else {
                // The column exists, but the constraint might be missing/duplicated.
                // We'll try to add the constraint directly, guarding against the duplicate key error.
                try {
                    $table->foreign('course_id')
                          ->references('id')
                          ->on('courses')
                          ->name('enrollments_course_id_foreign'); // Use the default name
                } catch (\Exception $e) {
                    // Ignore the duplicate key name error (1061) if it occurs.
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Use try-catch in down() for safety against missing keys/columns
            try {
                $table->dropForeign(['course_id']);
            } catch (\Exception $e) {
                // Ignore failure to drop foreign key
            }
            
            if (Schema::hasColumn('enrollments', 'course_id')) {
                 $table->dropColumn('course_id');
            }
        });
    }
};