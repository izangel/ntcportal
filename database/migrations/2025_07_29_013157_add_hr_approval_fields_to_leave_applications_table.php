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
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->string('hr_status')->default('pending')->after('approval_status'); // e.g., 'pending', 'approved', 'rejected'
            $table->timestamp('hr_approved_at')->nullable()->after('hr_status');
            $table->unsignedBigInteger('hr_approved_by')->nullable()->after('hr_approved_at'); // Employee ID of the HR who approved
            $table->text('hr_remarks')->nullable()->after('hr_approved_by'); // HR's comments for rejection/approval

            // Foreign key constraint (optional but recommended)
            $table->foreign('hr_approved_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            // Drop foreign key first if it exists
            $table->dropForeign(['hr_approved_by']);

            $table->dropColumn([
                'hr_status',
                'hr_approved_at',
                'hr_approved_by',
                'hr_remarks',
            ]);
        });
    }
};