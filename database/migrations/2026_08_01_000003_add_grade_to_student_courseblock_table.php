<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_courseblock', function (Blueprint $table) {
            $table->string('grade', 10)->nullable()->after('course_block_id');
            $table->string('remarks', 255)->nullable()->after('grade');
        });
    }

    public function down(): void
    {
        Schema::table('student_courseblock', function (Blueprint $table) {
            $table->dropColumn(['grade', 'remarks']);
        });
    }
};
