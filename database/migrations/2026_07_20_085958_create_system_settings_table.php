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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed initial default values for the 2nd Semester, SY 2025-2026
        // Assumes your Academic Year ID for 2025-2026 is known, or defaults to 1
        DB::table('system_settings')->insert([
            ['key' => 'pes_dashboard_year_id', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pes_dashboard_semester', 'value' => '2nd', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
