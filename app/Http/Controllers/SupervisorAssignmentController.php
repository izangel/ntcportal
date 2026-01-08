<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PeerAssignment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class SupervisorAssignmentController extends Controller
{
    /**
     * Display the assignment interface.
     */
    public function index()
    {
        // Fetch all employees for the dropdowns
        $employees = Employee::orderBy('last_name')->get();
        
        // Fetch active academic years
        $academicYears = AcademicYear::all();

        // Fetch existing supervisor assignments using the 'supervisor' scope
        // We join with employees to allow sorting by the subordinate's last name
        $assignments = PeerAssignment::query()
            ->supervisor()
            ->with(['teacher', 'peer', 'academicYear'])
            ->join('employees', 'peer_assignments.teacher_id', '=', 'employees.id')
            ->select('peer_assignments.*')
            ->orderBy('employees.last_name', 'asc')
            ->paginate(15);

        return view('hr.supervisor-assignment.index', compact('employees', 'academicYears', 'assignments'));
    }

    /**
     * Store or Update a supervisor assignment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:employees,id',
            'supervisor_id' => 'required|exists:employees,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|string',
        ]);

        // Prevent self-evaluation through the supervisor route
        if ($request->teacher_id == $request->supervisor_id) {
            return back()->with('error', 'An employee cannot be their own supervisor.');
        }

        // updateOrCreate ensures one subordinate has only one supervisor per term
        PeerAssignment::updateOrCreate(
            [
                'teacher_id' => $request->teacher_id,
                'academic_year_id' => $request->academic_year_id,
                'semester' => $request->semester,
                'assignment_type' => 'supervisor'
            ],
            [
                'peer_id' => $request->supervisor_id,
                'is_completed' => false // Reset status if supervisor is changed
            ]
        );

        return redirect()->route('hr.supervisor-assignments.index')
            ->with('success', 'Supervisor assigned successfully.');
    }

    /**
     * Remove the assignment.
     */
    public function destroy(PeerAssignment $assignment)
    {
        // Ensure we are only deleting supervisor-type assignments here
        if ($assignment->assignment_type !== 'supervisor') {
            return back()->with('error', 'Unauthorized deletion attempt.');
        }

        $assignment->delete();

        return redirect()->route('hr.supervisor-assignments.index')
            ->with('success', 'Assignment removed successfully.');
    }
}