<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_program')) {
            return;
        }

        Schema::table('course_program', function (Blueprint $table) {
            $table->index('course_id', 'course_program_course_id_index');
            $table->index('program_id', 'course_program_program_id_index');
            $table->dropUnique('course_program_course_id_program_id_unique');
            $table->unique(
                ['course_id', 'program_id', 'effective_batch_year'],
                'course_program_course_program_batch_unique'
            );
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('course_program')) {
            return;
        }

        Schema::table('course_program', function (Blueprint $table) {
            $table->dropUnique('course_program_course_program_batch_unique');
            $table->unique(['course_id', 'program_id']);
        });
    }
};