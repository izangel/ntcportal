<?php

// database/migrations/YYYY_MM_DD_HHMMSS_add_finalized_to_course_blocks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::table('course_blocks', function (Blueprint $table) {
        //     // Add a boolean column, defaulting to false (not finalized)
        //     $table->boolean('finalized')->default(false)->after('schedule_string');
        // });
    }

    public function down(): void
    {
        Schema::table('course_blocks', function (Blueprint $table) {
            $table->dropColumn('finalized');
        });
    }
};