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
    public function index(Request $request)
    {
        $ays = AcademicYear::orderBy('start_year')->get();
        $activeAy = \App\Services\AcademicYearSetup::activeYear();

        // Default the view to the active academic year; allow changing via the filter.
        $defaultAyId = $request->filled('academic_year_id') ? $request->academic_year_id : ($activeAy?->id);

        $sections = Section::with(['program','academicYear'])
            ->when($defaultAyId, fn ($q) => $q->where('academic_year_id', $defaultAyId))
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('program_id')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('sections.index', compact('sections', 'ays', 'activeAy', 'defaultAyId'));
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
     * Show the form for copying sections from another academic year.
     */
    public function copyForm()
    {
        $ays = AcademicYear::orderBy('start_year')->get();
        $activeAy = \App\Services\AcademicYearSetup::activeYear();

        return view('sections.copy', compact('ays', 'activeAy'));
    }

    /**
     * Bulk copy all sections from a source academic year into a target one.
     */
    public function copyStore(Request $request)
    {
        $request->validate([
            'source_academic_year_id' => 'required|exists:academic_years,id|different:target_academic_year_id',
            'target_academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $sourceSections = Section::with('program')
            ->where('academic_year_id', $request->source_academic_year_id)
            ->get();

        if ($sourceSections->isEmpty()) {
            return back()->with('error', 'No sections found in the selected source academic year.');
        }

        $existingKeys = Section::where('academic_year_id', $request->target_academic_year_id)
            ->get()
            ->map(fn ($s) => $s->program_id . '|' . $s->name)
            ->all();

        $created = 0;
        $skipped = 0;

        foreach ($sourceSections as $section) {
            $key = $section->program_id . '|' . $section->name;

            if (in_array($key, $existingKeys, true)) {
                $skipped++;
                continue;
            }

            Section::create([
                'academic_year_id' => $request->target_academic_year_id,
                'program_id' => $section->program_id,
                'name' => $section->name,
            ]);

            $existingKeys[] = $key;
            $created++;
        }

        $message = "Created {$created} section(s) in the target academic year.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} section(s) that already exist there.";
        }

        return redirect()->route('sections.copy.form')->with('success', $message);
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