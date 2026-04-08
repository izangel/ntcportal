<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_courseblocks', function (Blueprint $table) {
        $table->id();

        // 1. Define the column type
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('course_block_id');

        // 2. Add the Foreign Key Constraints
        $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        $table->foreign('course_block_id')->references('id')->on('course_blocks')->onDelete('cascade');

        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('student_courseblocks');
    }
};
