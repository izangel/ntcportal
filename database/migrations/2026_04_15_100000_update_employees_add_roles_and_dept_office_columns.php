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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('roles')->nullable()->constrained('roles')->onDelete('set null');
            $table->foreignId('dept_office')->nullable()->constrained('dept_office')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['roles']);
            $table->dropColumn('roles');
            $table->dropForeign(['dept_office']);
            $table->dropColumn('dept_office');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->enum('role', ['teacher', 'staff', 'academic_head', 'hr', 'admin'])->default('staff');
        });
    }
};
