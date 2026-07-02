<?php

namespace App\Http\Controllers;

use App\Models\{Evaluation, AcademicYear, Semester};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SelfEvaluationController extends Controller
{
    /**
     * Helper to convert "First Semester" to "1st", etc.
     */
    private function formatSemester($name)
    {
        if (str_contains($name, 'First')) return '1st';
        if (str_contains($name, 'Second')) return '2nd';
        if (str_contains($name, 'Summer')) return 'Sum';
        return $name; // Fallback
    }

    private function getCurrentContext()
    {
        $activeSemester = Semester::where('is_active', true)->first();
        
        if (!$activeSemester) {
            $activeAY = AcademicYear::where('is_active', true)->first();
            return [
                'ay' => $activeAY,
                'semester' => '1st' 
            ];
        }

        return [
            'ay' => $activeSemester->academicYear,
            'semester' => $this->formatSemester($activeSemester->name)
        ];
    }

    public function index()
    {
        $teacher = Auth::user()->employee;
        $context = $this->getCurrentContext();
        
        $evaluations = Evaluation::with('academicYear')
            ->where('teacher_id', $teacher->id)
            ->where('evaluator_type', 'self')
            ->latest()
            ->get();

        $hasSubmittedCurrent = $evaluations->where('academic_year_id', $context['ay']->id)
                                        ->where('semester', $context['semester'])
                                        ->isNotEmpty();

        return view('faculty.self-evaluations.index', [
            'evaluations' => $evaluations,
            'hasSubmittedCurrent' => $hasSubmittedCurrent,
            'currentSemester' => $context['semester'],
            'currentAY' => $context['ay']
        ]);
    }

    public function create()
    {
        $teacher = Auth::user()->employee;
        $context = $this->getCurrentContext();

        $exists = Evaluation::where([
            'teacher_id' => $teacher->id,
            'evaluator_type' => 'self',
            'academic_year_id' => $context['ay']->id,
            'semester' => $context['semester']
        ])->exists();

        if ($exists) {
            return redirect()->route('faculty.self-evaluations.index')
                ->with('error', 'You have already submitted your self-evaluation for this term.');
        }

        return view('faculty.self-evaluations.form', [
            'currentAY' => $context['ay'],
            'currentSemester' => $context['semester']
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ratings' => 'required|array',
            'academic_year_id' => 'required',
            'semester' => 'required' // This will now be "1st", "2nd", or "Sum"
        ]);
        
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