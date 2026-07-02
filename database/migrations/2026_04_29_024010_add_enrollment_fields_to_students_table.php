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
            // Personal Details
            $table->string('gender')->nullable()->after('middle_name');
            $table->date('birthday')->nullable()->after('gender');
            
            // Registrar Enrollment Fields
            $table->json('requirements_submitted')->nullable()->after('birthday');
            $table->boolean('is_fully_enrolled')->default(false)->after('requirements_submitted');
            
            // Optional: Status (Regular, Irregular, New)
            $table->string('enrollment_status')->default('New')->after('is_fully_enrolled');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'gender', 
                'birthday', 
                'requirements_submitted', 
                'is_fully_enrolled',
                'enrollment_status'
            ]);
        });
    }
};
