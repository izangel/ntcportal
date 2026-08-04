<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_syllabi', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')->nullable()->after('course_block_id');
        });

        DB::table('course_syllabi')->get()->each(function ($syllabus) {
            $block = DB::table('course_blocks')->where('id', $syllabus->course_block_id)->first();
            if (!$block) {
                return;
            }

            $programId = $this->resolveProgramId($block);
            if ($programId) {
                DB::table('course_syllabi')->where('id', $syllabus->id)->update(['program_id' => $programId]);
            }
        });

        Schema::table('course_syllabi', function (Blueprint $table) {
            $table->foreign('program_id')->references('id')->on('programs')->nullOnDelete();
            $table->unique(['course_block_id', 'program_id'], 'course_syllabi_block_program_unique');
        });
    }

    /**
     * Resolve the owning program for a block: course program_id FK, then the
     * distinct program of its sections (pivot, falling back to legacy section_id).
     */
    private function resolveProgramId(object $block): ?int
    {
        $course = DB::table('courses')->where('id', $block->course_id)->first();
        if ($course && $course->program_id) {
            return (int) $course->program_id;
        }

        $pivotPrograms = DB::table('course_block_section as cbs')
            ->join('sections as s', 's.id', '=', 'cbs.section_id')
            ->where('cbs.course_block_id', $block->id)
            ->pluck('s.program_id')
            ->filter()
            ->unique()
            ->values();

        if ($pivotPrograms->count() === 1) {
            return (int) $pivotPrograms->first();
        }

        if ($block->section_id) {
            $programId = DB::table('sections')->where('id', $block->section_id)->value('program_id');
            if ($programId) {
                return (int) $programId;
            }
        }

        return null;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_syllabi', function (Blueprint $table) {
            $table->dropUnique('course_syllabi_block_program_unique');
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
    }
};
