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
        Schema::table('sections', function (Blueprint $table) {
            // Add the column as an unsigned big integer
            $table->foreignId('academic_year_id')
                  ->nullable() // Decide if this column can be null
                  ->constrained('academic_years') // Assumes your academic years table is named 'academic_years'
                  ->onDelete('cascade') // Optional: Define what happens on delete
                  ->after('id'); // Optional: Specify the column order
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['academic_year_id']);

            // Then drop the column itself
            $table->dropColumn('academic_year_id');
        });
    }
};