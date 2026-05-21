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
        Schema::create('dept_office', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    /**
     * The following are the names for the dept_office used for the clearance, 
     * input directly at the database:
     * 1. Registrar's Office
     * 2. Guidance Office
     * 3. SHS Adviser
     * 4. Student's Accounts Office
     * 5. Laboratory In-charge
     * 6. SHS Organization
     * 7. Supreme Student Government (SSG)
     * 8. Supreme Student Council (SSC)
     * 9. Librarian
     * 10. Prefect of Discipline
     * 11. SHS Coordinator
     * 12. Director for Student Affairs and Services
     * 13. Director of Academic Affairs
     * 14. School Administrator
     */
    public function down(): void
    {
        Schema::dropIfExists('dept_office');
    }
};
