<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidacy;
use App\Models\Position;
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

        // Filter archived status
        $showArchived = $request->boolean('archived');
        if ($showArchived) {
            $query->archived();
        } else {
            $query->notArchived();
        }

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

        $positionOrder = Position::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
        $positionCounts = Candidacy::query()
            ->notArchived()
            ->selectRaw('position_id, COUNT(*) as total')
            ->groupBy('position_id')
            ->pluck('total', 'position_id');

        $applications = $query->paginate(15);
        $googleDriveLink = Setting::get('candidacy_google_drive_link', 'https://drive.google.com/drive/folders/1ll0nBJvq1a4I1rxezkaNCQO5VWSxI5_F');
        $isApplicationOpenSHS = Setting::get('candidacy_application_open_shs', 'true') === 'true';
        $isApplicationOpenCollege = Setting::get('candidacy_application_open_college', 'true') === 'true';

        return view('admin.candidacy.index', compact('applications', 'googleDriveLink', 'isApplicationOpenSHS', 'isApplicationOpenCollege', 'positionOrder', 'positionCounts', 'showArchived'));
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
        $positions = Position::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
        return view('admin.candidacy.edit', compact('candidacy', 'positions'));
    }

    /**
     * Update the specified candidacy application.
     */
    public function update(Request $request, Candidacy $candidacy)
    {
        $allowedPositions = Position::where('is_active', true)->pluck('id')->implode(',');
        $request->validate([
            'position_id' => 'required|string|in:' . $allowedPositions,
            'is_independent' => 'required|boolean',
            'partylist' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['position_id', 'is_independent']);
        $data['partylist'] = $request->is_independent ? null : $request->partylist;

        $candidacy->update($data);

        return redirect()->route('admin.candidacy.index')
            ->with('success', 'Candidacy application updated successfully.');
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
        $positions = Position::where('is_active', true)->orderBy('name')->pluck('id');
        $sql = 'CASE position_id';
        $index = 1;
        foreach ($positions as $id) {
            $sql .= " WHEN '{$id}' THEN {$index}";
            $index++;
        }
        $sql .= ' ELSE 99 END';
        return $sql;
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
     * Toggle the candidacy application open/closed status for a program type.
     */
    public function toggleApplicationStatus(string $programType)
    {
        $key = 'candidacy_application_open_' . $programType;
        $currentStatus = Setting::get($key, 'true');
        $newStatus = $currentStatus === 'true' ? 'false' : 'true';
        
        Setting::set($key, $newStatus, 'Whether candidacy applications are open for ' . strtoupper($programType) . ' students');

        $label = strtoupper($programType);
        $message = $newStatus === 'true' 
            ? "Candidacy applications are now OPEN for {$label} students." 
            : "Candidacy applications are now CLOSED for {$label} students.";

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

    /**
     * Archive a candidacy application.
     */
    public function archive(Candidacy $candidacy)
    {
        $candidacy->update(['archived_at' => now()]);

        return redirect()->route('admin.candidacy.index')
            ->with('success', 'Candidacy application has been archived.');
    }

    /**
     * Restore an archived candidacy application.
     */
    public function restore(Candidacy $candidacy)
    {
        $candidacy->update(['archived_at' => null]);

        return redirect()->route('admin.candidacy.index', ['archived' => 1])
            ->with('success', 'Candidacy application has been restored.');
    }

    /**
     * Archive all approved candidacy applications for a given academic year.
     */
    public function archiveAll()
    {
        $count = Candidacy::notArchived()->update(['archived_at' => now()]);

        return redirect()->route('admin.candidacy.index')
            ->with('success', "All {$count} candidacy applications have been archived.");
    }
}
