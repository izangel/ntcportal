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
        Schema::table('course_syllabi', function (Blueprint $table) {
            $table->text('course_requirements')->nullable()->after('grading_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_syllabi', function (Blueprint $table) {
            $table->dropColumn('course_requirements');
        });
    }
};
