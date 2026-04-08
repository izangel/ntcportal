<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('course_evaluations', function (Blueprint $table) {
    //         $table->id();
    //         $table->foreignId('student_id')->constrained();
    //         $table->foreignId('course_id')->constrained();
    //         $table->foreignId('academic_year_id')->constrained();
    //         $table->string('semester'); // '1st', '2nd', etc.
    //         $table->integer('rating'); // 1 to 5
    //         $table->text('comments')->nullable();
    //         $table->timestamps();
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::dropIfExists('course_evaluations');
    // }
};
