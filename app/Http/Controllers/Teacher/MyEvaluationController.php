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

        $evaluations = \App\Models\Evaluation::where([
            'teacher_id'       => $teacher->id,
            'academic_year_id' => $academicYearId,
            'semester'         => $semester
        ])->get();

        if ($evaluations->isEmpty()) {
            return redirect()->route('teacher.evaluations.index')->with('error', 'No data found.');
        }

        $types = ['student', 'peer', 'self', 'supervisor'];
        $breakdown = [];
        $categoryAverages = [];

        foreach ($types as $type) {
            $typeEvals = $evaluations->where('evaluator_type', $type);
            
            if ($typeEvals->isNotEmpty()) {
                $keys = array_keys($typeEvals->first()->ratings);

               
               
                foreach ($keys as $key) {
                    $breakdown[$type]['questions'][$key] = $typeEvals->avg("ratings.$key");
                }


                
               
                $breakdown[$type]['meta'] = [
                    'count' => $typeEvals->count(),
                    'average' => $typeEvals->avg('mean_score'),
                    // Collecting all three types of qualitative feedback
                    'feedback' => $typeEvals->map(fn($e) => [
                        'helped' => $e->aspects_helped,
                        'improved' => $e->aspects_improved,
                        'comments' => $e->comments
                    ])->filter(fn($f) => $f['helped'] || $f['improved'] || $f['comments'])
                ];
                $categoryAverages[] = $typeEvals->avg('mean_score');
            } else {
                $breakdown[$type] = null;
            }
        }

        $overallScore = count($categoryAverages) > 0 ? array_sum($categoryAverages) / count($categoryAverages) : 0;
        $academicYear = \App\Models\AcademicYear::find($academicYearId);

        return view('teacher.evaluations.report', compact('breakdown', 'overallScore', 'academicYear', 'semester'));
    }
}