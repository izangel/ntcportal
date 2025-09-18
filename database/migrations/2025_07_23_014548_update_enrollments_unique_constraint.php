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
        Schema::table('enrollments', function (Blueprint $table) {
            // 1. Temporarily drop foreign key constraints.
            $table->dropForeign(['student_id']);
            $table->dropForeign(['course_id']);

            // 2. Drop the problematic unique index.
            $table->dropUnique('enrollments_student_id_course_id_unique');

            // 3. Re-add the foreign key constraints using existing columns.
            // DO NOT use foreignId() here, as it tries to add the column again.
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');

            // 4. Add the new, correct unique constraint.
            $table->unique(['student_id', 'course_id', 'semester_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Reverse the operations in the 'down' method for proper rollback.

            // 1. Drop the new unique constraint.
            $table->dropUnique(['student_id', 'course_id', 'semester_id']);

            // 2. Drop the foreign key constraints.
            $table->dropForeign(['student_id']);
            $table->dropForeign(['course_id']);

            // 3. Re-add the old unique constraint (using its original name)
            $table->unique(['student_id', 'course_id'], 'enrollments_student_id_course_id_unique');

            // 4. Re-add the foreign key constraints.
            // DO NOT use foreignId() here.
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }
};