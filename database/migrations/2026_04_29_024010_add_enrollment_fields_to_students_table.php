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
            // 1. Inject middle_name if your application logic or forms expect it
            if (!Schema::hasColumn('students', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }

            // 2. Add gender dynamically safely checking the anchor column
            if (!Schema::hasColumn('students', 'gender')) {
                $anchor = Schema::hasColumn('students', 'middle_name') ? 'middle_name' : 'last_name';
                $table->string('gender')->nullable()->after($anchor);
            }
            
            // NOTE: 'birthday' column skipped because 'date_of_birth' already exists in the base table.

            // 3. Add Registrar Enrollment Fields cleanly if they don't exist
            if (!Schema::hasColumn('students', 'requirements_submitted')) {
                $table->json('requirements_submitted')->nullable()->after('date_of_birth');
            }

            if (!Schema::hasColumn('students', 'is_fully_enrolled')) {
                $table->boolean('is_fully_enrolled')->default(false)->after('requirements_submitted');
            }
            
            if (!Schema::hasColumn('students', 'enrollment_status')) {
                $table->string('enrollment_status')->default('New')->after('is_fully_enrolled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('students', 'middle_name')) $columnsToDrop[] = 'middle_name';
            if (Schema::hasColumn('students', 'gender')) $columnsToDrop[] = 'gender';
            if (Schema::hasColumn('students', 'requirements_submitted')) $columnsToDrop[] = 'requirements_submitted';
            if (Schema::hasColumn('students', 'is_fully_enrolled')) $columnsToDrop[] = 'is_fully_enrolled';
            if (Schema::hasColumn('students', 'enrollment_status')) $columnsToDrop[] = 'enrollment_status';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};