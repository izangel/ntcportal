<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // public function up(): void
    // {
    //     Schema::table('course_evaluations', function (Blueprint $table) {
    //         // Change existing rating to decimal for more accurate averages
    //         $table->decimal('rating', 3, 2)->change(); 
            
    //         // Add the new survey fields
    //         $table->json('ratings')->after('rating')->comment('Stores 15 specific question scores');
    //         $table->text('aspects_helped')->nullable()->after('ratings');
    //         $table->text('aspects_improved')->nullable()->after('aspects_helped');
            
    //         // 'comments' usually exists from our previous steps, 
    //         // but if not, uncomment the line below:
    //         // $table->text('comments')->nullable()->after('aspects_improved');
    //     });
    // }

    // public function down(): void
    // {
    //     Schema::table('course_evaluations', function (Blueprint $table) {
    //         $table->dropColumn(['ratings', 'aspects_helped', 'aspects_improved']);
    //         $table->integer('rating')->change(); // Revert back to integer
    //     });
    // }
};