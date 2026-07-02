<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Semester;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyEvaluationController extends Controller
{
    /**
     * Show a list of all semesters where the teacher was evaluated.
     */
    public function index()
    {
        $teacher = Auth::user()->employee;

        // Get unique periods where this teacher has received evaluations
        $availableReports = Evaluation::where('teacher_id', $teacher->id)
            ->select('academic_year_id', 'semester')
            ->distinct()
            ->with('academicYear')
            ->get();

        return view('teacher.evaluations.index', compact('availableReports'));
    }

    /**
     * The actual 360-degree feedback report for the teacher.
     */
    public function show($academicYearId, $semester)
{
    $teacher = auth()->user()->employee;

    // 1. Get Peer, Self, and Supervisor Evaluations
    $legacyEvals = \App\Models\Evaluation::where([
        'teacher_id'       => $teacher->id,
        'academic_year_id' => $academicYearId,
        'semester'         => $semester
    ])->get();

    // 2. Get Student Evaluations from faculty_evaluations
    // We filter by searching for the semester (e.g. "1st") within the course blocks
    $studentEvals = \App\Models\FacultyEvaluation::whereHas('courseBlock', function($q) use ($teacher, $academicYearId, $semester) {
            $q->where('faculty_id', $teacher->id)
              ->where('academic_year_id', $academicYearId)
              ->where('semester', 'LIKE', $semester . '%');
        })
        ->get();

    // If both are empty, no data
    if ($legacyEvals->isEmpty() && $studentEvals->isEmpty()) {
        return redirect()->route('teacher.evaluations.index')->with('error', 'No data found.');
    }

    $types = ['student', 'peer', 'self', 'supervisor'];
    $breakdown = [];
    $categoryAverages = [];

    foreach ($types as $type) {
        if ($type === 'student') {
            $typeEvals = $studentEvals;
        } else {
            $typeEvals = $legacyEvals->where('evaluator_type', $type);
        }
        
        if ($typeEvals->isNotEmpty()) {
            $breakdown[$type]['questions'] = [];
            
            // Map questions based on type
            if ($type === 'student') {
                // For Students: We need to iterate through categories in config
                foreach (config('evaluation_questions.student') as $group) {
                    foreach ($group['questions'] as $q) {
                        $key = $q['k']; // e.g., 'q1'
                        
                        // Calculate average for this specific key across all student evaluations
                        $avgForQ = $typeEvals->map(function($e) use ($key) {
                            $ratings = is_array($e->ratings) ? $e->ratings : json_decode($e->ratings, true);
                            return $ratings[$key] ?? null;
                        })->filter(fn($v) => !is_null($v))->average();

                        $breakdown[$type]['questions'][] = [
                            'key' => $key,
                            'text' => $q['t'],
                            'score' => $avgForQ ?? 0
                        ];
                    }
                }
            } else {
                // For Peer/Self/Supervisor: Use the flat key-value pairs from config
                $questionMap = config("evaluation_questions.{$type}");
                $firstEval = $typeEvals->first();
                $ratings = is_array($firstEval->ratings) ? $firstEval->ratings : json_decode($firstEval->ratings, true);

                foreach ($ratings as $rKey => $val) {
                    $breakdown[$type]['questions'][] = [
                        'key' => $rKey,
                        'text' => $questionMap[$rKey] ?? 'Question ' . $rKey,
                        'score' => $typeEvals->avg(function($e) use ($rKey) {
                             $r = is_array($e->ratings) ? $e->ratings : json_decode($e->ratings, true);
                             return $r[$rKey] ?? 0;
                        })
                    ];
                }
            }

            $breakdown[$type]['meta'] = [
                'count' => $typeEvals->count(),
                'average' => $typeEvals->avg('mean_score'),
                'feedback' => $typeEvals->map(fn($e) => [
                    'helped' => $e->aspects_helped ?? $e->comment_helped ?? null, // Map student specific columns
                    'improved' => $e->aspects_improved ?? $e->comment_improved ?? null,
                    'comments' => $e->comments ?? null
                ])->filter(fn($f) => $f['helped'] || $f['improved'] || $f['comments'])
            ];
            $categoryAverages[] = $typeEvals->avg('mean_score');
        } else {
            $breakdown[$type] = null;
        }
    }

    $overallScore = count($categoryAverages) > 0 ? array_sum($categoryAverages) / count($categoryAverages) : 0;
    $academicYear = \App\Models\AcademicYear::find($academicYearId);

        // Ensure all keys exist for the chart even if null
    foreach(['student', 'peer', 'supervisor', 'self'] as $type) {
        if (!isset($breakdown[$type])) {
            $breakdown[$type] = ['meta' => ['average' => 0, 'count' => 0]];
        }
    }

    return view('teacher.evaluations.report', compact('breakdown', 'overallScore', 'academicYear', 'semester'));
}
}