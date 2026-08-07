<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_syllabi', function (Blueprint $table) {
            $table->timestamp('revision_requested_at')->nullable()->after('academic_head_approved_by_name');
            $table->unsignedBigInteger('revision_requested_by_id')->nullable()->after('revision_requested_at');
            $table->string('revision_requested_by_name')->nullable()->after('revision_requested_by_id');
            $table->text('revision_remarks')->nullable()->after('revision_requested_by_name');
        });
    }

    public function down(): void
    {
        Schema::table('course_syllabi', function (Blueprint $table) {
            $table->dropColumn([
                'revision_requested_at',
                'revision_requested_by_id',
                'revision_requested_by_name',
                'revision_remarks',
            ]);
        });
    }
};