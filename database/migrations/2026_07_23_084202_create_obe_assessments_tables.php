<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessment_tasks')) {
            // 1. Assessment Tasks (e.g., Midterm Exam, Quiz 1, Final Project)
            Schema::create('assessment_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->cascadeOnDelete();
                $table->string('title'); // e.g., Midterm Exam
                $table->enum('type', ['Exam', 'Quiz', 'Assignment', 'Project', 'Practical']);
                $table->decimal('weight_percentage', 5, 2); // e.g., 30.00 (%)
                $table->decimal('total_marks', 8, 2); // e.g., 100.00
                $table->timestamps();
            });
        }

        if (Schema::hasTable('assessment_tasks') && !Schema::hasColumn('assessment_tasks', 'effective_batch_year')) {
            Schema::table('assessment_tasks', function (Blueprint $table) {
                $table->string('effective_batch_year', 10)->nullable()->after('course_id');
            });
        }

        if (!Schema::hasTable('assessment_items')) {
            // 2. Assessment Items (Questions / Rubric Criteria mapped to CLOs)
            Schema::create('assessment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_task_id')->constrained()->cascadeOnDelete();
                $table->foreignId('course_learning_outcome_id')->constrained()->cascadeOnDelete();
                $table->string('item_name'); // e.g., Question 1a, Part A, Rubric Item 1
                $table->decimal('max_marks', 8, 2); // e.g., 20.00
                $table->timestamps();
            });
        }

        if (Schema::hasTable('assessment_items') && !Schema::hasColumn('assessment_items', 'effective_batch_year')) {
            Schema::table('assessment_items', function (Blueprint $table) {
                $table->string('effective_batch_year', 10)->nullable()->after('assessment_task_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_items') && Schema::hasColumn('assessment_items', 'effective_batch_year')) {
            Schema::table('assessment_items', function (Blueprint $table) {
                $table->dropColumn('effective_batch_year');
            });
        }

        if (Schema::hasTable('assessment_tasks') && Schema::hasColumn('assessment_tasks', 'effective_batch_year')) {
            Schema::table('assessment_tasks', function (Blueprint $table) {
                $table->dropColumn('effective_batch_year');
            });
        }

        Schema::dropIfExists('assessment_items');
        Schema::dropIfExists('assessment_tasks');
    }
};