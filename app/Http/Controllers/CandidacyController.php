<?php

namespace App\Http\Controllers;

use App\Models\Candidacy;
use App\Models\AcademicYear;
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
        $isApplicationOpen = Setting::get('candidacy_application_open', 'true') === 'true';
        
        return view('candidacy.index', compact('existingCandidacy', 'activeAcademicYear', 'googleDriveLink', 'isApplicationOpen'));
    }

    /**
     * Store a new candidacy application.
     */
    public function store(Request $request)
    {
        // Check if applications are open
        $isApplicationOpen = Setting::get('candidacy_application_open', 'true') === 'true';
        if (!$isApplicationOpen) {
            return redirect()->route('student.candidacy.index')
                ->with('error', 'Candidacy applications are currently closed.');
        }

        $request->validate([
            'position' => 'required|string|max:255',
            'partylist' => 'nullable|string|max:255',
        ]);

        $student = Auth::user()->student;

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
            'position_applied' => $request->position,
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
}
