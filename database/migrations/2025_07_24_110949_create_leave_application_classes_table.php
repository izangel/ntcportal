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
        Schema::create('leave_application_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_application_id')->constrained('leave_applications')->onDelete('cascade');
            $table->string('course_code')->nullable();
            $table->string('title')->nullable();
            $table->string('day_time_room')->nullable();
            $table->text('topics')->nullable();
            $table->foreignId('substitute_teacher_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('acknowledgement_signature')->nullable();
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_application_classes');
    }
};