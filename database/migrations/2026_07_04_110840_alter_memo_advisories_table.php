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
        Schema::table('memo_advisories', function (Blueprint $table) {
            // text or json types work great for storing casted array configurations
            $table->text('to')->change(); 
            $table->text('specific_personnel')->nullable()->after('to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memo_advisories', function (Blueprint $table) {
            // Revert 'to' back to a standard string/varchar column
            $table->string('to', 255)->change();
            
            // Drop the specific_personnel column we added
            $table->dropColumn('specific_personnel');
        });
    }
};