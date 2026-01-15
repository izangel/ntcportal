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
        // 1. Disable foreign key checks to allow dropping a table with active links
   //     DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        Schema::dropIfExists('enrollments');

        // 2. Recreate the table with all 14 fields from your screenshot
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id(); // Field 1
            
            // Foreign Keys (Fields 2, 3, 4, 11, 13, 14)
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Data Fields (Fields 5, 6, 7, 8, 12)
            $table->boolean('is_new_student')->default(false);
            $table->string('grade')->nullable();
            $table->string('original_grade', 5)->nullable();
            $table->timestamp('resolution_date')->nullable();
            $table->string('semester', 50);

            // Timestamps (Fields 9, 10)
            $table->timestamps();

            // 3. Recreate the Unique Index properly
            // Based on your screenshot, this unique index currently uses section_id.
            // We'll give it a clean name that reflects the actual columns.
            $table->unique(
                ['student_id', 'section_id', 'semester_id'], 
                'enrollments_student_section_semester_unique'
            );
        });

      //  DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('enrollments');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
