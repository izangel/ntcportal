<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('section_student', function (Blueprint $table) {
        $table->id();
        $table->foreignId('student_id')->constrained()->onDelete('cascade');
        $table->foreignId('section_id')->constrained()->onDelete('cascade');
        $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
        $table->string('semester'); 
        $table->timestamps();

        // Unique constraint: A student can only be in one section per semester
        $table->unique(['student_id', 'academic_year_id', 'semester'], 'student_term_unique');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_student');
    }
};
