<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidacy;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidacyManagementController extends Controller
{
    /**
     * Display all candidacy applications.
     */
    public function index(Request $request)
    {
        $query = Candidacy::with('student.user');

        $this->applyPositionOrdering($query)
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('student', function ($studentQuery) use ($search) {
                $studentQuery->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(15);
        $googleDriveLink = Setting::get('candidacy_google_drive_link', 'https://drive.google.com/drive/folders/1ll0nBJvq1a4I1rxezkaNCQO5VWSxI5_F');
        $isApplicationOpen = Setting::get('candidacy_application_open', 'true') === 'true';

        return view('admin.candidacy.index', compact('applications', 'googleDriveLink', 'isApplicationOpen'));
        $applications = $query->paginate(10);
        $positionOrder = [
            'president' => 'President',
            'vice_president' => 'Vice President',
            'secretary' => 'Secretary',
            'treasurer' => 'Treasurer',
            'auditor' => 'Auditor',
            'pio' => 'PIO',
            'business_manager' => 'Business Manager',
        ];
        $positionCounts = Candidacy::query()
            ->selectRaw('position_applied, COUNT(*) as total')
            ->groupBy('position_applied')
            ->pluck('total', 'position_applied');
        $googleDriveLink = Setting::get('candidacy_google_drive_link', 'https://drive.google.com/drive/folders/1ll0nBJvq1a4I1rxezkaNCQO5VWSxI5_F');
        $isApplicationOpen = Setting::get('candidacy_application_open', 'true') === 'true';

        return view('admin.candidacy.index', compact('applications', 'googleDriveLink', 'isApplicationOpen', 'positionOrder', 'positionCounts'));
    }

    /**
     * Display a specific candidacy application.
     */
    public function show(Candidacy $candidacy)
    {
        $candidacy->load('student.user');
        return view('admin.candidacy.show', compact('candidacy'));
    } 

    /**
     * Show the form for editing the specified candidacy application.
     */
    public function edit(Candidacy $candidacy)
    {
        $candidacy->load('student.user');
        $positions = [
            'president' => 'President',
            'vice_president' => 'Vice President',
            'secretary' => 'Secretary',
            'treasurer' => 'Treasurer',
            'auditor' => 'Auditor',
            'pio' => 'PIO',
            'business_manager' => 'Business Manager',
        ];
        return view('admin.candidacy.edit', compact('candidacy', 'positions'));
    }

    /**
     * Update the specified candidacy application.
     */
    public function update(Request $request, Candidacy $candidacy)
    {
        $request->validate([
            'position_applied' => 'required|string|in:president,vice_president,secretary,treasurer,auditor,pio,business_manager',
            'is_independent' => 'required|boolean',
            'partylist' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['position_applied', 'is_independent']);
        $data['partylist'] = $request->is_independent ? null : $request->partylist;

        $candidacy->update($data);

        return redirect()->route('admin.candidacy.index')
            ->with('success', 'Candidacy application updated successfully.');
        $candidacy->update([
            'status' => 'approved',
            'remarks' => $request->remarks,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()->route('admin.candidacy.index')
            ->with('success', 'Candidacy application has been approved.');
    }

    /**
     * Reject a candidacy application.
     */
    public function reject(Request $request, Candidacy $candidacy)
    {
        $request->validate([
            'remarks' => 'required|string|max:500',
        ]);

        $candidacy->update([
            'status' => 'rejected',
            'remarks' => $request->remarks,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()->route('admin.candidacy.index')
            ->with('success', 'Candidacy application has been rejected.');
    }

    /**
     * Display approved candidates.
     */
    public function candidates()
    {
        $candidates = Candidacy::with('student.user')
            ->where('status', 'approved')
            ->orderByRaw($this->positionOrderCaseStatement())
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.candidacy.candidates', compact('candidates'));
    }

    /**
     * Apply fixed position ordering for candidacy records.
     */
    private function applyPositionOrdering(Builder $query): Builder
    {
        return $query->orderByRaw($this->positionOrderCaseStatement());
    }

    /**
     * SQL CASE statement for ordering positions consistently.
     */
    private function positionOrderCaseStatement(): string
    {
        return "CASE position_applied
            WHEN 'president' THEN 1
            WHEN 'vice_president' THEN 2
            WHEN 'secretary' THEN 3
            WHEN 'treasurer' THEN 4
            WHEN 'auditor' THEN 5
            WHEN 'pio' THEN 6
            WHEN 'business_manager' THEN 7
            ELSE 99
        END";
    }

    /**
     * Update the Google Drive link setting.
     */
    public function updateGoogleDriveLink(Request $request)
    {
        $request->validate([
            'google_drive_link' => 'required|url|max:500',
        ]);

        Setting::set('candidacy_google_drive_link', $request->google_drive_link, 'Google Drive folder link for candidacy student ID uploads');

        return redirect()->route('admin.candidacy.index')
            ->with('success', 'Google Drive link has been updated successfully.');
    }

    /**
     * Toggle the candidacy application open/closed status.
     */
    public function toggleApplicationStatus()
    {
        $currentStatus = Setting::get('candidacy_application_open', 'true');
        $newStatus = $currentStatus === 'true' ? 'false' : 'true';
        
        Setting::set('candidacy_application_open', $newStatus, 'Whether candidacy applications are open for students');

        $message = $newStatus === 'true' 
            ? 'Candidacy applications are now OPEN for students.' 
            : 'Candidacy applications are now CLOSED for students.';

        return redirect()->route('admin.candidacy.index')
            ->with('success', $message);
    }

    /**
     * Approve a candidacy application.
     */
    public function approve(Request $request, Candidacy $candidacy)
    {
        $candidacy->update([
            'status' => 'approved',
            'remarks' => $request->remarks,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()->route('admin.candidacy.index')
            ->with('success', 'Candidacy application has been approved.');
    }
}
