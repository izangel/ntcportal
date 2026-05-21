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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    /**
     * The following are the roles used, please refer to it:
     * 1. admin
     * 2. hr
     * 3. academic_head
     * 4. teacher
     * 5. staff
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
