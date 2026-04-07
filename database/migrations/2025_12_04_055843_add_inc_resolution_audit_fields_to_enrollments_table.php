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
        Schema::table('enrollments', function (Blueprint $table) {
            // Uncommented these lines!
            // Stores the grade *before* it was resolved (should be 'INC')
            $table->string('original_grade', 5)->nullable()->after('grade'); 
            
            // Timestamp of when the resolution occurred
            $table->timestamp('resolution_date')->nullable()->after('original_grade'); 
            
            // ID of the user (teacher/admin) who performed the resolution
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->after('resolution_date'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['resolved_by_user_id']);
            $table->dropColumn(['original_grade', 'resolution_date', 'resolved_by_user_id']);
        });
    }
};