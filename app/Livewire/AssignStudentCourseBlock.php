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

   // Add this property to store the student's current subjects
    public $current_student_load = [];
    public $selected_student_id; // Add this at the top with your other properties



    public function selectStudent($id)
    {
        $this->selected_student_id = $id;
        $this->refreshStudentLoad();
    }
   
    public function updatedTargetSectionId($value)
    {
        // When the section changes, clear the selected student and their load
        $this->selected_student_id = null;
        $this->current_student_load = [];
        
        // Optional: clear search results or other section-specific data
        $this->search = '';

        $this->selected_course_blocks = [];
        if ($value) {
            $this->selected_course_blocks = DB::table('course_block_section')
                ->where('section_id', $value)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->pluck('course_block_id')
                ->map(fn($id) => (string)$id)
                ->toArray();
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
            // 1. Base Query
            $query = CourseBlock::with(['course', 'faculty'])
                ->join('employees', 'course_blocks.faculty_id', '=', 'employees.id')
                ->join('courses', 'course_blocks.course_id', '=', 'courses.id')
                ->where('course_blocks.academic_year_id', $this->academic_year_id)
                ->where('course_blocks.semester', $this->semester)
                ->select('course_blocks.*');

            // 2. Flexible SHS vs College Logic
            if ($this->target_section_id) {
                $section = Section::with('program')->find($this->target_section_id);
                
                if ($section && $section->program) {
                    // Check if program name STARTS with SHS (handles SHS-TVL, SHS-ACAD, etc.)
                    $isSHSSection = str_starts_with(strtoupper($section->program->name), 'SHS');

                    if ($isSHSSection) {
                        // Only show courses where description is 'SHS'
                        $query->where('courses.description', 'SHS');
                    } else {
                        // College: Show courses where description is NOT 'SHS'
                        $query->where(function($q) {
                            $q->where('courses.description', '!=', 'SHS')
                            ->orWhereNull('courses.description');
                        });
                    }
                }
            }

            // 3. Search (using already joined tables for speed)
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('courses.code', 'like', '%' . $this->search . '%')
                    ->orWhere('employees.last_name', 'like', '%' . $this->search . '%');
                });
            }

            // 4. Sort by Faculty Name
            $courseBlocks = $query->orderBy('employees.last_name', 'asc')->get();

            // 5. Cleanup when no section is selected
            if (!$this->target_section_id) {
                $this->selected_student_id = null;
                $this->current_student_load = [];
            }

            // 6. Section Template Blocks (Sorted by Course Code)
            if ($this->target_section_id) {
                $sectionTemplateBlocks = CourseBlock::with(['course', 'faculty'])
                    ->join('courses', 'course_blocks.course_id', '=', 'courses.id')
                    ->whereIn('course_blocks.id', function($query) {
                        $query->select('course_block_id')
                            ->from('course_block_section')
                            ->where('section_id', $this->target_section_id)
                            ->where('academic_year_id', $this->academic_year_id)
                            ->where('semester', $this->semester);
                    })
                    ->orderBy('courses.code', 'asc')
                    ->select('course_blocks.*')
                    ->get();
            }
        }

        $contextLabel = 'Select a Section';
        if ($this->target_section_id) {
            $section = Section::with('program')->find($this->target_section_id);
            if ($section && $section->program) {
                $contextLabel = str_starts_with(strtoupper($section->program->name), 'SHS') ? 'SHS' : 'College';
            }
        }

        return view('livewire.assign-student-course-block', [
            'contextLabel' => $contextLabel,
            'academicYears' => AcademicYear::all(),
            'semesters'     => ['1st Semester', '2nd Semester', 'Summer'],
            'courseBlocks'  => $courseBlocks,
            'sectionTemplateBlocks' => $sectionTemplateBlocks,
            'sections'      => Section::with('program')->get(), // Added with('program') for efficiency
            'students'      => $this->getStudentsInTargetSection(),
        ])->extends('layouts.plain')->section('content');
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

    public function assignToSectionAndBlocks($studentId)
{
    DB::transaction(function () use ($studentId) {
        // 1. Identify the student's CURRENT section before changing it
        $currentSectionId = DB::table('section_student')
            ->where('student_id', $studentId)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('semester', $this->semester)
            ->value('section_id');

        // 2. If they have an old section, ONLY wipe subjects from THAT section
        if ($currentSectionId) {
            $oldSectionBlocks = DB::table('course_block_section')
                ->where('section_id', $currentSectionId)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('semester', $this->semester)
                ->pluck('course_block_id');

            DB::table('student_courseblock')
                ->where('student_id', $studentId)
                ->whereIn('course_block_id', $oldSectionBlocks)
                ->delete();
        }

        // 3. Update/Assign New Section Link
        DB::table('section_student')->updateOrInsert(
            ['student_id' => $studentId, 'academic_year_id' => $this->academic_year_id, 'semester' => $this->semester],
            ['section_id' => $this->target_section_id]
        );

        // 4. Enroll in New Blocks (The ones you selected in the UI)
        foreach ($this->selected_course_blocks as $blockId) {
            DB::table('student_courseblock')->updateOrInsert([
                'student_id'      => $studentId,
                'course_block_id' => $blockId,
            ]);
        }
    });
    
    $this->reset(['student_search', 'found_students']);
    session()->flash('message', 'Student moved successfully. Irregular subjects from other sections were preserved.');
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

    //for irregular students
  public function refreshStudentLoad()
{
    if (!$this->selected_student_id) {
        $this->current_student_load = [];
        return;
    }

    $this->current_student_load = DB::table('student_courseblock')
        ->join('course_blocks', 'student_courseblock.course_block_id', '=', 'course_blocks.id')
        ->join('courses', 'course_blocks.course_id', '=', 'courses.id')
        ->join('employees', 'course_blocks.faculty_id', '=', 'employees.id')
        ->where('student_courseblock.student_id', $this->selected_student_id)
        ->where('course_blocks.academic_year_id', $this->academic_year_id)
        ->where('course_blocks.semester', $this->semester)
        ->select(
            'courses.code', 
            'employees.first_name', 
            'employees.last_name', 
            'course_blocks.id', 
            'course_blocks.schedule_string', 
            'course_blocks.room_name'
        )
        ->orderBy('courses.code', 'asc') // This ensures Eng1 comes first if it's the lowest code
        ->get()
        ->toArray();
}



    public function addSingleBlockToStudent($blockId)
    {
        if (!$this->selected_student_id) {
            session()->flash('error', 'Please select a student first.');
            return;
        }

        DB::table('student_courseblock')->updateOrInsert([
            'student_id'      => $this->selected_student_id,
            'course_block_id' => $blockId,
        ], ['updated_at' => now()]);

        $this->refreshStudentLoad(); // This updates the "Confirmed Enrollment Load" list
        session()->flash('message', 'Subject added successfully!');
    }
    public function removeSingleBlock($blockId)
    {
        if (!$this->selected_student_id) return;

        // Remove the link between the student and this specific block
        DB::table('student_courseblock')
            ->where('student_id', $this->selected_student_id)
            ->where('course_block_id', $blockId)
            ->delete();

        // Refresh the list to show the change in the UI
        $this->refreshStudentLoad();

        session()->flash('message', 'Subject removed from student load.');
    }

    public $source_section_id;
    public function bulkEnrollFromSection()
    {
        $this->validate([
            'source_section_id' => 'required',
            'target_section_id' => 'required',
            'academic_year_id'  => 'required',
            'semester'          => 'required',
        ]);

        // 1. Determine the "Previous" semester logic
        // If current is 2nd Sem, previous was 1st Sem of the same Academic Year
        $prevSemester = ($this->semester == '2nd Semester') ? '1st Semester' : null;
        
        if (!$prevSemester) {
            session()->flash('error', 'Bulk enroll only works from 1st to 2nd Semester.');
            return;
        }

        // 2. Get all Student IDs from the source section in the previous semester
        $studentIds = DB::table('section_student')
            ->where('section_id', $this->source_section_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('semester', $prevSemester)
            ->pluck('student_id');

        if ($studentIds->isEmpty()) {
            session()->flash('error', 'No students found in the source section for the previous semester.');
            return;
        }

        // 3. Process each student using our "Smart Transfer" logic
        $count = 0;
        DB::transaction(function () use ($studentIds, &$count) {
            foreach ($studentIds as $id) {
                $this->assignToSectionAndBlocks($id);
                $count++;
            }
        });

        session()->flash('message', "Successfully enrolled $count students into {$this->semester}.");
    }


}