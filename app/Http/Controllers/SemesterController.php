<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\AcademicYear; // Import AcademicYear for dropdowns
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // For unique validation

class SemesterController extends Controller
{
    /**
     * Display a listing of the semesters.
     */
    public function index()
    {
        // Eager load the academic year relationship
        $semesters = Semester::with('academicYear')->orderBy('academic_year_id', 'desc')->orderBy('name')->paginate(10);
        return view('semesters.index', compact('semesters'));
    }

    /**
     * Show the form for creating a new semester.
     */
    public function create()
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get(); // Get all academic years for dropdown
        return view('semesters.create', compact('academicYears'));
    }

    /**
     * Store a newly created semester in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => [
                'required',
                'string',
                'max:255',
                // Semester name must be unique within its academic year
                Rule::unique('semesters')->where(function ($query) use ($request) {
                    return $query->where('academic_year_id', $request->academic_year_id);
                }),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        // If this semester is set as active, deactivate all others within the same academic year
        if (isset($validatedData['is_active']) && $validatedData['is_active']) {
            Semester::where('academic_year_id', $validatedData['academic_year_id'])
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
        }

        Semester::create($validatedData);

        return redirect()->route('semesters.index')->with('success', 'Semester created successfully.');
    }

    /**
     * Display the specified semester. (Optional)
     */
    public function show(Semester $semester)
    {
        // return view('semesters.show', compact('semester'));
        abort(404); // For now, we'll just use index, create, edit.
    }

    /**
     * Show the form for editing the specified semester.
     */
    public function edit(Semester $semester)
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get(); // Get all academic years for dropdown
        return view('semesters.edit', compact('semester', 'academicYears'));
    }

    /**
     * Update the specified semester in storage.
     */
    public function update(Request $request, Semester $semester)
    {
        $validatedData = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => [
                'required',
                'string',
                'max:255',
                // Semester name must be unique within its academic year, ignoring current semester's ID
                Rule::unique('semesters')->where(function ($query) use ($request) {
                    return $query->where('academic_year_id', $request->academic_year_id);
                })->ignore($semester->id),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        // If this semester is set as active, deactivate all others within the same academic year
        if (isset($validatedData['is_active']) && $validatedData['is_active']) {
            Semester::where('academic_year_id', $validatedData['academic_year_id'])
                    ->where('is_active', true)
                    ->where('id', '!=', $semester->id) // Don't deactivate itself
                    ->update(['is_active' => false]);
        }

        $semester->update($validatedData);

        return redirect()->route('semesters.index')->with('success', 'Semester updated successfully.');
    }

    /**
     * Remove the specified semester from storage.
     */
    public function destroy(Semester $semester)
    {
        // You might want to add a check here to prevent deleting if
        // there are associated enrollments.
        if ($semester->enrollments()->count() > 0) {
            return redirect()->route('semesters.index')->with('error', 'Cannot delete Semester with associated Enrollments. Delete enrollments first or reassign them.');
        }

        $semester->delete();

        return redirect()->route('semesters.index')->with('success', 'Semester deleted successfully.');
    }
}