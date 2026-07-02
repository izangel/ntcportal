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
        // Adding it as a nullable timestamp
        $table->timestamp('verified_at')->nullable()->after('blocks_verified');
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
