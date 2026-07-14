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
        // 1. Safe check for your actual 'sections' table layout
        if (Schema::hasTable('sections')) {
            if (!Schema::hasColumn('sections', 'status')) {
                Schema::table('sections', function (Blueprint $table) {
                    // Removed ->after() so MySQL places it safely at the end of the table
                    $table->string('status')->nullable()->default('New');
                });
            }
        }
        
        // 2. Safe check fallback in case 'section_student' is created elsewhere
        if (Schema::hasTable('section_student')) {
            if (!Schema::hasColumn('section_student', 'status')) {
                Schema::table('section_student', function (Blueprint $table) {
                    $table->string('status')->nullable()->default('New');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sections')) {
            if (Schema::hasColumn('sections', 'status')) {
                Schema::table('sections', function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }

        if (Schema::hasTable('section_student')) {
            if (Schema::hasColumn('section_student', 'status')) {
                Schema::table('section_student', function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }
    }
};