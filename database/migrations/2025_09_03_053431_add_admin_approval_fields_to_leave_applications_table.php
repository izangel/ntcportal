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
             // 'status' column is the general initial status (e.g., 'pending' when first submitted).
            // 'ah_status' will track the Academic Head's decision.
            $table->string('admin_status')->default('pending')->after('hr_approved_by'); // 'pending', 'approved', 'rejected'
            $table->timestamp('admin_approved_at')->nullable()->after('admin_status');
            $table->unsignedBigInteger('admin_approved_by')->nullable()->after('admin_approved_at'); // Employee ID of the Admin who approved
            $table->text('admin_remarks')->nullable()->after('admin_approved_by'); // Admin's comments

            // Foreign key constraint (optional but recommended)
            $table->foreign('admin_approved_by')->references('id')->on('employees')->onDelete('set null');
        });

    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            // Drop foreign key first if it exists
            $table->dropForeign(['admin_approved_by']);

            $table->dropColumn([
                'admin_status',
                'admin_approved_at',
                'admin_approved_by',
                'admin_remarks',
            ]);

        });
    }
};
