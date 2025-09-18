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
            // Column for timestamp of acknowledgment
            // 'sub_ack_at' is short for 'substitute_teacher_acknowledged_at'
            if (!Schema::hasColumn('leave_application_classes', 'sub_ack_at')) {
                $table->timestamp('sub_ack_at')->nullable()->after('acknowledgement_signature');
            }

            // Column for the ID of the employee who acknowledged (the substitute)
            // 'sub_ack_by' is short for 'substitute_teacher_acknowledged_by'
            // Laravel will automatically generate a foreign key name like 'leave_application_classes_sub_ack_by_foreign',
            // which will be well within MySQL's 64-character limit.
            if (!Schema::hasColumn('leave_application_classes', 'sub_ack_by')) {
                $table->foreignId('sub_ack_by') // Shorter column name
                      ->nullable()
                      ->constrained('employees') // Links to the 'employees' table
                      ->onDelete('set null')
                      ->after('sub_ack_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_application_classes', function (Blueprint $table) {
            // Drop in reverse order of creation
            if (Schema::hasColumn('leave_application_classes', 'sub_ack_by')) {
                $table->dropConstrainedForeignId('sub_ack_by');
                $table->dropColumn('sub_ack_by');
            }
            if (Schema::hasColumn('leave_application_classes', 'sub_ack_at')) {
                $table->dropColumn('sub_ack_at');
            }
        });
    }
};