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
        $table->foreignId('course_block_id')->constrained()->onDelete('cascade');
        $table->foreignId('section_id')->constrained()->onDelete('cascade');
        // These match the 'where' clauses in your current code
        $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
        $table->string('semester'); 
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('course_block_section');
    }
};