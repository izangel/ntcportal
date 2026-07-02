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
        Schema::table('leave_applications', function (Blueprint $BluePrint) {
            // Adds the column after the employee_id and links it to academic_years table
            $BluePrint->foreignId('school_year_id')
                ->nullable() // Allowed nullable in case old data exists
                ->after('employee_id')
                ->constrained('academic_years')
                ->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $BluePrint) {
            // Drops the foreign key constraint first, then the column itself
            $BluePrint->dropForeign(['school_year_id']);
            $BluePrint->dropColumn('school_year_id');
        });
    }
};