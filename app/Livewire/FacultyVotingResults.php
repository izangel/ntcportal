<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\ElectionVote;
use App\Traits\VotingQueries;

class FacultyVotingResults extends Component
{
    use VotingQueries;

    public function render()
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

        return view('livewire.faculty-voting-results', compact(
            'positions',
            'candidatesByPosition',
            'voteCountsByCandidate',
            'totalVotesByPosition',
            'totalVoters',
            'activeAcademicYear'
        ));
    }
}
