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
        Schema::create('evaluation_process', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->string('semester');      // e.g., "1st Semester"
            $table->integer('step')->default(1); // 1: Setup, 2: Blocks, 3: Enrollment, 4: Loading
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_process');
    }
};
