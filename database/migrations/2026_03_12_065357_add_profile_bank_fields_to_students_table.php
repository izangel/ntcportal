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
    Schema::table('students', function (Blueprint $table) {
        // Personal Data
        $table->string('gender')->nullable();
        $table->string('civil_status')->nullable();
        $table->string('card_number')->nullable();
        $table->string('place_birth')->nullable();
        $table->string('current_address')->nullable();
        $table->string('nationality')->default('Filipino');
        $table->string('religion')->nullable();
        $table->string('mobile_number')->nullable();
        $table->string('profile_photo')->nullable();

        // Parents Data
        $table->string('father_name')->nullable();
        $table->string('father_occupation')->nullable();
        $table->string('mother_name')->nullable();
        $table->string('mother_occupation')->nullable();
        $table->string('parent_address')->nullable();
        $table->string('parent_tel')->nullable();

        // Guardian & Admission
        $table->string('guardian_name')->nullable();
        $table->string('guardian_address')->nullable();
        $table->string('guardian_tel')->nullable();
        $table->string('basis_of_admission')->nullable();
        $table->date('date_of_admission')->nullable();
        $table->foreignId('encoded_by')->nullable()->constrained('users');
        $table->foreignId('last_updated_by')->nullable()->constrained('users');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            //
        });
    }
};
