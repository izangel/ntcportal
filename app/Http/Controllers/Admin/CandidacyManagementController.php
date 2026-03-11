<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidacyManagementController extends Controller
{
    /**
     * Display all candidacy applications.
     */
    public function index(Request $request)
    {
        $query = Candidacy::with('student.user')->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(15);

        return view('admin.candidacy.index', compact('applications'));
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
            ->latest()
            ->paginate(15);

        return view('admin.candidacy.candidates', compact('candidates'));
    }
}
