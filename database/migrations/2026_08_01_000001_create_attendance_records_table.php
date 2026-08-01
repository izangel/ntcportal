<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_block_id')->constrained('course_blocks')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status')->default('present');
            $table->timestamp('checked_in_at')->nullable();
            $table->string('token')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['course_block_id', 'student_id', 'attendance_date'], 'attendance_block_student_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
