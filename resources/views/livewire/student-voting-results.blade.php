<div class="py-8" wire:poll.5s>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl shadow-lg p-6 text-white col-span-1">
                <p class="text-indigo-100 text-xs uppercase font-bold tracking-wider">Total Participants</p>
                <div class="flex items-end gap-2 mt-1">
                    <p class="text-4xl font-extrabold">{{ number_format($totalVoters) }}</p>
                    <p class="text-indigo-200 text-sm mb-1 pb-1">Voters</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:col-span-2">
                <p class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-4">Your Ballot Summary</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($positions as $positionKey => $positionLabel)
                        @php $myVote = $myVotes[$positionKey] ?? null; @endphp
                        <div class="px-3 py-2 bg-gray-50 rounded-xl border border-gray-100 flex flex-col">
                            <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $positionLabel }}</span>
                            <span class="text-sm font-bold text-gray-800">
                                {{ $myVote->candidacy->student->last_name ?? 'No Vote' }}
                            </span>
                        </div>
                    @endforeach
                </div>
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

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-8 bg-indigo-500 rounded-full"></div>
                        <h4 class="text-xl font-bold text-gray-800">{{ $positionLabel }}</h4>
                    </div>
                    <span class="text-sm font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-lg">
                        {{ number_format($positionTotalVotes) }} Total Votes
                    </span>
                </div>

                <div class="p-6">
                    @if($positionCandidates->isEmpty())
                        <div class="flex flex-col items-center py-8 text-gray-400">
                            <p class="italic">No approved candidates for this position.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($positionCandidates as $index => $candidate)
                                @php
                                    $votes = (int) ($voteCountsByCandidate[$candidate->id] ?? 0);
                                    $percentage = $positionTotalVotes > 0 ? ($votes / $positionTotalVotes) * 100 : 0;
                                    $isWinner = $index === 0 && $votes > 0;
                                    $isMyVote = (int) $myVoteCandidateId === (int) $candidate->id;
                                @endphp

                                <div class="relative group">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full font-bold text-sm 
                                                {{ $isWinner ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $index + 1 }}
                                            </span>
                                            
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-gray-900">
                                                        {{ $candidate->student->first_name }} {{ $candidate->student->last_name }}
                                                    </span>
                                                    @if($isMyVote)
                                                        <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-bold uppercase tracking-tighter">Your Choice</span>
                                                    @endif
                                                    @if($isWinner)
                                                        <i class="fas fa-crown text-yellow-400 text-xs"></i>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-gray-500 font-medium">
                                                    {{ $candidate->is_independent ? 'Independent' : ($candidate->partylist ?? 'No Partylist') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="block text-sm font-bold text-gray-900">{{ number_format($votes) }}</span>
                                            <span class="block text-xs font-medium text-gray-500">{{ number_format($percentage, 1) }}%</span>
                                        </div>
                                    </div>

                                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000 
                                            {{ $isWinner ? 'bg-indigo-600' : 'bg-gray-400' }} 
                                            {{ $isMyVote ? 'ring-2 ring-blue-300 ring-inset' : '' }}" 
                                             style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
