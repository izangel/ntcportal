<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
//     public function up(): void
// {
//     Schema::table('students', function (Blueprint $table) {
//         // Adding middle_name as a string, making it nullable 
//         // because not everyone has a middle name.
//         $table->string('middle_name')->nullable()->after('last_name');
//     });
// }

// /**
//  * Reverse the migrations.
//  */
// public function down(): void
// {
//     Schema::table('students', function (Blueprint $table) {
//         // Always drop the column in the down method
//         $table->dropColumn('middle_name');
//     });
// }
};
