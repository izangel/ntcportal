<?php

namespace App\Http\Controllers;

use App\Models\Program; // Import the Program model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // To use unique validation rule

class ProgramController extends Controller
{
    /**
     * Display a listing of the programs.
     */
    public function index()
    {
        $programs = Program::paginate(10); // Paginate programs, 10 per page
        return view('programs.index', compact('programs'));
    }

    /**
     * Show the form for creating a new program.
     */
    public function create()
    {
        return view('programs.create');
    }

    /**
     * Store a newly created program in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:programs,name', // Program names must be unique
        ]);

        Program::create($validatedData);

        return redirect()->route('programs.index')->with('success', 'Program created successfully.');
    }

    /**
     * Display the specified program.
     */
    public function show(Program $program)
    {
        // This method is optional if you only need index, create, edit.
        // But it can be used to show program details and list associated sections/students later.
        return view('programs.show', compact('program'));
    }

    /**
     * Show the form for editing the specified program.
     */
    public function edit(Program $program)
    {
        return view('programs.edit', compact('program'));
    }

    /**
     * Update the specified program in storage.
     */
    public function update(Request $request, Program $program)
    {
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('programs')->ignore($program->id), // Ignore current program's ID for unique check
            ],
        ]);

        $program->update($validatedData);

        return redirect()->route('programs.index')->with('success', 'Program updated successfully.');
    }

    /**
     * Remove the specified program from storage.
     */
    public function destroy(Program $program)
    {
        // Consider what happens to associated sections when a program is deleted
        // onDelete('cascade') in migration handles child sections, but you might want to prevent deletion if sections exist.
        $program->delete();

        return redirect()->route('programs.index')->with('success', 'Program deleted successfully.');
    }
}