<?php

// database/migrations/2025_12_02_xxxxxx_update_enrollments_for_academic_year.php

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
        Schema::table('enrollments', function (Blueprint $table) {
            // // ... Drop semester_id column logic ...
            
            // // 1. Add as nullable (required to pass the constraint check)
            $table->foreignId('academic_year_id')
                  ->nullable() // MUST BE NULLABLE
                  ->constrained('academic_years')
                  ->after('section_id'); 

            $table->string('semester', 50)->nullable()->after('academic_year_id');
        });
        
        // --- DATA POPULATION STEP ---
        
        // Find a default Academic Year ID (e.g., the oldest or the current one)
        // You'll need to know a valid ID to use as a fallback. Let's assume AY ID is 1.
        // $defaultAcademicYearId = 3; 
        // $defaultSemester = '1st'; 
        
        // // 2. Set the default values for existing rows
        // DB::statement("
        //     UPDATE enrollments 
        //     SET academic_year_id = ?, 
        //         semester = ? 
        //     WHERE academic_year_id IS NULL
        // ", [$defaultAcademicYearId, $defaultSemester]);
    }

    /**
     * Reverse the migrations.
     * (We keep the down method for proper rollback if you need to reverse the ADDED columns later)
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Remove the new columns
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
            $table->dropColumn('semester');

            // Re-add the old semester_id column for a clean rollback
            // You MUST know the table it constrained against. Assuming 'semesters'.
            // $table->foreignId('semester_id')->nullable()->constrained('semesters');
        });
    }
};
