<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_attainment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_block_id')
                ->constrained('course_blocks')
                ->cascadeOnDelete()
                ->unique();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->json('action_plans')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_attainment_reports');
    }
};
