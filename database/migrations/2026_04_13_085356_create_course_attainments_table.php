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
        Schema::create('course_attainments', function (Blueprint $table) {
            $table->id();
            // Link to the ID from your screenshot
            $table->foreignId('course_session_id')->constrained('course_blocks')->onDelete('cascade');
            $table->string('google_sheet_url');
            $table->enum('status', ['pending', 'submitted', 'reviewed', 'approved'])->default('pending');
            $table->text('remarks')->nullable(); // For Academic Head feedback
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_attainments');
    }
};
