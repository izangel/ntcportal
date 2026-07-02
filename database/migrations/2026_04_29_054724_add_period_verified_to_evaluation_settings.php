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
        $table->boolean('period_verified')->default(false)->after('semester');
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
