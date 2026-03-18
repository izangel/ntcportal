<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty_evaluations', function (Blueprint $table) {
            $table->id();
            // Link to the student and the specific course block
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_block_id')->constrained()->onDelete('cascade');
            
            // Evaluation Data
            $table->json('ratings'); // Stores individual question scores
            $table->decimal('mean_score', 3, 2); // Calculated average (e.g., 4.50)
            
            // Qualitative Feedback
            $table->text('aspects_helped')->nullable();
            $table->text('aspects_improved')->nullable();
            $table->text('comments')->nullable();
            
            $table->timestamps();

            // Prevent a student from evaluating the same course block twice
            $table->unique(['student_id', 'course_block_id'], 'student_block_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_evaluations');
    }
};