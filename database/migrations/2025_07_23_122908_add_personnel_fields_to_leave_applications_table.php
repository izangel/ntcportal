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
            // Add personnel_to_take_over_id as a foreign key to employees table
            // It's nullable because not all employees (e.g., teachers) might use it
            $table->foreignId('personnel_to_take_over_id')
                  ->nullable()
                  ->constrained('employees') // Assumes your employees table is named 'employees'
                  ->onDelete('set null') // If an employee is deleted, set this field to null
                  ->after('tasks_endorsed'); // Place it after tasks_endorsed

            // Add the signature field for personnel who take over
            $table->string('acknowledgement_personnel_take_over_signature')->nullable()->after('personnel_to_take_over_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['personnel_to_take_over_id']);
            // Then drop the columns
            $table->dropColumn('personnel_to_take_over_id');
            $table->dropColumn('acknowledgement_personnel_take_over_signature');
        });
    }
};