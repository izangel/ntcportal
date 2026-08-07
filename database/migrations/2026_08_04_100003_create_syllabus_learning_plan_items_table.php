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
        Schema::create('syllabus_learning_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_syllabus_id')
                  ->constrained('course_syllabi')
                  ->cascadeOnDelete();
            $table->text('learning_outcomes')->nullable();
            $table->text('topics_readings')->nullable();
            $table->string('schedule')->nullable();
            $table->text('learning_activities')->nullable();
            $table->text('assessment_tools')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_learning_plan_items');
    }
};