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
            $table->string('ah_status')->default('pending')->after('reason'); // 'pending', 'approved', 'rejected'
            $table->timestamp('ah_approved_at')->nullable()->after('ah_status');
            $table->unsignedBigInteger('ah_approved_by')->nullable()->after('ah_approved_at'); // Employee ID of the Academic Head who approved
            $table->text('ah_remarks')->nullable()->after('ah_approved_by'); // Academic Head's comments

            // Foreign key constraint (optional but recommended)
            $table->foreign('ah_approved_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            // Drop foreign key first if it exists
            $table->dropForeign(['ah_approved_by']);

            $table->dropColumn([
                'ah_status',
                'ah_approved_at',
                'ah_approved_by',
                'ah_remarks',
            ]);
        });
    }
};