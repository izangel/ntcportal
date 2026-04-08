<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\ElectionVote;
use Illuminate\Support\Facades\Auth;
use App\Traits\VotingQueries;

class StudentVotingResults extends Component
{
    use VotingQueries;

    public function render()
    {
        $student = Auth::user()->student;
        
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

        $myVotes = null;
        if ($student) {
            $myVotes = $this->studentVotesQueryForElection($student->id, $activeAcademicYear)
                ->with('candidacy.student')
                ->get()
                ->filter(fn (ElectionVote $vote) => !empty($vote->candidacy?->position_applied))
                ->keyBy(fn (ElectionVote $vote) => $vote->candidacy->position_applied);
        } else {
            $myVotes = collect();
        }

        return view('livewire.student-voting-results', compact(
            'positions',
            'candidatesByPosition',
            'voteCountsByCandidate',
            'totalVotesByPosition',
            'totalVoters',
            'myVotes',
            'activeAcademicYear'
        ));
    }
}
