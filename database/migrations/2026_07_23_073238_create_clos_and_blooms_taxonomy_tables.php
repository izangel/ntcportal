<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bloom's Taxonomy Reference Table
        Schema::create('blooms_taxonomies', function (Blueprint $table) {
            $table->id();
            $table->enum('domain', ['Cognitive', 'Affective', 'Psychomotor']);
            $table->string('code'); // e.g., C1, C2, A1, P1
            $table->string('level'); // e.g., Remembering, Applying, Evaluating
            $table->text('action_verbs'); // e.g., "Analyze, Calculate, Compare"
            $table->timestamps();
        });

        // 2. Course Learning Outcomes (CLOs)
        Schema::create('course_learning_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blooms_taxonomy_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // e.g., CLO-01
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_learning_outcomes');
        Schema::dropIfExists('blooms_taxonomies');
    }
};