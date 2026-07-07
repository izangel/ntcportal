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
        // Only attempt to create the column and constraint if it doesn't already exist
        if (!Schema::hasColumn('sections', 'academic_year_id')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->foreignId('academic_year_id')
                      ->nullable() 
                      ->constrained('academic_years') 
                      ->onDelete('cascade') 
                      ->after('id'); 
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only attempt to drop the foreign key and column if it actually exists
        if (Schema::hasColumn('sections', 'academic_year_id')) {
            Schema::table('sections', function (Blueprint $table) {
                // Drop the foreign key constraint first
                $table->dropForeign(['academic_year_id']);

                // Then drop the column itself
                $table->dropColumn('academic_year_id');
            });
        }
    }
};