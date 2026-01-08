<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peer_assignments', function (Blueprint $table) {
            // We default to 'peer' so existing records remain valid
            $table->string('assignment_type')->default('peer')->after('peer_id');
        });
    }

    public function down(): void
    {
        Schema::table('peer_assignments', function (Blueprint $table) {
            $table->dropColumn('assignment_type');
        });
    }
};