<?php

namespace App\Http\Controllers;

use App\Models\Section;    // Import the Section model
use App\Models\Program;    // Import the Program model for dropdowns
use App\Models\AcademicYear; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // To use unique validation rule

class SectionController extends Controller
{
    /**
     * Display a listing of the sections.
     */
    public function index()
    {
        // Eager load the program relationship to avoid N+1 query problem
        $sections = Section::with(['program','academicYear'])->paginate(10);
        return view('sections.index', compact('sections'));
    }

    /**
     * Show the form for creating a new section.
     */
    public function create()
    {
        $ays = AcademicYear::orderBy('start_year')->get(); // Get all programs for the dropdown
        $programs = Program::orderBy('name')->get(); // Get all programs for the dropdown
        return view('sections.create', compact('programs','ays'));
    }

    /**
     * Store a newly created section in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'academic_year_id' =>  'required|exists:academic_years,id',
            'program_id' => 'required|exists:programs,id', // Ensure selected program exists
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique rule: section name must be unique *within* the selected program
                Rule::unique('sections')->where(function ($query) use ($request) {
                    return $query->where('program_id', $request->program_id);
                }),
            ],
        ]);

        Section::create($validatedData);

        return redirect()->route('sections.index')->with('success', 'Section created successfully.');
    }

    /**
     * Display the specified section. (Optional, can be used later if needed)
     */
    public function show(Section $section)
    {
        // You can add logic here if you want a dedicated 'show' page for a section
        // return view('sections.show', compact('section'));
        abort(404); // For now, we'll just use index, create, edit.
    }

    /**
     * Show the form for editing the specified section.
     */
    public function edit(Section $section)
    {
         $ays = AcademicYear::orderBy('start_year')->get();
        $programs = Program::orderBy('name')->get(); // Get all programs for the dropdown
        return view('sections.edit', compact('section', 'programs', 'ays'));
    }

    /**
     * Update the specified section in storage.
     */
    public function update(Request $request, Section $section)
    {
        $validatedData = $request->validate([
             'academic_year_id' => 'required|exists:academic_years,id',
            'program_id' => 'required|exists:programs,id',
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique rule: ignore current section's ID but still unique within its program
                Rule::unique('sections')->where(function ($query) use ($request) {
                    return $query->where('program_id', $request->program_id);
                })->ignore($section->id),
            ],
        ]);

        $section->update($validatedData);

        return redirect()->route('sections.index')->with('success', 'Section updated successfully.');
    }

    /**
     * Remove the specified section from storage.
     */
    public function destroy(Section $section)
    {
        // Note: If you have students linked to this section,
        // the onDelete('set null') in your migration will set their section_id to NULL.
        // If you want to prevent deletion if students exist, you'd add a check here.
        $section->delete();

        return redirect()->route('sections.index')->with('success', 'Section deleted successfully.');
    }
}