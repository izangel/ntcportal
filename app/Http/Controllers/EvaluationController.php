<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\PeerAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    /**
     * Updated Store Method with Dynamic Validation
     */
    public function store(Request $request)
    {
        $type = $request->evaluator_type; // student, peer, self, supervisor
        
        // Fetch valid keys from config for this type (e.g., p1, p2... or s1, s2...)
        $questionKeys = array_keys(config("evaluation_questions.$type"));
        
        // Build dynamic validation rules
        $rules = [
            'teacher_id'       => 'required|exists:users,id',
            'evaluator_type'   => 'required|in:student,peer,self,supervisor',
            'academic_year_id' => 'required',
            'semester'         => 'required',
            'ratings'          => 'required|array',
        ];

        foreach ($questionKeys as $key) {
            $rules["ratings.$key"] = 'required|integer|min:1|max:5';
        }

        $request->validate($rules);

        $ratings = $request->input('ratings');
        $meanScore = collect($ratings)->avg();

        DB::beginTransaction();
        try {
            Evaluation::create([
                'teacher_id'       => $request->teacher_id,
                'evaluator_type'   => $type,
                'evaluator_id'     => Auth::id(),
                'academic_year_id' => $request->academic_year_id,
                'semester'         => $request->semester,
                'ratings'          => $ratings,
                'mean_score'       => $meanScore,
                'comments'         => $request->comments,
            ]);

            // Mark peer assignment as done if applicable
            if ($type === 'peer') {
                PeerAssignment::where([
                    'teacher_id' => $request->teacher_id,
                    'peer_id'    => Auth::id(),
                    'is_completed' => false
                ])->update(['is_completed' => true, 'completed_at' => now()]);
            }

            DB::commit();
            return redirect()->route('dashboard')->with('success', 'Evaluation submitted!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Submission failed: ' . $e->getMessage());
        }
    }
}