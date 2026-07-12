<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\ElectionVote;
use App\Traits\VotingQueries;

class FacultyVotingResults extends Component
{
    use VotingQueries;

    public $electionStatusSHS = 'open';
    public $electionStatusCollege = 'open';

    public function mount()
    {
        $this->electionStatusSHS = \App\Models\Setting::get('election_status_shs', 'open');
        $this->electionStatusCollege = \App\Models\Setting::get('election_status_college', 'open');
    }

    public function toggleSHS()
    {
        $newStatus = $this->electionStatusSHS === 'open' ? 'closed' : 'open';
        \App\Models\Setting::set('election_status_shs', $newStatus, 'Current status of the SSG election for SHS');
        $this->electionStatusSHS = $newStatus;

        session()->flash('message', 'SHS election has been ' . $newStatus . '.');
    }

    public function toggleCollege()
    {
        $newStatus = $this->electionStatusCollege === 'open' ? 'closed' : 'open';
        \App\Models\Setting::set('election_status_college', $newStatus, 'Current status of the SSG election for College');
        $this->electionStatusCollege = $newStatus;

        session()->flash('message', 'College election has been ' . $newStatus . '.');
    }

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
