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
        Schema::table('student_courseblock', function (Blueprint $table) {
            // 1. Add the columns
            $table->foreignId('student_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('course_block_id')->after('student_id')->constrained()->onDelete('cascade');
        });
    }

   

    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        Schema::table('student_courseblock', function (Blueprint $table) {
            // 2. Drop the foreign keys and columns
            $table->dropForeign(['student_id']);
            $table->dropForeign(['course_block_id']);
            $table->dropColumn(['student_id', 'course_block_id']);
        });
    }
};
