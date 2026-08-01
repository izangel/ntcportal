<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stores raw marks achieved by students per assessment item
        Schema::create('student_assessment_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assessment_item_id')->constrained('assessment_items')->cascadeOnDelete();
            $table->decimal('marks_obtained', 8, 2);
            $table->timestamps();

            $table->unique(['student_id', 'assessment_item_id'], 'student_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_assessment_marks');
    }
};