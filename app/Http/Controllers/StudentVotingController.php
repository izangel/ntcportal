<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Candidacy;
use App\Models\ElectionVote;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\VotingQueries;

class StudentVotingController extends Controller
{
    use VotingQueries;

    /**
     * Show the voting page for students.
     */
    public function index()
    {
        $student = Auth::user()->student;
        abort_unless($student, 403);

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $positions = $this->positionOrder();

        $candidates = $this->approvedCandidatesQuery($activeAcademicYear)->get();
        $candidatesByPosition = $candidates->groupBy('position_applied');

        $selectedVotesQuery = $this->studentVotesQueryForElection($student->id, $activeAcademicYear);
        $selectedVotes = (clone $selectedVotesQuery)
            ->with('candidacy:id,position_applied')
            ->get()
            ->filter(fn (ElectionVote $vote) => !empty($vote->candidacy?->position_applied))
            ->mapWithKeys(fn (ElectionVote $vote) => [$vote->candidacy->position_applied => $vote->candidacy_id]);
        $hasSubmittedVotes = (clone $selectedVotesQuery)->exists();

        return view('student.voting.index', compact(
            'positions',
            'candidatesByPosition',
            'selectedVotes',
            'hasSubmittedVotes',
            'activeAcademicYear'
        ));
    }

    /**
     * Store student votes.
     */
    public function store(Request $request)
    {
        $student = Auth::user()->student;
        abort_unless($student, 403);

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        if ($this->studentVotesQueryForElection($student->id, $activeAcademicYear)->exists()) {
            return redirect()
                ->route('student.voting.index')
                ->with('error', 'You already submitted your votes. Vote changes are no longer allowed.');
        }

        $candidateIdsByPosition = $this->approvedCandidatesQuery($activeAcademicYear)
            ->get(['id', 'position_applied'])
            ->groupBy('position_applied')
            ->map(fn (Collection $group) => $group->pluck('id')->all())
            ->toArray();

        if (empty($candidateIdsByPosition)) {
            return redirect()
                ->route('student.voting.index')
                ->with('error', 'No approved candidates are available for voting yet.');
        }

        $rules = [
            'votes' => ['required', 'array'],
        ];

        foreach ($candidateIdsByPosition as $position => $candidateIds) {
            $rules["votes.$position"] = ['required', 'integer', Rule::in($candidateIds)];
        }

        $messages = [
            'votes.required' => 'Please cast your vote before submitting.',
        ];

        foreach (array_keys($candidateIdsByPosition) as $position) {
            $messages["votes.$position.required"] = 'Please select a candidate for ' . $this->formatPosition($position) . '.';
        }

        $validated = $request->validate($rules, $messages);

        DB::transaction(function () use ($validated, $candidateIdsByPosition, $student) {
            foreach ($candidateIdsByPosition as $position => $candidateIds) {
                $candidacyId = $validated['votes'][$position] ?? null;

                if (!$candidacyId || !in_array((int) $candidacyId, $candidateIds, true)) {
                    continue;
                }

                ElectionVote::create([
                    'student_id' => $student->id,
                    'candidacy_id' => $candidacyId,
                    'voted_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('student.voting.results')
            ->with('success', 'Your votes have been submitted successfully.');
    }

    /**
     * Show election results to students.
     */
    public function results()
    {
        $student = Auth::user()->student;
        abort_unless($student, 403);

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $positions = $this->positionOrder();

        $approvedCandidates = $this->approvedCandidatesQuery($activeAcademicYear)->get();
        $candidatesByPosition = $approvedCandidates->groupBy('position_applied');

        $votesQuery = $this->votesQueryForAcademicYear($activeAcademicYear);

        $voteCountsByCandidate = (clone $votesQuery)
            ->selectRaw('candidacy_id, COUNT(*) as total_votes')
            ->groupBy('candidacy_id')
            ->pluck('total_votes', 'candidacy_id');

        $totalVotesByPosition = (clone $votesQuery)
            ->join('candidacies', 'election_votes.candidacy_id', '=', 'candidacies.id')
            ->selectRaw('candidacies.position_applied as position, COUNT(*) as total_votes')
            ->groupBy('candidacies.position_applied')
            ->pluck('total_votes', 'position');

        $totalVoters = (clone $votesQuery)
            ->distinct('student_id')
            ->count('student_id');

        $myVotes = $this->studentVotesQueryForElection($student->id, $activeAcademicYear)
            ->with('candidacy.student')
            ->get()
            ->filter(fn (ElectionVote $vote) => !empty($vote->candidacy?->position_applied))
            ->keyBy(fn (ElectionVote $vote) => $vote->candidacy->position_applied);

        return view('student.voting.results', compact(
            'positions',
            'candidatesByPosition',
            'voteCountsByCandidate',
            'totalVotesByPosition',
            'totalVoters',
            'myVotes',
            'activeAcademicYear'
        ));
    }

    /**
     * Show election results for faculty/staff/admin users.
     */
    public function facultyResults()
    {
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $positions = $this->positionOrder();

        $approvedCandidates = $this->approvedCandidatesQuery($activeAcademicYear)->get();
        $candidatesByPosition = $approvedCandidates->groupBy('position_applied');

        $votesQuery = $this->votesQueryForAcademicYear($activeAcademicYear);

        $voteCountsByCandidate = (clone $votesQuery)
            ->selectRaw('candidacy_id, COUNT(*) as total_votes')
            ->groupBy('candidacy_id')
            ->pluck('total_votes', 'candidacy_id');

        $totalVotesByPosition = (clone $votesQuery)
            ->join('candidacies', 'election_votes.candidacy_id', '=', 'candidacies.id')
            ->selectRaw('candidacies.position_applied as position, COUNT(*) as total_votes')
            ->groupBy('candidacies.position_applied')
            ->pluck('total_votes', 'position');

        $totalVoters = (clone $votesQuery)
            ->distinct('student_id')
            ->count('student_id');

        return view('faculty.election.results', compact(
            'positions',
            'candidatesByPosition',
            'voteCountsByCandidate',
            'totalVotesByPosition',
            'totalVoters',
            'activeAcademicYear'
        ));
    }
}
