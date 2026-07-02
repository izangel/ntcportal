<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Section;
use App\Models\Program;
use App\Models\Semester;
use App\Models\AcademicYear;
use App\Models\CourseBlock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use League\Csv\Reader; 
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    /**
     * Helper to ensure semester naming consistency.
     * Converts "First Semester" -> "1st Semester"
     */
    private function formatSemester($semesterName)
    {
        return str_replace(['First', 'Second'], ['1st', '2nd'], $semesterName);
    }

    /**
     * Display a listing of the students.
     */
    public function index(Request $request)
    {
        $context = $this->getCurrentContext();
        $activeAYId = $context['ay']->id ?? null;
        $activeSem = $context['semester'];
        
        // Normalize semester strings for the query
        $currentSem = $this->formatSemester($activeSem);
        $altSem = str_contains($activeSem, 'Second') ? '2nd Semester' : '1st Semester';

        // 1. FILTER DROPDOWN & SIDEBAR (College Only)
        $sections = Section::join('programs', 'sections.program_id', '=', 'programs.id')
            ->where('programs.name', 'NOT LIKE', '%Grade%')
            ->where('programs.name', 'NOT LIKE', '%SHS%')
            ->select('sections.*')
            ->with('program')
            ->get();

        // 2. MAIN STUDENT QUERY
        $studentsQuery = Student::with(['user', 'sections.program'])
            ->join('section_student', function($join) use ($activeAYId, $currentSem) {
                $join->on('students.id', '=', 'section_student.student_id')
                     ->where('section_student.academic_year_id', '=', $activeAYId)
                     ->where('section_student.semester', 'like', $currentSem . '%');
            })
            ->join('sections', 'section_student.section_id', '=', 'sections.id')
            ->join('programs', 'sections.program_id', '=', 'programs.id');

        // Filter out SHS
        $studentsQuery->where('programs.name', 'NOT LIKE', '%Grade%')
                      ->where('programs.name', 'NOT LIKE', '%SHS%');

        if ($request->filled('search')) {
            $search = $request->search;
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('students.first_name', 'like', "%$search%")
                  ->orWhere('students.last_name', 'like', "%$search%")
                  ->orWhere('students.student_id', 'like', "%$search%");
            });
        }

        if ($request->filled('section_id')) {
            $studentsQuery->where('sections.id', $request->section_id);
        }

        $stats = [
            'total' => (clone $studentsQuery)->distinct('students.id')->count(),
        ];

        $students = $studentsQuery
            ->orderByRaw("CASE 
                WHEN programs.name LIKE 'ACT%' THEN 1 
                WHEN programs.name LIKE 'BSIS%' THEN 2 
                ELSE 3 END")
            ->orderBy('programs.name', 'ASC')
            ->orderBy('sections.name', 'ASC')
            ->orderBy('students.last_name', 'ASC')
            ->select('students.*', 'sections.name as sec_name', 'sections.id as sec_id', 'programs.name as prog_name')
            ->distinct()
            ->paginate(100);

        $students->appends($request->all());

        return view('students.index', compact('students', 'sections', 'context', 'stats', 'currentSem'));
    }
public function bulkPromote(Request $request)
{
    $request->validate([
        'student_ids' => 'required|array',
        'target_section_id' => 'required',
        'target_semester' => 'required'
    ]);

    $context = $this->getCurrentContext();
    $ayId = $context['ay']->id;

    try {
        DB::transaction(function () use ($request, $ayId) {
            foreach ($request->student_ids as $id) {
                // We use DB::table to have direct control over the composite key constraints
                DB::table('section_student')->updateOrInsert(
                    [
                        // Search Criteria: Same student in the same Year and Semester
                        'student_id' => $id,
                        'academic_year_id' => $ayId,
                        'semester' => $request->target_semester,
                    ],
                    [
                        // Data to Update or Insert: The new section and status
                        'section_id' => $request->target_section_id,
                        'status' => $request->status ?? 'Regular',
                        'updated_at' => now(),
                        'created_at' => now(), // created_at is ignored on updates in updateOrInsert
                    ]
                );

                // Optional: Re-sync course blocks for the new section
                $blocks = DB::table('course_block_section')
                    ->where('section_id', $request->target_section_id)
                    ->where('academic_year_id', $ayId)
                    ->where('semester', $request->target_semester)
                    ->pluck('course_block_id');

                foreach ($blocks as $blockId) {
                    DB::table('student_courseblock')->updateOrInsert(
                        ['student_id' => $id, 'course_block_id' => $blockId],
                        ['updated_at' => now()]
                    );
                }
            }
        });

        return redirect()->route('students.index')
            ->with('success', count($request->student_ids) . ' students processed successfully.');

    } catch (\Exception $e) {
        // This will now catch actual errors, but ignore "Duplicate" errors because we handle them with updateOrInsert
        return back()->with('error', 'Promotion failed: ' . $e->getMessage());
    }
}

    private function getCurrentContext()
    {
        $activeSemester = Semester::with('academicYear')->where('is_active', true)->first();
        
        if (!$activeSemester) {
            $activeAY = AcademicYear::where('is_active', true)->first();
            return [
                'ay' => $activeAY,
                'semester' => '1st Semester'
            ];
        }

        return [
            'ay' => $activeSemester->academicYear,
            'semester' => $activeSemester->name
        ];
    }

    public function export(Request $request)
    {
        $studentsQuery = Student::with(['user', 'sections.program']);

        if ($request->filled('program_id')) {
            $studentsQuery->whereHas('sections.program', function ($q) use ($request) {
                $q->where('programs.id', $request->program_id);
            });
        }
        if ($request->filled('section_id')) {
            $studentsQuery->whereHas('sections', function ($q) use ($request) {
                $q->where('sections.id', $request->section_id);
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $students = $studentsQuery->get();

        $response = new StreamedResponse(function () use ($students) {
            $csv = Writer::createFromPath('php://output', 'w+');
            $csv->insertOne(['ID', 'Last Name', 'First Name', 'Email', 'Program', 'Section', 'Semester']);

            foreach ($students as $student) {
                $currentSection = $student->sections->first();
                $csv->insertOne([
                    $student->id,
                    $student->last_name,
                    $student->first_name,
                    $student->email,
                    $currentSection->program->name ?? 'N/A',
                    $currentSection->name ?? 'N/A',
                    $currentSection->pivot->semester ?? 'N/A',
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="students_list.csv"');

        return $response;
    }

    public function create()
    {
        $context = $this->getCurrentContext(); 
        $sections = Section::with('program')
            ->join('programs', 'sections.program_id', '=', 'programs.id')
            ->orderBy('programs.name')
            ->orderBy('sections.name')
            ->select('sections.*')
            ->get();

        return view('students.create', compact('context', 'sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'section_id' => 'required',
            'birthday' => 'required|date',
        ]);

        $existingStudent = Student::where('first_name', $request->first_name)
            ->where('last_name', $request->last_name)
            ->where('birthday', $request->birthday)
            ->first();

        if ($existingStudent) {
            return back()->withInput()->with('error', "Duplicate Entry: {$request->first_name} {$request->last_name} already registered.");
        }

        $context = $this->getCurrentContext();
        $ayId = $context['ay']->id;
        $sem = $this->formatSemester($context['semester']);

        DB::transaction(function () use ($request, $ayId, $sem) {
            $student = Student::create([
                'student_id' => $request->student_id ?? $this->generateStudentId(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
                'gender' => $request->gender,
                'birthday' => $request->birthday,
                'requirements_submitted' => $request->requirements, 
            ]);

            DB::table('section_student')->updateOrInsert(
                ['student_id' => $student->id, 'academic_year_id' => $ayId, 'semester' => $sem],
                ['section_id' => $request->section_id, 'status' => 'New', 'updated_at' => now()]
            );

            $templateBlocks = DB::table('course_block_section')
                ->where('section_id', $request->section_id)
                ->where('academic_year_id', $ayId)
                ->where('semester' , $sem)
                ->pluck('course_block_id');

            foreach ($templateBlocks as $blockId) {
                DB::table('student_courseblock')->updateOrInsert([
                    'student_id' => $student->id,
                    'course_block_id' => $blockId,
                ], ['updated_at' => now()]);
            }
        });

        return redirect()->route('students.index')->with('success', 'Student Enrolled and Schedule Synced!');
    }

    private function generateStudentId() {
        $year = date('Y');
        $lastStudent = Student::where('student_id', 'like', "$year%")->latest()->first();
        $number = $lastStudent ? (int)substr($lastStudent->student_id, 5) + 1 : 1;
        return $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function show(Student $student)
    {
        $context = $this->getCurrentContext();
        $ayId = $context['ay']->id;
        $sem = $this->formatSemester($context['semester']);

        $subjects = DB::table('student_courseblock')
            ->join('course_blocks', 'student_courseblock.course_block_id', '=', 'course_blocks.id')
            ->join('courses', 'course_blocks.course_id', '=', 'courses.id')
            ->join('employees', 'course_blocks.faculty_id', '=', 'employees.id')
            ->where('student_courseblock.student_id', $student->id)
            ->where('course_blocks.academic_year_id', $ayId)
            ->where('course_blocks.semester', $sem)
            ->select('courses.code', 'courses.name', 'employees.last_name as instructor', 'course_blocks.schedule_string', 'course_blocks.room_name')
            ->orderBy('courses.code', 'asc')
            ->get();

        return view('students.show', compact('student', 'subjects', 'context', 'sem'));
    }

    public function edit(Student $student)
    {
        $context = $this->getCurrentContext();
        $ayId = $context['ay']->id;
        $sem = $this->formatSemester($context['semester']);

        $currentSectionId = DB::table('section_student')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ayId)
            ->where('semester', $sem)
            ->value('section_id');

        $sections = Section::with('program')->get();
        
        return view('students.edit', compact('student', 'sections', 'currentSectionId', 'context'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'section_id' => 'required',
            'birthday' => 'required|date',
        ]);

        $context = $this->getCurrentContext();
        $ayId = $context['ay']->id;
        $sem = $this->formatSemester($context['semester']);

        DB::transaction(function () use ($request, $student, $ayId, $sem) {
            $student->update([
                'first_name'  => $request->first_name,
                'last_name'   => $request->last_name,
                'middle_name' => $request->middle_name,
                'gender'      => $request->gender,
                'birthday'    => $request->birthday,
                'requirements_submitted' => $request->requirements,
            ]);

            DB::table('section_student')->updateOrInsert(
                ['student_id' => $student->id, 'academic_year_id' => $ayId, 'semester' => $sem],
                ['section_id' => $request->section_id, 'updated_at' => now()]
            );
        });

        return redirect()->route('students.index')->with('success', 'Student record updated.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    public function showUploadForm()
    {
        return view('students.upload');
    }

    public function import(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt|max:2048']);
        
        try {
            $file = $request->file('csv_file');
            $csv = Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();
            $studentsToInsert = [];

            foreach ($records as $record) {
                $studentsToInsert[] = [
                    'first_name' => $record['first_name'],
                    'last_name' => $record['last_name'],
                    'email' => $record['email'],
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
            DB::table('students')->insert($studentsToInsert);
            return redirect()->back()->with('success', 'Imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['csv_error' => $e->getMessage()]);
        }
    }

    public function dashboard()
    {
        $context = $this->getCurrentContext();
        $activeAYId = $context['ay']->id ?? null;

        $totalStudents = Student::whereHas('sections', function($q) use ($activeAYId) {
            $q->where('section_student.academic_year_id', $activeAYId);
        })->count();

        $programStats = Program::withCount(['students' => function($q) use ($activeAYId) {
            $q->where('section_student.academic_year_id', $activeAYId);
        }])->get();

        return view('admin.dashboard', compact('totalStudents', 'programStats', 'context'));
    }

    public function promote(Request $request)
    {
        $studentIds = $request->student_ids;
        $targetSectionId = $request->target_section_id;
        $targetAYId = $request->target_ay_id;
        $targetSem = $request->target_semester;

        foreach($studentIds as $id) {
            $student = Student::find($id);
            $student->sections()->attach($targetSectionId, [
                'academic_year_id' => $targetAYId,
                'semester' => $targetSem,
                'status' => 'Enrolled'
            ]);
        }
        return back()->with('success', count($studentIds) . ' students promoted!');
    }

    public function printCOR(Student $student)
    {
        $context = $this->getCurrentContext();
        $activeAYId = $context['ay']->id;

        $enrollment = $student->sections()
            ->where('section_student.academic_year_id', $activeAYId)
            ->with('program')
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'No active enrollment record found.');
        }

        return view('students.cor', compact('student', 'enrollment', 'context'));
    }

    public function classList($course_block_id, $academic_year_id = null, $semester = null)
    {
        $block = CourseBlock::with(['course', 'faculty', 'academicYear'])->findOrFail($course_block_id);
        $students = Student::join('student_courseblock', 'students.id', '=', 'student_courseblock.student_id')
            ->where('student_courseblock.course_block_id', $course_block_id)
            ->orderBy('students.last_name', 'asc')
            ->select('students.*')
            ->get();

        return view('reports.class-list', compact('block', 'students'));
    }

    public function showPromoteForm(Request $request)
{
    $context = $this->getCurrentContext();
    $sections = Section::with('program')->get();
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

    // 1. Capture filter inputs
    $filterAY = $request->input('filter_ay', $context['ay']->id);
    $selectedSem = $request->input('filter_semester', $this->formatSemester($context['semester']));

    // 2. CREATE VARIATIONS: Catch "1st", "1st Semester", "2nd", "2nd Semester"
    // We strip " Semester" if it exists to get the short version, and add it if it doesn't.
    $shortSem = str_replace(' Semester', '', $selectedSem); // e.g., "1st"
    $longSem = $shortSem . ' Semester';                     // e.g., "1st Semester"
    $semVariations = [$shortSem, $longSem];

    // 3. Query students using whereIn for the variations
    $studentsQuery = Student::whereHas('sections', function($q) use ($filterAY, $semVariations) {
        $q->where('section_student.academic_year_id', $filterAY)
          ->whereIn('section_student.semester', $semVariations); // Catches both formats
    })->with(['sections' => function($q) use ($filterAY, $semVariations) {
        $q->where('section_student.academic_year_id', $filterAY)
          ->whereIn('section_student.semester', $semVariations);
    }]);

    if ($request->filled('section_id')) {
        $studentsQuery->whereHas('sections', function($q) use ($request) {
            $q->where('sections.id', $request->section_id);
        });
    }

    $students = $studentsQuery->orderBy('last_name')->get();

    return view('students.promote', compact('students', 'sections', 'academicYears', 'context', 'filterAY', 'selectedSem'));
}
}