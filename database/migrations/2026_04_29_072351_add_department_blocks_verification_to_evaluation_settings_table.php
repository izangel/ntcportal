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
        // Adding the split verification columns
        $table->boolean('shs_blocks_verified')->default(false)->after('period_verified');
        $table->boolean('college_blocks_verified')->default(false)->after('shs_blocks_verified');
    });
}

public function down()
{
    Schema::table('evaluation_settings', function (Blueprint $table) {
        $table->dropColumn(['shs_blocks_verified', 'college_blocks_verified']);
    });
}
};
