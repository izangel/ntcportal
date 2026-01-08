<?php

namespace App\Http\Controllers;

use App\Models\{Evaluation, Semester, AcademicYear, Employee};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class EvaluationReportController extends Controller
{
    /**
     * Show the selection page (AY and Semester only).
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        return view('faculty.reports.index', compact('academicYears'));
    }

    /**
     * Generate the consolidated 360 report.
     */
    public function show360Report(Request $request)
    {
        // 1. Validation & Setup
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|string'
        ]);

        $user = Auth::user();
        $employee = Auth::user()->employee;
        
        if (!$employee) {
            return back()->with('error', 'Faculty profile not found.');
        }

        $ayId = $request->academic_year_id;
        $semester = $request->semester;

        // 2. Get the Active Semester
    
        $activeSem = Semester::where('academic_year_id', $ayId)->first(); // For display purposes


        // 2. Fetch all evaluations for this faculty in the selected term
        $evals = Evaluation::where([
            'teacher_id' => $employee->id, // Assuming teacher_id matches User ID or Employee ID
            'academic_year_id' => $ayId,
            'semester' => $semester
        ])->get();

        if ($evals->isEmpty()) {
            return back()->with('error', 'No evaluation data found for the selected period.');
        }

      

        // 3. Group Averages for the 25% weights
        // We calculate the mean for each group independently
        $groupScores = [
            'student'    => $evals->where('evaluator_type', 'student')->avg('mean_score') ?: 0,
            'peer'       => $evals->where('evaluator_type', 'peer')->avg('mean_score') ?: 0,
            'self'       => $evals->where('evaluator_type', 'self')->avg('mean_score') ?: 0,
            'supervisor' => $evals->where('evaluator_type', 'supervisor')->avg('mean_score') ?: 0,
        ];

        // 4. Calculate Final Weighted Score (Standard 360 Logic)
        $finalScore = (
            ($groupScores['student'] * 0.25) + 
            ($groupScores['peer'] * 0.25) + 
            ($groupScores['self'] * 0.25) + 
            ($groupScores['supervisor'] * 0.25)
        );

        // 5. Fetch all Qualitative Feedback (Comments) across all types
        $comments = Evaluation::where([
                'teacher_id' => $user->id,
                'academic_year_id' => $ayId,
                'semester' => $semester
            ])
            ->whereNotNull('comments')
            ->orderBy('created_at', 'desc')
            ->get();

        // 6. Get Questions from Config for the Tabbed View
        $allQuestions = config('evaluation_questions');

        return view('faculty.reports.detailed_360', compact(
            'groupScores', 
            'finalScore', 
            'allQuestions', 
            'evals', 
            'semester', 
            'employee', 
            'activeSem',
            'comments'
        ));
    }

    /**
     * Admin view remains focused on global ranking, but can link to individual 360s.
     */
    public function adminView()
    {
        $activeSem = Semester::where('is_active', 1)->first();
        if (!$activeSem) return back()->with('error', 'No active semester.');

        // Simple aggregation for Admin to see who has evaluations
        $reports = Evaluation::select(
                'teacher_id',
                \DB::raw('AVG(mean_score) as average_rating'),
                \DB::raw('COUNT(*) as total_responses')
            )
            ->where('academic_year_id', $activeSem->academic_year_id)
            ->groupBy('teacher_id')
            ->with('teacher') // Ensure the User/Employee relationship is loaded
            ->get();

        return view('reports.admin-global', compact('reports', 'activeSem'));
    }
}