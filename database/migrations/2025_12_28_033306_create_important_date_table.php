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
        // Schema::create('important_dates', function (Blueprint $table) {
        //     // $table->id();
        //     // $table->string('title');
        //     // $table->text('description')->nullable();
        //     // $table->date('start_date');
        //     // $table->date('end_date')->nullable();
        //     // $table->foreignId('user_id')->constrained(); // Who posted it
        //     // $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('important_dates');
    }
};
