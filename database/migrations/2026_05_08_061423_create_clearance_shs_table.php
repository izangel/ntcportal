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
        Schema::create('clearance_shs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('registrar_status')->nullable();
            $table->foreignId('registrar_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('registrar_approved_at')->nullable();
            $table->text('registrar_remarks')->nullable();
            $table->string('guidance_status')->nullable();
            $table->foreignId('guidance_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('guidance_approved_at')->nullable();
            $table->text('guidance_remarks')->nullable();
            $table->string('adviser_status')->nullable();
            $table->foreignId('adviser_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('adviser_approved_at')->nullable();
            $table->text('adviser_remarks')->nullable();
            $table->string('sao_status')->nullable();
            $table->foreignId('sao_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('sao_approved_at')->nullable();
            $table->text('sao_remarks')->nullable();
            $table->string('lab_status')->nullable();
            $table->foreignId('lab_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('lab_approved_at')->nullable();
            $table->text('lab_remarks')->nullable();
            $table->string('org_status')->nullable();
            $table->foreignId('org_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('org_approved_at')->nullable();
            $table->text('org_remarks')->nullable();
            $table->string('ssg_status')->nullable();
            $table->foreignId('ssg_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('ssg_approved_at')->nullable();
            $table->text('ssg_remarks')->nullable();
            $table->string('librarian_status')->nullable();
            $table->foreignId('librarian_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('librarian_approved_at')->nullable();
            $table->text('librarian_remarks')->nullable();
            $table->string('pod_status')->nullable();
            $table->foreignId('pod_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('pod_approved_at')->nullable();
            $table->text('pod_remarks')->nullable();
            $table->string('coordinator_status')->nullable();
            $table->foreignId('coordinator_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('coordinator_approved_at')->nullable();
            $table->text('coordinator_remarks')->nullable();
            $table->string('dsas_status')->nullable();
            $table->foreignId('dsas_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('dsas_approved_at')->nullable();
            $table->text('dsas_remarks')->nullable();
            $table->string('ah_status')->nullable();
            $table->foreignId('ah_approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('ah_approved_at')->nullable();
            $table->text('ah_remarks')->nullable();
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
        Schema::dropIfExists('clearance_shs');
    }
};
