<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Use change() to modify the existing column.
            // This drops the nullable constraint.
            $table->foreignId('academic_year_id')->nullable(false)->change();
            $table->string('semester', 50)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // On rollback, allow nulls again.
            $table->foreignId('academic_year_id')->nullable()->change();
            $table->string('semester', 50)->nullable()->change();
        });
    }
};