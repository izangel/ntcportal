<?php

namespace App\Http\Controllers;

use App\Models\{Evaluation, AcademicYear};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SelfEvaluationController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->employee;
        
        // Fetch all past self-evaluations
        $evaluations = Evaluation::with('academicYear')
            ->where('teacher_id', $teacher->id)
            ->where('evaluator_type', 'self')
            ->latest()
            ->get();

        // Determine if current period is already done
        $currentAY = AcademicYear::where('is_active', true)->first();
        $currentSemester = "1st"; // Logic to get current semester
        
        $hasSubmittedCurrent = $evaluations->where('academic_year_id', $currentAY->id)
                                        ->where('semester', $currentSemester)
                                        ->isNotEmpty();

        return view('faculty.self-evaluations.index', compact(
            'evaluations', 
            'hasSubmittedCurrent', 
            'currentSemester'
        ));
    }

    public function create()
    {
        $teacher = Auth::user()->employee;
        
        // Get the current active Academic Year (you may have a specific logic for 'active')
        $currentAY = AcademicYear::where('is_active', true)->first();
        $currentSemester = "1st"; // This should ideally come from a settings table

        // Check if already submitted
        $exists = Evaluation::where([
            'teacher_id' => $teacher->id,
            'evaluator_id' => Auth::id(),
            'evaluator_type' => 'self',
            'academic_year_id' => $currentAY->id,
            'semester' => $currentSemester
        ])->exists();

        if ($exists) {
            return redirect()->route('faculty.self-evaluations.index')
                ->with('error', 'You have already submitted your self-evaluation for this term.');
        }

        return view('faculty.self-evaluations.form', compact('currentAY', 'currentSemester'));
    }

    public function store(Request $request)
    {
        $request->validate(['ratings' => 'required|array']);
        
        $teacher = Auth::user()->employee;
        $ratings = $request->ratings;
        $meanScore = array_sum($ratings) / count($ratings);

        Evaluation::create([
            'teacher_id' => $teacher->id,
            'evaluator_id' => $teacher->id,
            'evaluator_type' => 'self',
            'academic_year_id' => $request->academic_year_id,
            'semester' => $request->semester,
            'ratings' => $ratings,
            'mean_score' => $meanScore,
            'comments' => $request->comments,
        ]);

        return redirect()->route('faculty.self-evaluations.index')
            ->with('success', 'Self-evaluation submitted successfully.');
    }
}