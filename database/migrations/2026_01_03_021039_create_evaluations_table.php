<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            
            // The Faculty/Teacher being evaluated
            $table->unsignedBigInteger('teacher_id'); 
            
            // Evaluator Details
            $table->string('evaluator_type'); // 'student', 'peer', 'self', 'supervisor'
            $table->unsignedBigInteger('evaluator_id'); // ID from users or students table
            
            // Contextual Data
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('academic_year_id')->constrained();
            $table->string('semester'); // '1st', '2nd', 'Summer'

            // Scores and Feedback
            $table->json('ratings'); // Stores the 17 question responses
            $table->decimal('mean_score', 3, 2); // Pre-calculated average (e.g., 4.55)
            $table->text('aspects_helped')->nullable();
            $table->text('aspects_improved')->nullable();
            $table->text('comments')->nullable();

            $table->timestamps();

            // Indexing for faster reporting
            $table->index(['teacher_id', 'academic_year_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};