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
            // Uncommented these lines so the column actually gets created!
            $table->foreignId('academic_year_id')
                  ->nullable() 
                  ->constrained('academic_years') 
                  ->onDelete('cascade') 
                  ->after('id'); 
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