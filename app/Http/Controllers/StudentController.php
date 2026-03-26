<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Section;
use App\Models\Program;
use App\Models\StudentDocument;
use App\Models\StudentEducation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Added for photo handling
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    /**
     * Display a searchable listing specifically for the Student Profile Data Bank.
     */
    public function profileBank(Request $request)
    {
        $search = $request->input('search');

        $students = Student::query()
            ->when($search, function ($query, $search) {
                return $query->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('student_id', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(15);

        return view('students.profile_bank', compact('students'));
    }

    /**
     * Display a listing of the students (Manage Students).
     */
    public function index(Request $request)
    {
        $programs = Program::orderBy('name')->get();
        $sections = Section::with('program')->orderBy('name')->get();

        $studentsQuery = Student::with(['user', 'sections.program']);

        if ($request->filled('program_id')) {
            $studentsQuery->whereHas('sections.program', function ($query) use ($request) {
                $query->where('programs.id', $request->program_id);
            });
        }

        if ($request->filled('section_id')) {
            $studentsQuery->whereHas('sections', function ($query) use ($request) {
                $query->where('sections.id', $request->section_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
            });
        }

        $students = $studentsQuery->paginate(10);
        $students->appends($request->only(['program_id', 'section_id', 'search']));

        return view('students.index', compact('students', 'programs', 'sections'));
    }

    /**
     * Display the specified student profile details.
     */
    public function show(Student $student, Request $request)
    {
        // Eager load details for the current student
        $student->load(['sections.program', 'education', 'documents', 'encoder', 'updater']);

        // Fetch the list for the sidebar search table
        $search = $request->input('search_table');
        $studentList = Student::with(['sections.program'])
            ->when($search, function ($query, $search) {
                return $query->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('student_id', 'like', "%{$search}%");
            })
            ->paginate(10, ['*'], 'student_list');

        return view('students.show', compact('student', 'studentList'));
    }

    /**
     * CRUD - Store Student Document (Part of Documents Upload Tab).
     */
    public function storeDocument(Request $request, Student $student)
    {
        $request->validate([
            'document_name' => 'required|string|max:255',
            'document_file' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($request->hasFile('document_file')) {
            $path = $request->file('document_file')->store('student_docs', 'public');

            $student->documents()->create([
                'document_name' => $request->document_name,
                'file_path' => $path,
            ]);
        }

        return back()->with('success', 'Document added to profile.');
    }

    /**
     * CRUD - Delete Student Document.
     */
    public function destroyDocument(StudentDocument $document)
    {
        // Delete the file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
        return back()->with('success', 'Document deleted.');
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
                    $student->student_id,
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

    public function exportEducation(Request $request)
    {
        $students = Student::with(['education', 'documents', 'sections.program'])
            ->when($request->filled('student_id'), function ($query) use ($request) {
                $query->where('id', $request->input('student_id'));
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $response = new StreamedResponse(function () use ($students) {
            echo '<html><head><meta charset="UTF-8">';
            echo '<style>';
            echo 'table{border-collapse:collapse;width:100%;font-family:Arial,sans-serif;font-size:11px;}';
            echo 'th,td{border:1px solid #9ca3af;padding:6px;vertical-align:top;}';
            echo '.title{background:#1f4e78;color:#ffffff;font-weight:700;font-size:14px;text-align:center;}';
            echo '.header{background:#dbeafe;font-weight:700;text-align:center;}';
            echo '.wrap{white-space:normal;}';
            echo '.bullet-list{margin:0;padding-left:18px;}';
            echo '.bullet-list li{margin:0 0 4px 0;}';
            echo '.edu-section-title{font-weight:700;margin:4px 0 2px 0;}';
            echo '</style></head><body>';
            echo '<table>';
            echo '<colgroup>';
            echo '<col style="width:90px">';
            echo '<col style="width:120px">';
            echo '<col style="width:120px">';
            echo '<col style="width:110px">';
            echo '<col style="width:180px">';
            echo '<col style="width:100px">';
            echo '<col style="width:90px">';
            echo '<col style="width:90px">';
            echo '<col style="width:120px">';
            echo '<col style="width:220px">';
            echo '<col style="width:150px">';
            echo '<col style="width:100px">';
            echo '<col style="width:100px">';
            echo '<col style="width:100px">';
            echo '<col style="width:120px">';
            echo '<col style="width:110px">';
            echo '<col style="width:130px">';
            echo '<col style="width:130px">';
            echo '<col style="width:130px">';
            echo '<col style="width:130px">';
            echo '<col style="width:200px">';
            echo '<col style="width:120px">';
            echo '<col style="width:130px">';
            echo '<col style="width:200px">';
            echo '<col style="width:120px">';
            echo '<col style="width:140px">';
            echo '<col style="width:120px">';
            echo '<col style="width:350px">';
            echo '<col style="width:220px">';
            echo '</colgroup>';

            echo '<tr><th colspan="29" class="title">STUDENT PROFILE DATA BANK</th></tr>';
            echo '<tr>';
            echo '<th class="header">Student ID</th>';
            echo '<th class="header">Last Name</th>';
            echo '<th class="header">First Name</th>';
            echo '<th class="header">Middle Name</th>';
            echo '<th class="header">Email</th>';
            echo '<th class="header">Date of Birth</th>';
            echo '<th class="header">Gender</th>';
            echo '<th class="header">Civil Status</th>';
            echo '<th class="header">Mobile Number</th>';
            echo '<th class="header">Current Address</th>';
            echo '<th class="header">Place of Birth</th>';
            echo '<th class="header">Nationality</th>';
            echo '<th class="header">Religion</th>';
            echo '<th class="header">Program</th>';
            echo '<th class="header">Section</th>';
            echo '<th class="header">Card Number</th>';
            echo '<th class="header">Father Name</th>';
            echo '<th class="header">Father Occupation</th>';
            echo '<th class="header">Mother Name</th>';
            echo '<th class="header">Mother Occupation</th>';
            echo '<th class="header">Parent Address</th>';
            echo '<th class="header">Parent Tel</th>';
            echo '<th class="header">Guardian Name</th>';
            echo '<th class="header">Guardian Address</th>';
            echo '<th class="header">Guardian Tel</th>';
            echo '<th class="header">Basis of Admission</th>';
            echo '<th class="header">Date of Admission</th>';
            echo '<th class="header">Education Details</th>';
            echo '<th class="header">Document Details</th>';
            echo '</tr>';

            foreach ($students as $student) {
                $currentSection = $student->sections->first();

                $primarySecondaryLevels = [
                    'Nursery Education',
                    'Junior Kinder Education',
                    'Senior Kinder Education',
                    'Primary Education',
                    'Intermediate Education',
                    'Secondary Education',
                ];

                $higherEducationLevels = [
                    'Bacaluarte/ Teritary',
                    'Masteral / Graduate',
                    'Doctorate / Post Graduate',
                ];

                $primarySecondaryEducation = $student->education
                    ->where('education_group', 'primary_secondary')
                    ->keyBy('level');

                $higherEducation = $student->education
                    ->where('education_group', 'higher_education')
                    ->keyBy('level');

                $hasEducation = $student->education->isNotEmpty();
                if ($hasEducation) {
                    $primarySecondaryItems = collect($primarySecondaryLevels)
                        ->map(function ($level) use ($primarySecondaryEducation) {
                            $edu = $primarySecondaryEducation->get($level);
                            if (!$edu) {
                                return null;
                            }

                            $segments = [
                                '<strong>'.e($level).'</strong>',
                                'School: '.e($edu->school_name ?: 'N/A'),
                                'Inclusive Dates: '.e($edu->inclusive_dates ?: 'N/A'),
                                'Date Entered: '.e(optional($edu->date_entered)->format('Y-m-d') ?: 'N/A'),
                                'Date Graduated: '.e(optional($edu->date_graduated)->format('Y-m-d') ?: 'N/A'),
                                'Honors/Awards: '.e($edu->honors_awards ?: 'N/A'),
                            ];

                            return '<li>'.implode(' | ', $segments).'</li>';
                        })
                        ->filter()
                        ->values();

                    $higherEducationItems = collect($higherEducationLevels)
                        ->map(function ($level) use ($higherEducation) {
                            $edu = $higherEducation->get($level);
                            if (!$edu) {
                                return null;
                            }

                            $segments = [
                                '<strong>'.e($level).'</strong>',
                                'School: '.e($edu->school_name ?: 'N/A'),
                                'Course/Major: '.e($edu->course_major ?: 'N/A'),
                                'Date Graduated: '.e(optional($edu->date_graduated)->format('Y-m-d') ?: 'N/A'),
                                'SO No.: '.e($edu->so_number ?: 'N/A'),
                                'Thesis: '.e($edu->thesis ?: 'N/A'),
                            ];

                            return '<li>'.implode(' | ', $segments).'</li>';
                        })
                        ->filter()
                        ->values();

                    $educationSections = [];

                    if ($primarySecondaryItems->isNotEmpty()) {
                        $educationSections[] = '<div class="edu-section-title">Primary and Secondary Education</div>';
                        $educationSections[] = '<ul class="bullet-list">'.$primarySecondaryItems->implode('').'</ul>';
                    }

                    if ($higherEducationItems->isNotEmpty()) {
                        $educationSections[] = '<div class="edu-section-title">College / Graduate / Post Graduate Education</div>';
                        $educationSections[] = '<ul class="bullet-list">'.$higherEducationItems->implode('').'</ul>';
                    }

                    $educationDetails = implode('', $educationSections);
                } else {
                    $educationDetails = 'N/A';
                }

                $documentDetails = $student->documents
                    ->map(function ($document) {
                        return $document->document_name;
                    })
                    ->filter()
                    ->implode(' || ');

                echo '<tr>';
                echo '<td>'.e($student->student_id).'</td>';
                echo '<td>'.e($student->last_name).'</td>';
                echo '<td>'.e($student->first_name).'</td>';
                echo '<td>'.e($student->middle_name).'</td>';
                echo '<td>'.e($student->email).'</td>';
                echo '<td>'.e($student->date_of_birth).'</td>';
                echo '<td>'.e($student->gender).'</td>';
                echo '<td>'.e($student->civil_status).'</td>';
                echo '<td>'.e($student->mobile_number).'</td>';
                echo '<td class="wrap">'.e($student->current_address).'</td>';
                echo '<td>'.e($student->place_birth).'</td>';
                echo '<td>'.e($student->nationality).'</td>';
                echo '<td>'.e($student->religion).'</td>';
                echo '<td>'.e($currentSection->program->name ?? null).'</td>';
                echo '<td>'.e($currentSection->name ?? null).'</td>';
                echo '<td>'.e($student->card_number).'</td>';
                echo '<td>'.e($student->father_name).'</td>';
                echo '<td>'.e($student->father_occupation).'</td>';
                echo '<td>'.e($student->mother_name).'</td>';
                echo '<td>'.e($student->mother_occupation).'</td>';
                echo '<td class="wrap">'.e($student->parent_address).'</td>';
                echo '<td>'.e($student->parent_tel).'</td>';
                echo '<td>'.e($student->guardian_name).'</td>';
                echo '<td class="wrap">'.e($student->guardian_address).'</td>';
                echo '<td>'.e($student->guardian_tel).'</td>';
                echo '<td>'.e($student->basis_of_admission).'</td>';
                echo '<td>'.e($student->date_of_admission).'</td>';
                echo '<td class="wrap">'.$educationDetails.'</td>';
                echo '<td class="wrap">'.e($documentDetails).'</td>';
                echo '</tr>';
            }

            echo '</table></body></html>';
        });

        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="student_full_profile_export.xls"');

        return $response;
    }

    public function create()
    {
        $users = User::orderBy('email')->where('role','student')->get();
        $sections = Section::with('program')->orderBy('name')->get();
        return view('students.create', compact('users', 'sections'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'user_id' => 'nullable|exists:users,id',
            'student_id' => 'required|string|unique:students,student_id',
        ]);

        $student = Student::create($validatedData);

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    public function edit(Student $student, Request $request)
    {
        $student->load('education');

        $users = User::orderBy('email')->where('role','student')->get();
        $sections = Section::with('program')->orderBy('name')->get();

        // Added studentList for the sidebar search table in the Edit view
        $search = $request->input('search_table');
        $studentList = Student::with(['sections.program'])
            ->when($search, function ($query, $search) {
                return $query->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('student_id', 'like', "%{$search}%");
            })
            ->paginate(10, ['*'], 'student_list');

        return view('students.edit', compact('student', 'users', 'sections', 'studentList'));
    }

    public function update(Request $request, Student $student)
    {
        $validatedData = $request->validate([
            // Personal Data
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student->user_id, 'id')],
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'civil_status' => 'nullable|string',
            'card_number' => 'nullable|string',
            'place_birth' => 'nullable|string',
            'current_address' => 'nullable|string',
            'nationality' => 'nullable|string',
            'religion' => 'nullable|string',
            'mobile_number' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Parents Data
            'father_name' => 'nullable|string',
            'father_occupation' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'mother_occupation' => 'nullable|string',
            'parent_address' => 'nullable|string',
            'parent_tel' => 'nullable|string',

            // Guardian Data
            'guardian_name' => 'nullable|string',
            'guardian_address' => 'nullable|string',
            'guardian_tel' => 'nullable|string',
            'basis_of_admission' => 'nullable|string',
            'date_of_admission' => 'nullable|date',
            'section_id' => 'nullable|exists:sections,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // HANDLE PROFILE PHOTO UPLOAD
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($student->profile_photo && Storage::disk('public')->exists($student->profile_photo)) {
                Storage::disk('public')->delete($student->profile_photo);
            }

            // Store new photo
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $validatedData['profile_photo'] = $path;
        }

        // Track who updated it
        $validatedData['last_updated_by'] = Auth::id();

        $student->update($validatedData);

        return redirect()->route('students.show', $student)->with('success', 'Student Profile fully updated.');
    }

    public function editEducation(Student $student)
    {
        $student->load('education');

        return view('students.edit_education', compact('student'));
    }

    public function updateEducation(Request $request, Student $student)
    {
        $primarySecondaryLevels = [
            'Nursery Education',
            'Junior Kinder Education',
            'Senior Kinder Education',
            'Primary Education',
            'Intermediate Education',
            'Secondary Education',
        ];

        $higherEducationLevels = [
            'Bacaluarte/ Teritary',
            'Masteral / Graduate',
            'Doctorate / Post Graduate',
        ];

        $request->validate([
            'primary_secondary_education' => 'required|array|size:6',
            'primary_secondary_education.*.id' => 'nullable|integer|exists:student_educations,id',
            'primary_secondary_education.*.level' => ['required', Rule::in($primarySecondaryLevels)],
            'primary_secondary_education.*.school_name' => 'nullable|string|max:255',
            'primary_secondary_education.*.inclusive_dates' => 'nullable|string|max:255',
            'primary_secondary_education.*.date_entered' => 'nullable|date',
            'primary_secondary_education.*.date_graduated' => 'nullable|date',
            'primary_secondary_education.*.honors_awards' => 'nullable|string|max:255',

            'higher_education' => 'required|array|size:3',
            'higher_education.*.id' => 'nullable|integer|exists:student_educations,id',
            'higher_education.*.level' => ['required', Rule::in($higherEducationLevels)],
            'higher_education.*.school_name' => 'nullable|string|max:255',
            'higher_education.*.course_major' => 'nullable|string|max:255',
            'higher_education.*.date_graduated' => 'nullable|date',
            'higher_education.*.so_number' => 'nullable|string|max:255',
            'higher_education.*.thesis' => 'nullable|string|max:255',
        ]);

        $primarySecondaryRows = collect($request->input('primary_secondary_education', []))->keyBy('level');
        foreach ($primarySecondaryLevels as $level) {
            $row = $primarySecondaryRows->get($level, []);

            $hasPrimarySecondaryData = filled($row['school_name'] ?? null)
                || filled($row['inclusive_dates'] ?? null)
                || filled($row['date_entered'] ?? null)
                || filled($row['date_graduated'] ?? null)
                || filled($row['honors_awards'] ?? null);

            $existing = $student->education()
                ->where('education_group', 'primary_secondary')
                ->where('level', $level)
                ->first();

            if (! $hasPrimarySecondaryData) {
                if ($existing) {
                    $existing->delete();
                }

                continue;
            }

            $rowData = [
                'education_group' => 'primary_secondary',
                'level' => $level,
                'school_name' => $row['school_name'] ?? null,
                'inclusive_dates' => $row['inclusive_dates'] ?? null,
                'date_entered' => $row['date_entered'] ?? null,
                'date_graduated' => $row['date_graduated'] ?? null,
                'honors_awards' => $row['honors_awards'] ?? null,
                'course_major' => null,
                'so_number' => null,
                'thesis' => null,
                'year_graduated' => $row['date_graduated'] ?? null,
            ];

            if ($existing) {
                $existing->update($rowData);
            } else {
                $student->education()->create($rowData);
            }
        }

        $student->education()
            ->where('education_group', 'primary_secondary')
            ->whereNotIn('level', $primarySecondaryLevels)
            ->delete();

        $higherEducationRows = collect($request->input('higher_education', []))->keyBy('level');
        foreach ($higherEducationLevels as $level) {
            $row = $higherEducationRows->get($level, []);

            $hasHigherEducationData = filled($row['school_name'] ?? null)
                || filled($row['course_major'] ?? null)
                || filled($row['date_graduated'] ?? null)
                || filled($row['so_number'] ?? null)
                || filled($row['thesis'] ?? null);

            $existing = $student->education()
                ->where('education_group', 'higher_education')
                ->where('level', $level)
                ->first();

            if (! $hasHigherEducationData) {
                if ($existing) {
                    $existing->delete();
                }

                continue;
            }

            $rowData = [
                'education_group' => 'higher_education',
                'level' => $level,
                'school_name' => $row['school_name'] ?? null,
                'inclusive_dates' => null,
                'date_entered' => null,
                'date_graduated' => $row['date_graduated'] ?? null,
                'honors_awards' => null,
                'course_major' => $row['course_major'] ?? null,
                'so_number' => $row['so_number'] ?? null,
                'thesis' => $row['thesis'] ?? null,
                'year_graduated' => $row['date_graduated'] ?? null,
            ];

            if ($existing) {
                $existing->update($rowData);
            } else {
                $student->education()->create($rowData);
            }
        }

        $student->education()
            ->where('education_group', 'higher_education')
            ->whereNotIn('level', $higherEducationLevels)
            ->delete();

        $student->update([
            'last_updated_by' => Auth::id(),
        ]);

        return redirect()->route('students.show', ['student' => $student, 'tab' => 'education'])
            ->with('success', 'Education details updated successfully.');
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

            $studentsToInsert = [];
            foreach ($csv->getRecords() as $record) {
                $studentsToInsert[] = [
                    'first_name' => $record['first_name'],
                    'last_name' => $record['last_name'],
                    'email' => $record['email'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('students')->insert($studentsToInsert);
            return redirect()->back()->with('success', 'Students imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['csv_error' => $e->getMessage()]);
        }
    }
}
