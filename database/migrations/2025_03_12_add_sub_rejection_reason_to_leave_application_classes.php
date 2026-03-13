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
        Schema::table('leave_application_classes', function (Blueprint $table) {
            // Add column for rejection reason from substitute teacher
            if (!Schema::hasColumn('leave_application_classes', 'sub_rejection_reason')) {
                $table->text('sub_rejection_reason')->nullable()->after('sub_ack_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_application_classes', function (Blueprint $table) {
            if (Schema::hasColumn('leave_application_classes', 'sub_rejection_reason')) {
                $table->dropColumn('sub_rejection_reason');
            }
        });
    }
};
