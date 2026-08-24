<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_tasks') && !Schema::hasColumn('assessment_tasks', 'sort_order')) {
            Schema::table('assessment_tasks', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('total_marks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_tasks') && Schema::hasColumn('assessment_tasks', 'sort_order')) {
            Schema::table('assessment_tasks', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};