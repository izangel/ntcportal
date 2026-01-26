<?php

namespace App\Http\Controllers;

use App\Models\{Employee, AcademicYear, PeerAssignment};
use Illuminate\Http\Request;

class PeerAssignmentController extends Controller
{
    public function index()
{
    $employees = Employee::orderBy('last_name')->get();
    $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
    
   $assignments = PeerAssignment::query()
    ->peer()
    ->with(['teacher', 'peer', 'academicYear'])
    ->join('employees', 'peer_assignments.teacher_id', '=', 'employees.id')
    ->orderBy('employees.last_name', 'asc')
    ->orderBy('employees.first_name', 'asc')
    ->select('peer_assignments.*')
    ->paginate(15);

    // Points to resources/views/hr/assignment/index.blade.php
    return view('hr.assignment.index', compact('employees', 'academicYears', 'assignments'));
}

public function store(Request $request)
{
    $request->validate([
        'teacher_id' => 'required|exists:employees,id',
        'peer_ids' => 'required|array',
        'peer_ids.*' => 'exists:employees,id|different:teacher_id',
        'academic_year_id' => 'required|exists:academic_years,id',
        'semester' => 'required|string',
    ]);

    foreach ($request->peer_ids as $peerId) {
        PeerAssignment::updateOrCreate([
            'teacher_id' => $request->teacher_id,
            'peer_id' => $peerId,
            'academic_year_id' => $request->academic_year_id,
            'semester' => $request->semester,
        ]);
    }

    return redirect()->route('hr.peer-assignments.index')
        ->with('success', 'Peer assignments successfully created.');
}
    public function destroy(PeerAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Assignment removed.');
    }
}