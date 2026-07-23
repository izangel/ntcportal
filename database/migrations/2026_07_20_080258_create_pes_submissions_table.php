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
        Schema::create('pes_submissions', function (Blueprint $table) {
            $table->id();
            // Links to your employees table
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); 
            // Tracking markers from academic_years & course_blocks
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('semester', 50); 
            
            $table->boolean('is_submitted')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('actioned_by_user_id')->nullable()->constrained('users'); // The logged-in admin user account
            $table->timestamps();

            // Unique restraint per employee, per year, per semester
            $table->unique(['employee_id', 'academic_year_id', 'semester'], 'employee_pes_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pes_submissions');
    }
};
