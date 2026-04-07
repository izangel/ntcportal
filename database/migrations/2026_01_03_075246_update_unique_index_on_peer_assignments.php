<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // public function up(): void
    // {
    //     // 1. Disable the barrier
    //     Schema::disableForeignKeyConstraints();

    //     Schema::table('peer_assignments', function (Blueprint $table) {
    //         // 2. Drop the old index
    //         $table->dropUnique('unique_peer_assignment');

    //         // 3. Create the new index including the type
    //         $table->unique(
    //             ['teacher_id', 'peer_id', 'academic_year_id', 'semester', 'assignment_type'], 
    //             'unique_peer_assignment'
    //         );
    //     });

    //     // 4. Re-enable the checks
    //     Schema::enableForeignKeyConstraints();
    // }

    // public function down(): void
    // {
    //     Schema::disableForeignKeyConstraints();

    //     Schema::table('peer_assignments', function (Blueprint $table) {
    //         $table->dropUnique('unique_peer_assignment');
    //         $table->unique(
    //             ['teacher_id', 'peer_id', 'academic_year_id', 'semester'], 
    //             'unique_peer_assignment'
    //         );
    //     });

    //     Schema::enableForeignKeyConstraints();
    // }
};
