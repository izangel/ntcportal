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
        Schema::create('faculty_loadings', function (Blueprint $table) {
            $table->id();
            
            // References to other tables (Foreign Keys)
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('semester');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('faculty_id')->constrained('employees')->onDelete('cascade'); // Assuming faculty are users
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade'); // Assuming faculty are users
            
            // Specific Loading Details
           
            $table->string('room', 50);
            $table->string('schedule', 100); // e.g., 'MWF 10:00-11:00 AM'
            
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_loadings');
    }
};
