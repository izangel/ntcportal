<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{Student, CourseBlock, AcademicYear, Section};
use Illuminate\Support\Facades\DB;

class AssignStudentCourseBlock extends Component
{
    public $academic_year_id;
    public $semester;
    public $selected_course_blocks = []; 
    public $target_section_id;
    public $search = '';
    public $student_search = '';
    public $found_students = [];

    public function updatedAcademicYearId() { $this->resetSelection(); }
    public function updatedSemester() { $this->resetSelection(); }
    
// Inside AssignStudentCourseBlock.php
// app/Livewire/AssignStudentCourseBlock.php

public function updatedTargetSectionId($value)
{
    if ($value) {
        // Now that the table is migrated, this query will work
        $this->selected_course_blocks = DB::table('course_block_section')
            ->where('section_id', $value)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('semester', $this->semester)
            ->pluck('course_block_id')
            ->map(fn($id) => (string)$id)
            ->toArray();
    } else {
        $this->selected_course_blocks = [];
    }
}

    private function resetSelection()
    {
        $this->selected_course_blocks = [];
        $this->search = '';
        $this->student_search = '';
        $this->found_students = [];
    }

    public function updatedStudentSearch()
    {
        if (strlen($this->student_search) < 2) {
            $this->found_students = [];
            return;
        }
        $this->found_students = Student::where('last_name', 'like', '%' . $this->student_search . '%')
            ->orWhere('first_name', 'like', '%' . $this->student_search . '%')
            ->orWhere('student_id', 'like', '%' . $this->student_search . '%')
            ->limit(5)->get()->toArray();
    }

    public function render()
    {
        $courseBlocks = collect();
        $sectionTemplateBlocks = collect();

        if ($this->academic_year_id && $this->semester) {
            $courseBlocks = CourseBlock::with(['course', 'faculty'])
                ->where('course_blocks.academic_year_id', $this->academic_year_id)
                ->where('course_blocks.semester', $this->semester)
                ->join('employees', 'course_blocks.faculty_id', '=', 'employees.id')
                ->select('course_blocks.*')
                ->orderBy('employees.last_name', 'asc')
                ->when($this->search, function($query) {
                    $query->whereHas('course', fn($q) => $q->where('code', 'like', '%'.$this->search.'%'))
                          ->orWhereHas('faculty', fn($q) => $q->where('last_name', 'like', '%'.$this->search.'%'));
                })->get();

            if ($this->target_section_id) {
                $sectionTemplateBlocks = CourseBlock::with(['course', 'faculty'])
                    ->whereIn('id', function($query) {
                        $query->select('course_block_id')
                            ->from('course_block_section')
                            ->where('section_id', $this->target_section_id)
                            ->where('academic_year_id', $this->academic_year_id)
                            ->where('semester', $this->semester);
                    })->get();
            }
        }

        return view('livewire.assign-student-course-block', [
            'academicYears' => AcademicYear::all(),
            'semesters'     => ['1st Semester', '2nd Semester', 'Summer'],
            'courseBlocks'  => $courseBlocks,
            'sectionTemplateBlocks' => $sectionTemplateBlocks,
            'sections'      => Section::all(),
            'students'      => $this->getStudentsInTargetSection(),
        ])->extends('layouts.admin')->section('content');
    }

    public function getStudentsInTargetSection()
    {
        if (!$this->target_section_id || !$this->academic_year_id || !$this->semester) {
            return collect();
        }

        // Get the count of blocks in the current template to use as a baseline
        $templateCount = DB::table('course_block_section')
            ->where('section_id', $this->target_section_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('semester', $this->semester)
            ->count();

        return Student::whereHas('sections', function($query) {
            $query->where('section_student.section_id', $this->target_section_id)
                ->where('section_student.academic_year_id', $this->academic_year_id)
                ->where('section_student.semester', $this->semester);
        })
        ->withCount(['courseBlocks' => function($query) {
            // You'll need a 'courseBlocks' relationship in your Student Model
            // or use a raw subquery if you don't have the relationship set up.
        }])
        ->get()
        ->map(function($student) use ($templateCount) {
            // Manually count if relationship isn't perfect
            $actualCount = DB::table('student_courseblock')
                ->where('student_id', $student->id)
                // We should filter by the blocks that belong to the current term
                ->whereIn('course_block_id', function($q) {
                    $q->select('id')->from('course_blocks')
                    ->where('academic_year_id', $this->academic_year_id)
                    ->where('semester', $this->semester);
                })
                ->count();
                
            $student->actual_block_count = $actualCount;
            $student->has_load_mismatch = ($actualCount > $templateCount);
            return $student;
        });
    }

    public function assignRegularSection()
    {
        if (empty($this->selected_course_blocks)) {
            session()->flash('error', 'Please select at least one course block.');
            return;
        }
        $this->validate([
            'target_section_id' => 'required',
            'academic_year_id'  => 'required',
            'semester'          => 'required',
        ]);

        DB::transaction(function () {
            DB::table('course_block_section')
                ->where('section_id', $this->target_section_id)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->whereNotIn('course_block_id', $this->selected_course_blocks)
                ->delete();

            foreach ($this->selected_course_blocks as $blockId) {
                DB::table('course_block_section')->updateOrInsert([
                    'section_id'       => $this->target_section_id,
                    'course_block_id'  => $blockId,
                    'academic_year_id' => $this->academic_year_id,
                    'semester'         => $this->semester,
                ], ['updated_at' => now()]);
            }

            $students = $this->getStudentsInTargetSection();
            foreach ($students as $student) {
                DB::table('student_courseblock')
                    ->where('student_id', $student->id)
                    ->whereIn('course_block_id', function($query) {
                        $query->select('course_block_id')->from('course_block_section')
                            ->where('section_id', $this->target_section_id)
                            ->where('academic_year_id', $this->academic_year_id)
                            ->where('semester', $this->semester);
                    })
                    ->whereNotIn('course_block_id', $this->selected_course_blocks)
                    ->delete();

                foreach ($this->selected_course_blocks as $blockId) {
                    DB::table('student_courseblock')->updateOrInsert([
                        'student_id'      => $student->id,
                        'course_block_id' => $blockId,
                    ]);
                }
            }
        });
        session()->flash('message', 'Section load updated and student schedules synced.');
    }

    /**
     * SMART TRANSFER: Cleans up old section blocks automatically
     */
    public function assignToSectionAndBlocks($studentId)
    {
        DB::transaction(function () use ($studentId) {
            // 1. Identify and Wipe old course blocks belonging to ANY section template for this term
            $oldSectionBlocks = DB::table('student_courseblock')
                ->join('course_block_section', 'student_courseblock.course_block_id', '=', 'course_block_section.course_block_id')
                ->where('student_courseblock.student_id', $studentId)
                ->where('course_block_section.academic_year_id', $this->academic_year_id)
                ->where('course_block_section.semester', $this->semester)
                ->pluck('student_courseblock.course_block_id');

            DB::table('student_courseblock')
                ->where('student_id', $studentId)
                ->whereIn('course_block_id', $oldSectionBlocks)
                ->delete();

            // 2. Wipe the old section link
            DB::table('section_student')
                ->where('student_id', $studentId)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->delete();

            // 3. Assign New Section
            DB::table('section_student')->insert([
                'student_id'       => $studentId,
                'section_id'       => $this->target_section_id,
                'academic_year_id' => $this->academic_year_id,
                'semester'         => $this->semester,
            ]);

            // 4. Enroll in New Blocks
            foreach ($this->selected_course_blocks as $blockId) {
                DB::table('student_courseblock')->updateOrInsert([
                    'student_id'      => $studentId,
                    'course_block_id' => $blockId,
                ]);
            }
        });
        
        $this->student_search = '';
        $this->found_students = [];
        session()->flash('message', 'Student transferred successfully. Old load cleared.');
    }

    public function printClassList()
    {
        if (!$this->target_section_id) {
            session()->flash('error', 'Please select a section first.');
            return;
        }
        return redirect()->route('admin.reports.class-list', [
            'section_id'       => $this->target_section_id,
            'academic_year_id' => $this->academic_year_id,
            'semester'         => $this->semester
        ]);
    }

    public function removeStudent($studentId)
    {
        DB::transaction(function () use ($studentId) {
            $templateBlockIds = DB::table('course_block_section')
                ->where('section_id', $this->target_section_id)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->pluck('course_block_id');

            DB::table('student_courseblock')
                ->where('student_id', $studentId)
                ->whereIn('course_block_id', $templateBlockIds)
                ->delete();

            DB::table('section_student')
                ->where('student_id', $studentId)
                ->where('section_id', $this->target_section_id)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->delete();
        });
        session()->flash('message', 'Student removed from section and synced blocks.');
    }

    public function resetSectionLoad()
    {
        $this->validate([
            'target_section_id' => 'required',
            'academic_year_id'  => 'required',
            'semester'          => 'required',
        ]);

        DB::transaction(function () {
            $templateBlockIds = DB::table('course_block_section')
                ->where('section_id', $this->target_section_id)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->pluck('course_block_id');

            $studentIds = $this->getStudentsInTargetSection()->pluck('id');
            
            DB::table('student_courseblock')
                ->whereIn('student_id', $studentIds)
                ->whereIn('course_block_id', $templateBlockIds)
                ->delete();

            DB::table('course_block_section')
                ->where('section_id', $this->target_section_id)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->delete();
        });

        $this->selected_course_blocks = [];
        session()->flash('message', 'Section load and student schedules completely reset.');
    }

    public function globalSyncCleanup()
    {
        $count = 0;
        
        // 1. Get all section assignments for the current term
        $assignments = DB::table('section_student')
            ->where('academic_year_id', $this->academic_year_id)
            ->where('semester', $this->semester)
            ->get();

        foreach ($assignments as $assign) {
            // 2. Get the "Official" template for this specific student's section
            $officialBlocks = DB::table('course_block_section')
                ->where('section_id', $assign->section_id)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->pluck('course_block_id');

            // 3. Delete any blocks this student has that are NOT in the official template
            // Note: This assumes you want them to ONLY have section blocks.
            $deleted = DB::table('student_courseblock')
                ->where('student_id', $assign->student_id)
                ->whereNotIn('course_block_id', $officialBlocks)
                ->delete();
                
            $count += $deleted;
        }

        session()->flash('message', "Cleanup complete! Removed $count duplicate/orphan records.");
    }

    public function fixSingleStudentLoad($studentId)
    {
        DB::transaction(function () use ($studentId) {
            // 1. Get the official template blocks
            $templateBlockIds = DB::table('course_block_section')
                ->where('section_id', $this->target_section_id)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->pluck('course_block_id');

            // 2. Remove all term-specific blocks the student has
            DB::table('student_courseblock')
                ->where('student_id', $studentId)
                ->whereIn('course_block_id', function($q) {
                    $q->select('id')->from('course_blocks')
                    ->where('academic_year_id', $this->academic_year_id)
                    ->where('semester', $this->semester);
                })
                ->delete();

            // 3. Re-insert only the official ones
            foreach ($templateBlockIds as $id) {
                DB::table('student_courseblock')->insert([
                    'student_id' => $studentId,
                    'course_block_id' => $id
                ]);
            }
        });

        session()->flash('message', 'Student load synchronized to section template.');
    }
}