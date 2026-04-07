<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('peer_assignments', function (Blueprint $table) {
    //     $table->id();
        
    //     // The Teacher being evaluated
    //     $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
        
    //     // The Peer assigned to do the evaluating
    //     $table->foreignId('peer_id')->constrained('users')->onDelete('cascade');
        
    //     $table->foreignId('academic_year_id')->constrained();
    //     $table->string('semester');
        
    //     // Track if the evaluation has been submitted
    //     $table->boolean('is_completed')->default(false);
    //     $table->timestamp('completed_at')->nullable();

    //     $table->timestamps();

    //     // Prevent HR from assigning the same peer to the same teacher twice in one term
    //     $table->unique(['teacher_id', 'peer_id', 'academic_year_id', 'semester'], 'unique_peer_assignment');
    // });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::dropIfExists('peer_assignments');
    // }
};
