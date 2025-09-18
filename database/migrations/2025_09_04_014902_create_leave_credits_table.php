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
       Schema::create('leave_credits', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('employee_id');
        $table->integer('sick_leave')->default(15);
        $table->integer('vacation_leave')->default(15);
        $table->integer('service_incentive_leave')->default(15);
        $table->string('academic_year'); // e.g. "2024-2025"
        $table->timestamps();

        $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_credits');
    }
};
