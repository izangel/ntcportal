<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Section; // <-- ADD THIS LINE
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use League\Csv\Reader; 
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index(Request $request)
    {
        $programs = Program::orderBy('name')->get();
        $sections = Section::with('program')->orderBy('name')->get();

        // Eager load sections and their programs through the pivot
        $studentsQuery = Student::with(['user', 'sections.program']);

        // Filter by Program (through sections pivot)
        if ($request->filled('program_id')) {
            $studentsQuery->whereHas('sections.program', function ($query) use ($request) {
                $query->where('programs.id', $request->program_id);
            });
        }

        // Filter by Section (through pivot)
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

    public function export(Request $request)
    {
        $studentsQuery = Student::with(['user', 'sections.program']);

        // Apply same filters as index
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
                // Get the first/current section for the CSV row
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

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $users = User::orderBy('email')->where('role','student')->get();
        // Fetch sections and eager load their programs
        $sections = Section::with('program')->orderBy('name')->get(); // <-- ADD THIS LINE
        return view('students.create', compact('users', 'sections')); // <-- Pass sections
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
             'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'user_id' => 'nullable|exists:users,id',
           // 'section_id' => 'nullable|exists:sections,id', // <-- ADD VALIDATION
            // Add any other student-specific fields (e.g., date_of_birth if you have it)
        ]);

        // Create the user first if user_id is not provided and you intend to create a new user for the student
        // Or handle linking to an existing user if user_id is provided
        // For simplicity, we'll assume user_id is selected or left null for later linking.

        $student = Student::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'user_id' => $validatedData['user_id'] ?? null,
           // 'section_id' => $validatedData['section_id'] ?? null, // <-- SAVE SECTION_ID
        ]);

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        // Eager load section and program for the show view
        $student->load(['section.program']);
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        $users = User::orderBy('email')->where('role','student')->get();
        // Fetch sections and eager load their programs
        $sections = Section::with('program')->orderBy('name')->get(); // <-- ADD THIS LINE
        return view('students.edit', compact('student', 'users', 'sections')); // <-- Pass sections
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($student->user_id, 'id'), // Unique email for users table, ignoring current user
            ],
            'user_id' => 'nullable|exists:users,id',
           // 'section_id' => 'nullable|exists:sections,id', // <-- ADD VALIDATION
            // Add any other student-specific fields (e.g., date_of_birth)
        ]);

        $student->update([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'user_id' => $validatedData['user_id'] ?? null,
           // 'section_id' => $validatedData['section_id'] ?? null, // <-- UPDATE SECTION_ID
        ]);

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    /**
     * Show the form for uploading a CSV file.
     *
     * @return \Illuminate\View\View
     */
    public function showUploadForm()
    {
        return view('students.upload');
    }

    /**
     * Import students from a CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        // 1. Validate the file upload
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        // The rest of your import logic goes here.
        // This code will only run if validation passes.
        
        try {
            $file = $request->file('csv_file');
            $csv = Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();

            $studentsToInsert = [];

            foreach ($records as $record) {
                // Optional: Basic data validation for each row
                if (!isset($record['first_name']) || !isset($record['last_name']) || !isset($record['email'])) {
                    throw new \Exception("Missing required fields in CSV file.");
                }
                
                $studentsToInsert[] = [
                    'first_name' => $record['first_name'],
                    'last_name' => $record['last_name'],
                    'email' => $record['email'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('students')->insert($studentsToInsert);

            return redirect()->back()->with('success', 'Students imported successfully! 🎉');

        } catch (\Exception $e) {
            // If any exception occurs (e.g., file read error, malformed data)
            return redirect()->back()->withErrors(['csv_error' => $e->getMessage()]);
        }
    }
}