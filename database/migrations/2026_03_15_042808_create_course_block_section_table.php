<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('course_block_section', function (Blueprint $table) {
        $table->id();
        // The foreign keys
        $table->foreignId('course_block_id')->constrained()->onDelete('cascade');
        $table->foreignId('section_id')->constrained()->onDelete('cascade');
        $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
        
        // The semester string (e.g., "2nd Semester")
        $table->string('semester'); 
        
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('course_block_section');
    }
};