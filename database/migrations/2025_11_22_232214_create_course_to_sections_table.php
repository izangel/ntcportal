<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // In up() method of the migration file
    public function up(): void
    {
        Schema::create('course_to_sections', function (Blueprint $table) {
            $table->id();
            
            // References to other tables (Foreign Keys)
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('semester');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade'); 
            
            
           
            
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_to_sections');
    }
};
