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
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->boolean('is_half_day')->default(false);
            $table->decimal('total_days', 5, 2)->default(0)->change();
        });

        Schema::table('leave_credits', function (Blueprint $table) {
            $table->decimal('sick_leave', 5, 2)->default(15)->change();
            $table->decimal('vacation_leave', 5, 2)->default(15)->change();
            $table->decimal('service_incentive_leave', 5, 2)->default(15)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn('is_half_day');
            $table->integer('total_days')->default(0)->change();
        });

        Schema::table('leave_credits', function (Blueprint $table) {
            $table->integer('sick_leave')->default(15)->change();
            $table->integer('vacation_leave')->default(15)->change();
            $table->integer('service_incentive_leave')->default(15)->change();
        });
    }
};
