<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('student_courseblock', function (Blueprint $table) {
        $table->id();
        $table->foreignId('student_id')->constrained()->onDelete('cascade');
        // Assuming your course blocks table is named 'course_blocks'
        $table->foreignId('course_block_id')->constrained('course_blocks')->onDelete('cascade');
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('student_course');
    }
};