<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_course', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys and Context
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('semester'); // e.g., '1st Semester', '2nd Semester'
            
            // New Validation/Audit Columns
            $table->boolean('validated')->default(false);
            $table->unsignedBigInteger('validated_by')->nullable(); // User ID of the validator
            
            $table->timestamps();
            
            // Ensure a student isn't assigned the same course in the same section/semester context
            $table->unique(['student_id', 'course_id', 'section_id', 'academic_year_id', 'semester'], 'student_course_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_course');
    }
};