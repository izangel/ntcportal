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
        Schema::create('student_courseblock', function (Blueprint $table) {
            $table->id();

            // Link to the student
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();

            // The specific details you requested
            $table->string('course_code')->nullable();
            $table->string('course_title')->nullable();
            $table->string('faculty')->nullable();
            $table->string('rooms')->nullable();
            $table->string('schedule')->nullable();

            // Optional: Track status (e.g., enrolled, dropped)
            $table->string('status')->default('enrolled');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_courseblock');
    }
};
