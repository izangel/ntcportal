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
        Schema::table('section_student', function (Blueprint $table) {
            // This is the column the error is complaining about
            $table->string('status')->nullable()->default('New')->after('semester');
        });
    }

    public function down(): void
    {
        Schema::table('section_student', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
