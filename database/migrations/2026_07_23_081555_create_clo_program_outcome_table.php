<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clo_program_outcome', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_learning_outcome_id')
                  ->constrained('course_learning_outcomes')
                  ->cascadeOnDelete();
            $table->foreignId('program_outcome_id')
                  ->constrained('program_outcomes')
                  ->cascadeOnDelete();
            $table->enum('level', ['I', 'G', 'A']);
            $table->timestamps();

            $table->unique(['course_learning_outcome_id', 'program_outcome_id'], 'clo_po_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clo_program_outcome');
    }
};
