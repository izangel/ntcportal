@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Voting Results') }}
        </h2>
        <span class="text-sm text-gray-500 font-medium">{{ now()->format('l, F j, Y') }}</span>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500">Total Voters</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalVoters }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 md:col-span-2">
                <p class="text-xs uppercase tracking-wider text-gray-500">Coverage</p>
                <p class="text-sm text-gray-700 mt-1">
                    @if($activeAcademicYear)
                        SSG election results for Academic Year {{ $activeAcademicYear->start_year }}-{{ $activeAcademicYear->end_year }}
                    @else
                        SSG election results for all available records
                    @endif
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Your Selections</h3>
                <a href="{{ route('student.voting.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    <i class="fas fa-vote-yea mr-1"></i> Back to Ballot
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($positions as $positionKey => $positionLabel)
                    @php $myVote = $myVotes[$positionKey] ?? null; @endphp
                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $positionLabel }}</p>
                        @if($myVote && $myVote->candidacy && $myVote->candidacy->student)
                            <p class="text-sm font-semibold text-gray-900 mt-1">
                                {{ $myVote->candidacy->student->last_name ?? '' }}, {{ $myVote->candidacy->student->first_name ?? '' }}
                            </p>
                        @else
                            <p class="text-sm text-gray-500 italic mt-1">No vote submitted</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @foreach($positions as $positionKey => $positionLabel)
            @php
                $positionCandidates = ($candidatesByPosition[$positionKey] ?? collect())
                    ->sortByDesc(fn ($candidate) => $voteCountsByCandidate[$candidate->id] ?? 0)
                    ->values();
                $positionTotalVotes = (int) ($totalVotesByPosition[$positionKey] ?? 0);
                $myVoteCandidateId = $myVotes[$positionKey]->candidacy_id ?? null;
            @endphp

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h4 class="font-semibold text-gray-900">{{ $positionLabel }}</h4>
                        <span class="text-xs text-gray-500">Total Votes: {{ $positionTotalVotes }}</span>
                    </div>
                </div>

                @if($positionCandidates->isEmpty())
                    <div class="p-6 text-sm text-gray-500 italic">No approved candidates for this position yet.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partylist</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Votes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($positionCandidates as $candidate)
                                    @php
                                        $votes = (int) ($voteCountsByCandidate[$candidate->id] ?? 0);
                                        $percentage = $positionTotalVotes > 0 ? number_format(($votes / $positionTotalVotes) * 100, 2) : '0.00';
                                        $isMyVote = (int) $myVoteCandidateId === (int) $candidate->id;
                                    @endphp
                                    <tr class="{{ $isMyVote ? 'bg-blue-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $candidate->student->last_name ?? '' }}, {{ $candidate->student->first_name ?? '' }} {{ $candidate->student->middle_name ?? '' }}
                                                @if($isMyVote)
                                                    <span class="ml-2 text-xs font-bold text-blue-600">(Your Vote)</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if($candidate->is_independent)
                                                <span class="italic">Independent</span>
                                            @else
                                                {{ $candidate->partylist ?? 'No Partylist' }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $votes }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $percentage }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
