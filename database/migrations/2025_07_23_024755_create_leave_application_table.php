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
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // Employee applying for leave

            $table->enum('leave_type', ['service_incentive_leave', 'sick_leave', 'vacation_leave', 'other']);
            $table->text('reason');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days'); // Calculated from start_date and end_date

            $table->timestamp('date_filed')->useCurrent(); // Date application was created/filed

            // Workflow fields (nullable as they are filled during approval steps)
            $table->timestamp('academic_head_noted_at')->nullable();
            $table->timestamp('hr_recommended_at')->nullable();
            $table->timestamp('administrator_approved_at')->nullable();

            // Approval Status
            $table->enum('approval_status', ['pending', 'noted_by_academic_head', 'recommended_by_hr', 'approved_with_pay', 'approved_without_pay', 'rejected'])->default('pending');
            $table->text('comments')->nullable(); // General comments on approval/rejection

            // Specific fields for Teachers
            $table->json('classes_to_miss')->nullable(); // Stores array of objects: {course_code, title, day/time/room, topics, substitute_teacher}
            $table->string('acknowledgement_subject_teacher')->nullable(); // Name or ID of acknowledged teacher

            // Specific fields for Staff
            $table->text('tasks_endorsed')->nullable(); // List of works/tasks endorsed
            $table->string('personnel_to_take_over')->nullable(); // Name or ID of personnel taking over
            $table->string('acknowledgement_personnel_take_over')->nullable(); // Name or ID of acknowledged personnel

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};