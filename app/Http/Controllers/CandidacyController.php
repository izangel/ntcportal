<?php

namespace App\Http\Controllers;

use App\Models\Candidacy;
use App\Models\AcademicYear;
use App\Models\Position;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidacyController extends Controller
{
    /**
     * Display the candidacy application form.
     */
    public function index()
    {
        $student = Auth::user()->student;
        $existingCandidacy = $student ? Candidacy::where('student_id', $student->id)->latest()->first() : null;
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $googleDriveLink = Setting::get('candidacy_google_drive_link', 'https://drive.google.com/drive/folders/1ll0nBJvq1a4I1rxezkaNCQO5VWSxI5_F');

        $positions = [];
        $isApplicationOpen = false;
        if ($student) {
            $programType = $student->program_type;
            $isApplicationOpen = Setting::get('candidacy_application_open_' . $programType, 'true') === 'true';
            $positions = Position::where('is_active', true)
                ->whereIn('program_type', [$programType, 'both'])
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
        }
        
        return view('candidacy.index', compact('existingCandidacy', 'activeAcademicYear', 'googleDriveLink', 'isApplicationOpen', 'positions'));
    }

    /**
     * Store a new candidacy application.
     */
    public function store(Request $request)
    {
        // Check if applications are open for student's program type
        $student = Auth::user()->student;
        $programType = $student->program_type;
        $isApplicationOpen = Setting::get('candidacy_application_open_' . $programType, 'true') === 'true';
        if (!$isApplicationOpen) {
            $label = $programType === 'shs' ? 'SHS' : 'College';
            return redirect()->route('student.candidacy.index')
                ->with('error', "Candidacy applications are currently closed for {$label} students.");
        }

        $allowedPositions = Position::where('is_active', true)->pluck('id')->implode(',');
        $request->validate([
            'position' => 'required|string|in:' . $allowedPositions,
            'partylist' => 'nullable|string|max:255',
        ]);

        // Check if student already has any candidacy application
        $existingCandidacy = Candidacy::where('student_id', $student->id)->first();

        if ($existingCandidacy) {
            return redirect()->route('student.candidacy.status')
                ->with('error', 'You have already submitted a candidacy application.');
        }

        // Get active academic year
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        Candidacy::create([
            'student_id' => $student->id,
            'academic_year_id' => $activeAcademicYear?->id,
            'position_id' => $request->position,
            'partylist' => $request->partylist,
            'is_independent' => $request->has('is_independent'),
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return redirect()->route('student.candidacy.status')
            ->with('success', 'Your candidacy application has been submitted successfully.');
    }

    /**
     * Display candidacy application status.
     */
    public function status()
    {
        $student = Auth::user()->student;
        $application = $student ? Candidacy::where('student_id', $student->id)->latest()->first() : null;
        
        return view('candidacy.status', compact('application'));
    }

    /**
     * Display candidacy requirements.
     */
    public function requirements()
    {
        return view('candidacy.requirements');
    }

    public function destroy(\App\Models\Candidacy $application)
    {
        // Optional: If you want to delete files from storage if they exist
        // Storage::delete($application->id_path); 

        $application->delete();

        return back()->with('success', 'Application deleted successfully.');
    }
}
