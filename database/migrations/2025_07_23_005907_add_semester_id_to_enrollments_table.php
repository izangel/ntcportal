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
        Schema::table('enrollments', function (Blueprint $table) {
            // Make it nullable initially if you have existing enrollments without semesters,
            // otherwise remove nullable if every enrollment must have a semester.
            $table->foreignId('semester_id')->nullable()->after('course_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
             $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });
    }
};
