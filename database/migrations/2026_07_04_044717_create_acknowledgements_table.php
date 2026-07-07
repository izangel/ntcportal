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
        Schema::create('acknowledgments', function (Blueprint $table) {
            $table->id();

            // Foreign key linking directly to 'id' on your employees table 
            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->onDelete('cascade');

            // ID of the item being acknowledged (e.g., an announcement, memo, or notice)
            $table->foreignId('advisory_no')->constrained('memo_advisories','id')->onDelete('cascade'); 

            // Tracks exactly when they clicked the button
            $table->timestamp('acknowledged_at')->useCurrent();

            // Unique constraint prevents an employee from acknowledging the same item twice
            $table->unique(['employee_id', 'advisory_no']);
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acknowledgments');
    }
};