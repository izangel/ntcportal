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
        Schema::create('clearance_college', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

            // Registrar
            $table->string('registrar_status')->nullable();
            $table->foreignId('registrar_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('registrar_approved_at')->nullable();
            $table->text('registrar_remarks')->nullable();

            // Guidance
            $table->string('guidance_status')->nullable();
            $table->foreignId('guidance_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('guidance_approved_at')->nullable();
            $table->text('guidance_remarks')->nullable();

            // SAO
            $table->string('sao_status')->nullable();
            $table->foreignId('sao_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('sao_approved_at')->nullable();
            $table->text('sao_remarks')->nullable();

            // Lab
            $table->string('lab_status')->nullable();
            $table->foreignId('lab_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('lab_approved_at')->nullable();
            $table->text('lab_remarks')->nullable();

            // SSC
            $table->string('ssc_status')->nullable();
            $table->foreignId('ssc_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('ssc_approved_at')->nullable();
            $table->text('ssc_remarks')->nullable();

            // Librarian
            $table->string('librarian_status')->nullable();
            $table->foreignId('librarian_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('librarian_approved_at')->nullable();
            $table->text('librarian_remarks')->nullable();

            // POD
            $table->string('pod_status')->nullable();
            $table->foreignId('pod_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('pod_approved_at')->nullable();
            $table->text('pod_remarks')->nullable();

            // DSAS
            $table->string('dsas_status')->nullable();
            $table->foreignId('dsas_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('dsas_approved_at')->nullable();
            $table->text('dsas_remarks')->nullable();

            // AH
            $table->string('ah_status')->nullable();
            $table->foreignId('ah_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('ah_approved_at')->nullable();
            $table->text('ah_remarks')->nullable();

            // Admin
            $table->string('admin_status')->nullable();
            $table->foreignId('admin_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('admin_approved_at')->nullable();
            $table->text('admin_remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearance_college');
    }
};
