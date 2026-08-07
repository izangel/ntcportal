<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('syllabus_grading_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_syllabus_id')
                  ->constrained('course_syllabi')
                  ->cascadeOnDelete();
            $table->string('assessment_type');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabus_grading_components');
    }
};