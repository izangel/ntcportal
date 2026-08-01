<?php

namespace App\Livewire\CourseMaterials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\CourseMaterial;

class StudentCourseMaterials extends Component
{
    public $selectedAcademicYear;
    public $selectedSemester = '1st';
    public $semesterOptions = ['1st', '2nd Semester', 'Summer'];

    public function mount()
    {
        $activeAY = AcademicYear::where('is_active', true)->first();

        $this->selectedAcademicYear = $activeAY ? $activeAY->id : AcademicYear::orderBy('start_year', 'desc')->value('id');
    }

    public function render()
    {
        $student = Auth::user()->student;
        $blocks = collect();
        $totalMaterials = 0;

        if ($student && $this->selectedAcademicYear) {
            $studentBlockIds = $student->courseBlocks()
                ->where('course_blocks.academic_year_id', $this->selectedAcademicYear)
                ->where('course_blocks.semester', $this->selectedSemester)
                ->pluck('course_blocks.id');

            $materials = CourseMaterial::whereIn('course_block_id', $studentBlockIds)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('course_block_id');

            $blocks = \App\Models\CourseBlock::with(['course', 'faculty', 'academicYear'])
                ->whereIn('id', $studentBlockIds)
                ->orderBy('course_blocks.id')
                ->get()
                ->map(function ($block) use ($materials) {
                    $blockMaterials = $materials->get($block->id, collect());

                    return [
                        'block_id' => $block->id,
                        'course_code' => $block->course?->code,
                        'course_name' => $block->course?->name,
                        'faculty_name' => $block->faculty ? trim($block->faculty->first_name . ' ' . $block->faculty->last_name) : 'TBA',
                        'schedule_string' => $block->schedule_string,
                        'room_name' => $block->room_name,
                        'materials' => $blockMaterials->values()->map(fn ($m) => [
                            'id' => $m->id,
                            'type' => $m->type,
                            'type_label' => CourseMaterial::typeLabel($m->type),
                            'type_icon' => CourseMaterial::typeIcon($m->type),
                            'title' => $m->title,
                            'url' => $m->url,
                        ]),
                    ];
                })
                ->filter(fn ($block) => count($block['materials']) > 0)
                ->values();

            $totalMaterials = $blocks->sum(fn ($block) => count($block['materials']));
        }

        return view('livewire.course-materials.student-course-materials', [
            'academicYears' => AcademicYear::orderBy('start_year', 'desc')->get(),
            'blocks' => $blocks,
            'totalMaterials' => $totalMaterials,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
