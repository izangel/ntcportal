<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the academic years.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->paginate(10);
        return view('academic_years.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new academic year.
     */
    public function create()
    {
        return view('academic_years.create');
    }

    /**
     * Store a newly created academic year in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'start_year' => 'required|integer|digits:4|unique:academic_years,start_year',
            'end_year' => 'required|integer|digits:4|gt:start_year', // End year must be greater than start year
            'is_active' => 'boolean',
        ]);

        // If this academic year is set as active, deactivate all others
        if (isset($validatedData['is_active']) && $validatedData['is_active']) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        AcademicYear::create($validatedData);

        return redirect()->route('academic_years.index')->with('success', 'Academic Year created successfully.');
    }

    /**
     * Display the specified academic year. (Optional)
     */
    public function show(AcademicYear $academicYear)
    {
        // return view('academic_years.show', compact('academicYear'));
        abort(404); // For now, we'll just use index, create, edit.
    }

    /**
     * Show the form for editing the specified academic year.
     */
    public function edit(AcademicYear $academicYear)
    {
        return view('academic_years.edit', compact('academicYear'));
    }

    /**
     * Update the specified academic year in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validatedData = $request->validate([
            'start_year' => 'required|integer|digits:4|unique:academic_years,start_year,' . $academicYear->id,
            'end_year' => 'required|integer|digits:4|gt:start_year',
            'is_active' => 'boolean',
        ]);

        // If this academic year is set as active, deactivate all others
        if (isset($validatedData['is_active']) && $validatedData['is_active']) {
            AcademicYear::where('is_active', true)
                        ->where('id', '!=', $academicYear->id) // Don't deactivate itself
                        ->update(['is_active' => false]);
        }

        $academicYear->update($validatedData);

        return redirect()->route('academic_years.index')->with('success', 'Academic Year updated successfully.');
    }

    /**
     * Remove the specified academic year from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        // You might want to add a check here to prevent deleting if
        // there are associated semesters or enrollments.
        // If semesters exist, deleting the academic year will cascade delete them due to onDelete('cascade').
        if ($academicYear->semesters()->count() > 0) {
            return redirect()->route('academic_years.index')->with('error', 'Cannot delete Academic Year with associated Semesters. Delete semesters first.');
        }

        $academicYear->delete();

        return redirect()->route('academic_years.index')->with('success', 'Academic Year deleted successfully.');
    }
}