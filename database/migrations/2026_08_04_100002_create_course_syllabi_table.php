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
        Schema::create('course_syllabi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_block_id')
                  ->constrained('course_blocks')
                  ->cascadeOnDelete()
                  ->unique();
            $table->text('grading_system')->nullable();
            $table->text('textbooks_references')->nullable();
            $table->text('classroom_policies')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_syllabi');
    }
};