<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_blocks', function (Blueprint $table) {
            // $table->id();
            // // Core Assignments
            // $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            // $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            // $table->foreignId('faculty_id')->constrained('employees')->onDelete('cascade'); // Links to 'employees' table
            // $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade'); // Links to 'academic_years' table
            // $table->string('semester', 50); // e.g., '1st Semester'

            // // Data Stored as Strings (Per requirement)
            // $table->string('room_name', 100); // e.g., 'C305'
            // $table->string('schedule_string', 150); // e.g., 'MW 1:00 PM - 3:00 PM'

            // $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_blocks');
    }
};