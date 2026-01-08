<?php

namespace App\Http\Controllers;

use App\Models\{PeerAssignment, Evaluation, Semester};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeerEvaluationController extends Controller
{
    /**
     * Display list of peers the user needs to evaluate.
     */
    public function index()
    {
        $employeeId = Auth::user()->employee->id;
        
        // Fetch assignments where current user is the "Evaluator"
        $tasks = PeerAssignment::with(['teacher', 'academicYear'])
            ->where('peer_id', $employeeId)
            ->get();

        return view('faculty.peer-evaluations.index', compact('tasks'));
    }

    /**
     * Show the evaluation form for a specific peer.
     */
    public function create(PeerAssignment $assignment)
    {
        // Security check: Ensure the logged-in user is actually assigned to this peer
        if ($assignment->peer_id !== Auth::user()->employee->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($assignment->is_completed) {
            return redirect()->route('faculty.peer-evaluations.index')->with('error', 'Evaluation already submitted.');
        }

        return view('faculty.peer-evaluations.form', compact('assignment'));
    }

    /**
     * Store the evaluation.
     */
    public function store(Request $request, PeerAssignment $assignment)
    {
        $request->validate([
            'ratings' => 'required|array',
            'comments' => 'nullable|string|max:1000',
        ]);

        $ratings = $request->ratings;
        $meanScore = array_sum($ratings) / count($ratings);

        // Save to evaluations table
        Evaluation::create([
            'teacher_id' => $assignment->teacher_id, // Person being rated
            'evaluator_id' => Auth::id(),           // Person doing the rating
            'evaluator_type' => 'peer',
            'academic_year_id' => $assignment->academic_year_id,
            'semester' => $assignment->semester,
            'ratings' => $ratings,
            'mean_score' => $meanScore,
            'comments' => $request->comments,
        ]);

        // Mark assignment as completed
        $assignment->update(['is_completed' => true]);

        return redirect()->route('faculty.peer-evaluations.index')
            ->with('success', 'Evaluation submitted successfully.');
    }
}