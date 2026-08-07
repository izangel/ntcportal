<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('course_blocks', 'section_id')) {
            Schema::table('course_blocks', function (Blueprint $table) {
                $table->dropForeign(['section_id']);
                $table->dropColumn('section_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('course_blocks', 'section_id')) {
            Schema::table('course_blocks', function (Blueprint $table) {
                $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('cascade');
            });
        }
    }
};