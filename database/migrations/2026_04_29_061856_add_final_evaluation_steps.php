<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('evaluation_settings', function (Blueprint $table) {
        $table->boolean('loading_verified')->default(false)->after('students_verified');
        $table->boolean('evaluations_opened')->default(false)->after('loading_verified');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_settings', function (Blueprint $table) {
            //
        });
    }
};
