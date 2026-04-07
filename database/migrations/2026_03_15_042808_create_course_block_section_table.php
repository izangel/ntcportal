<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_block_section', function (Blueprint $table) {
            $table->id();
            
            // Link to the Section
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            
            // Link to the Course Block
            $table->foreignId('course_block_id')->constrained('course_blocks')->onDelete('cascade');
            
            // Academic Period context (Very important for historical records)
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('semester'); // 1st Semester, 2nd Semester, etc.

            $table->timestamps();

            // Prevention: Ensure a section can't have the same course block twice in the same term
            $table->unique(['section_id', 'course_block_id', 'academic_year_id', 'semester'], 'section_block_term_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_block_section');
    }
};