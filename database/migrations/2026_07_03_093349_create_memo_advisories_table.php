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
        Schema::create('memo_advisories', function (Blueprint $table) {
            $table->id();
            $table->string('advisory_no')->unique(); // ADV-0001
            $table->string('to');                    // Target Audience
            $table->string('from');                  // Author / Publisher
            $table->date('date');                    // Issuance date
            $table->string('subject');               // Topic summary
            $table->text('body');                    // Content message
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_advisories');
    }
};