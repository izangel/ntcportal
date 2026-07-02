<?php

namespace App\Http\Controllers;

use App\Models\EvaluationSetting;
use App\Models\Semester;
use App\Models\AcademicYear;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationWorkflowController extends Controller
{
    /**
     * Reusing your logic for consistency
     */
    private function getCurrentContext()
    {
        $activeSemester = Semester::with('academicYear')->where('is_active', true)->first();
        
        if (!$activeSemester) {
            $activeAY = AcademicYear::where('is_active', true)->first();
            return [
                'ay' => $activeAY,
                'semester' => '1st Semester' // Fallback
            ];
        }

        return [
            'ay' => $activeSemester->academicYear,
            'semester' => $activeSemester->name
        ];
    }

    // Helper to avoid repeating the AY/Sem query logic
    private function getActiveSetting()
    {
        $context = $this->getCurrentContext();
        if (!$context['ay']) return null;
        
        $ayString = $context['ay']->start_year . '-' . $context['ay']->end_year;
        return \App\Models\EvaluationSetting::where('academic_year', $ayString)
            ->where('semester', $context['semester'])
            ->first();
    }

    public function index()
    {
        $context = $this->getCurrentContext();
        $ayString = $context['ay'] ? $context['ay']->start_year . '-' . $context['ay']->end_year : 'N/A';
        $currentCycle = $this->getActiveSetting();

        return view('evaluation.tracker', compact('currentCycle', 'context', 'ayString'));
    }

    /**
     * STEP 1: Confirm Period
     */
    public function verifyPeriod()
    {
        // FIX: Added "!" to check if they DO NOT have the role
        if (!auth()->user()->hasRole('registrar')) {
            return back()->with('error', 'Only the Registrar can confirm the period.');
        }

        $context = $this->getCurrentContext();
        if (!$context['ay']) return back()->with('error', 'Active Academic Year not found.');

        $ayString = $context['ay']->start_year . '-' . $context['ay']->end_year;

        \App\Models\EvaluationSetting::updateOrCreate(
            [
                'academic_year' => $ayString,
                'semester'      => $context['semester'],
            ],
            [
                'is_active' => true,
                'period_verified' => true,
            ]
        );

        return back()->with('success', "Academic period confirmed.");
    }

    /**
     * STEP 2: Verify Blocks
     */
    public function verifyBlocks(Request $request)
{
    $setting = $this->getActiveSetting();
    $type = $request->input('type'); // 'shs' or 'college'

    if (!$setting) {
        return back()->with('error', 'Evaluation setting not found.');
    }

    if ($type === 'shs') {
        // Only allow users with the specific SHS role
        if (!auth()->user()->hasRole('program_head_shs')) {
            return back()->with('error', 'Only the SHS Program Head can verify these blocks.');
        }

        $setting->update(['shs_blocks_verified' => true]);
        $message = "SHS Blocks verified successfully.";
    } 
    elseif ($type === 'college') {
        // Only allow users with the specific College role
        if (!auth()->user()->hasRole('program_head_college')) {
            return back()->with('error', 'Only the College Program Head can verify these blocks.');
        }

        $setting->update(['college_blocks_verified' => true]);
        $message = "College Blocks verified successfully.";
    } 
    else {
        return back()->with('error', 'Invalid department selection.');
    }

    // Logic Gate: If BOTH are now verified, mark the entire Step 2 as complete
    if ($setting->shs_blocks_verified && $setting->college_blocks_verified) {
        $setting->update(['blocks_verified' => true]);
    }

    return back()->with('success', $message);
}

    /**
     * STEP 3: Verify Students
     */
    public function verifyStudents()
    {
        // Updated to use hasRole
        if (!auth()->user()->hasRole('registrar')) {
            return back()->with('error', 'Only Registrar can verify the student list.');
        }

        $setting = $this->getActiveSetting();

        if (!$setting || !$setting->blocks_verified) {
            return back()->with('error', 'Blocks must be verified before finalizing the student list.');
        }

        $setting->update([
            'students_verified' => true
        ]);

        return back()->with('success', 'Student population confirmed.');
    }

    /**
     * STEP 4: Finalize Subject Loading
     */
    public function verifyLoading(Request $request)
{
    $setting = $this->getActiveSetting();
    $type = $request->input('type');

    if (!$setting || !$setting->students_verified) {
        return back()->with('error', 'Student list must be verified first.');
    }

    if ($type === 'shs') {
        // Requirement: SHS Program Head verifies SHS Loading
        if (!auth()->user()->hasRole('program_head_shs')) {
            return back()->with('error', 'Only the SHS Program Head can verify SHS loading.');
        }
        $setting->update(['shs_loading_verified' => true]);
        $message = "SHS Subject loading verified.";
    } 
    elseif ($type === 'college') {
        // Requirement: Registrar verifies College Loading
        if (!auth()->user()->hasRole('registrar')) {
            return back()->with('error', 'Only the Registrar can verify College loading.');
        }
        $setting->update(['college_loading_verified' => true]);
        $message = "College Subject loading verified.";
    }

    // Mark step 4 complete if both are done
    if ($setting->shs_loading_verified && $setting->college_loading_verified) {
        $setting->update(['loading_verified' => true]);
    }

    return back()->with('success', $message);
}

    /**
     * STEP 5: Open Evaluations
     */
    public function openEvaluations()
{
    $user = auth()->user();

    // 1. Double check role permission
    if (!$user->hasRole('academic_head') && !$user->hasRole('guidance')) {
        return back()->with('error', 'Unauthorized: Only Academic Head or Guidance can open evaluations.');
    }

    $setting = $this->getActiveSetting();

    // 2. Check if the previous steps are actually finished
    if (!$setting) {
        return back()->with('error', 'Cycle record not found. Please verify the period first.');
    }

    if (!$setting->loading_verified) {
        return back()->with('error', 'Cannot open evaluations. Subject loading is not yet verified for both departments.');
    }

    // 3. Execute the update
    $setting->update([
        'evaluations_opened' => true,
        'opened_at' => now(), // Good for tracking
    ]);

    return back()->with('success', 'Teacher evaluations are now LIVE for students.');
}
}