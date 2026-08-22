<?php

namespace App\Http\Controllers;

use App\Models\{PeerAssignment, Evaluation, AcademicYear};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorEvaluationController extends Controller
{
    public function index()
    {
        $supervisorId = Auth::user()->employee->id;
        
        // Fetch subordinates assigned by HR
        // Note: We'll reuse the logic where 'peer_id' acts as the Evaluator (Supervisor)
        $subordinates = PeerAssignment::with(['teacher', 'academicYear'])
            ->where('peer_id', $supervisorId)
            ->where('assignment_type', 'supervisor') // Differentiates from peer links
            ->get();

        return view('supervisor.evaluations.index', compact('subordinates'));
    }

    /**
     * Ensure the logged-in supervisor owns this assignment of type 'supervisor'.
     */
    private function authorizeSupervisor(PeerAssignment $assignment): void
    {
        if ($assignment->assignment_type !== 'supervisor') {
            abort(403, 'Unauthorized actions.');
        }
        if ($assignment->peer_id != Auth::user()->employee->id) {
            abort(403, 'Unauthorized actions.');
        }
    }

    public function create(PeerAssignment $assignment)
    {
        $this->authorizeSupervisor($assignment);

        if ($assignment->is_completed) {
            return back()->with('error', 'Evaluation already completed.');
        }
        return view('supervisor.evaluations.form', compact('assignment'));
    }

    public function store(Request $request, PeerAssignment $assignment)
    {
        $this->authorizeSupervisor($assignment);

        if ($assignment->is_completed) {
            return back()->with('error', 'Evaluation already completed.');
        }

        $request->validate([
            'ratings' => 'required|array|min:1',
            'ratings.*' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string|max:1000',
        ]);
        
        $ratings = array_values($request->ratings);
        $meanScore = array_sum($ratings) / count($ratings);

        Evaluation::create([
            'teacher_id' => $assignment->teacher_id,
            'evaluator_id' => Auth::user()->employee->id,
            'evaluator_type' => 'supervisor',
            'academic_year_id' => $assignment->academic_year_id,
            'semester' => $assignment->semester,
            'ratings' => $ratings,
            'mean_score' => $meanScore,
            'comments' => $request->comments,
        ]);

        $assignment->update(['is_completed' => true]);

        return redirect()->route('supervisor.evaluations.index')
            ->with('success', 'Evaluation submitted successfully.');
    }
}