<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_syllabi', function (Blueprint $table) {
            $table->timestamp('program_head_reviewed_at')->nullable()->after('submitted_at');
            $table->unsignedBigInteger('program_head_reviewed_by_id')->nullable()->after('program_head_reviewed_at');
            $table->string('program_head_reviewed_by_name')->nullable()->after('program_head_reviewed_by_id');
            $table->timestamp('academic_head_approved_at')->nullable()->after('program_head_reviewed_by_name');
            $table->unsignedBigInteger('academic_head_approved_by_id')->nullable()->after('academic_head_approved_at');
            $table->string('academic_head_approved_by_name')->nullable()->after('academic_head_approved_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('course_syllabi', function (Blueprint $table) {
            $table->dropColumn([
                'program_head_reviewed_at',
                'program_head_reviewed_by_id',
                'program_head_reviewed_by_name',
                'academic_head_approved_at',
                'academic_head_approved_by_id',
                'academic_head_approved_by_name',
            ]);
        });
    }
};