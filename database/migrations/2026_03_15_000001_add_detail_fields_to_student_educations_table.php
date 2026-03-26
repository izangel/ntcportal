<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_educations')) {
            return;
        }

        if (! Schema::hasColumn('student_educations', 'education_group')) {
            Schema::table('student_educations', function (Blueprint $table) {
                $table->string('education_group')->nullable()->after('student_id');
            });
        }

        if (! Schema::hasColumn('student_educations', 'inclusive_dates')) {
            Schema::table('student_educations', function (Blueprint $table) {
                $table->string('inclusive_dates')->nullable()->after('school_name');
            });
        }

        if (! Schema::hasColumn('student_educations', 'date_entered')) {
            Schema::table('student_educations', function (Blueprint $table) {
                $table->date('date_entered')->nullable()->after('inclusive_dates');
            });
        }

        if (! Schema::hasColumn('student_educations', 'date_graduated')) {
            Schema::table('student_educations', function (Blueprint $table) {
                $table->date('date_graduated')->nullable()->after('date_entered');
            });
        }

        if (! Schema::hasColumn('student_educations', 'honors_awards')) {
            Schema::table('student_educations', function (Blueprint $table) {
                $table->string('honors_awards')->nullable()->after('date_graduated');
            });
        }

        if (! Schema::hasColumn('student_educations', 'course_major')) {
            Schema::table('student_educations', function (Blueprint $table) {
                $table->string('course_major')->nullable()->after('honors_awards');
            });
        }

        if (! Schema::hasColumn('student_educations', 'so_number')) {
            Schema::table('student_educations', function (Blueprint $table) {
                $table->string('so_number')->nullable()->after('course_major');
            });
        }

        if (! Schema::hasColumn('student_educations', 'thesis')) {
            Schema::table('student_educations', function (Blueprint $table) {
                $table->string('thesis')->nullable()->after('so_number');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_educations')) {
            return;
        }

        Schema::table('student_educations', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('student_educations', 'education_group') ? 'education_group' : null,
                Schema::hasColumn('student_educations', 'inclusive_dates') ? 'inclusive_dates' : null,
                Schema::hasColumn('student_educations', 'date_entered') ? 'date_entered' : null,
                Schema::hasColumn('student_educations', 'date_graduated') ? 'date_graduated' : null,
                Schema::hasColumn('student_educations', 'honors_awards') ? 'honors_awards' : null,
                Schema::hasColumn('student_educations', 'course_major') ? 'course_major' : null,
                Schema::hasColumn('student_educations', 'so_number') ? 'so_number' : null,
                Schema::hasColumn('student_educations', 'thesis') ? 'thesis' : null,
            ]));

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
