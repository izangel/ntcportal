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
      // 1. DROP THE FOREIGN KEY (Surgical Check)
        // We check if it exists as a FOREIGN KEY in the system tables first
        $fkExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'enrollments' 
            AND CONSTRAINT_NAME = 'enrollments_course_id_foreign' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        if (!empty($fkExists)) {
            DB::statement('ALTER TABLE enrollments DROP FOREIGN KEY enrollments_course_id_foreign');
        }

        // 2. DROP THE INDEX (Surgical Check)
        // Even if the Foreign Key is gone, the INDEX (from your screenshot) often remains.
        $indexExists = DB::select("
            SELECT INDEX_NAME 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'enrollments' 
            AND INDEX_NAME = 'enrollments_course_id_foreign'
        ");

        if (!empty($indexExists)) {
            DB::statement('ALTER TABLE enrollments DROP INDEX enrollments_course_id_foreign');
        }

        // 3. DROP THE MISNAMED UNIQUE KEY
        try {
            DB::statement('ALTER TABLE enrollments DROP INDEX enrollments_student_id_course_id_semester_id_unique');
        } catch (\Exception $e) {
            // Already gone
        }

        // 4. FIX COLUMNS AND ADD NEW KEYS
        Schema::table('enrollments', function (Blueprint $table) {
            // Add course_id if it's not there
            if (!Schema::hasColumn('enrollments', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('student_id');
            }
        });

        Schema::table('enrollments', function (Blueprint $table) {
            // Re-apply constraints with proper logic
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            // Give section_id its own unique constraint name
            /*$table->foreign('section_id', 'enrollments_section_id_fk')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade');*/

            // New unique index
            $table->unique(['student_id', 'course_id', 'semester_id'], 'enrollments_pivot_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['section_id']);
            $table->dropUnique('enrollments_standard_unique');
            $table->dropColumn('course_id');
        });
    }
};