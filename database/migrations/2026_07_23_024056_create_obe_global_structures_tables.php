<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Institutional Goals (Mission/Vision Alignment)
        Schema::create('institutional_goals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., IG-01
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Program Educational Objectives (3-5 years post-graduation)
        Schema::create('peos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // e.g., PEO-01
            $table->text('description');
            $table->timestamps();
        });

        // 3. Program Outcomes / Program Learning Outcomes (At graduation)
        Schema::create('program_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // e.g., PO-01
            $table->text('description');
            $table->timestamps();
        });

        // Pivot Table: Map POs to PEOs
        Schema::create('peo_program_outcome', function (Blueprint $table) {
            $table->foreignId('peo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_outcome_id')->constrained()->cascadeOnDelete();
            $table->primary(['peo_id', 'program_outcome_id']);
        });

        // Pivot Table: Map PEOs to Institutional Goals
        Schema::create('institutional_goal_peo', function (Blueprint $table) {
            $table->foreignId('institutional_goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('peo_id')->constrained()->cascadeOnDelete();
            $table->primary(['institutional_goal_id', 'peo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutional_goal_peo');
        Schema::dropIfExists('peo_program_outcome');
        Schema::dropIfExists('program_outcomes');
        Schema::dropIfExists('peos');
        Schema::dropIfExists('institutional_goals');
    }
};