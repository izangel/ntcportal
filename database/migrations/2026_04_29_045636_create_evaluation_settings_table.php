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
        Schema::create('evaluation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year'); 
            $table->string('semester');
            $table->boolean('is_active')->default(true);
            // The "Gatekeeper" column
            $table->boolean('blocks_verified')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_settings');
    }
};
