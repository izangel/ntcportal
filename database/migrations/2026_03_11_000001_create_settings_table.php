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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default candidacy Google Drive link
        \DB::table('settings')->insert([
            'key' => 'candidacy_google_drive_link',
            'value' => 'https://drive.google.com/drive/folders/1ll0nBJvq1a4I1rxezkaNCQO5VWSxI5_F',
            'description' => 'Google Drive folder link for candidacy student ID uploads',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
