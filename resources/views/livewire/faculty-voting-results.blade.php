<div class="py-8" wire:poll.5s>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-xs uppercase font-bold tracking-wider">Total Voters</p>
                        <p class="text-4xl font-extrabold mt-1">{{ number_format($totalVoters) }}</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        @foreach($positions as $positionKey => $positionLabel)
            @php
                $positionCandidates = ($candidatesByPosition[$positionKey] ?? collect())
                    ->sortByDesc(fn ($candidate) => $voteCountsByCandidate[$candidate->id] ?? 0)
                    ->values();
                $positionTotalVotes = (int) ($totalVotesByPosition[$positionKey] ?? 0);
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-8 bg-indigo-500 rounded-full"></div>
                        <h4 class="text-xl font-bold text-gray-800">{{ $positionLabel }}</h4>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-lg">
                            {{ number_format($positionTotalVotes) }} Total Votes
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    @if($positionCandidates->isEmpty())
                        <div class="flex flex-col items-center py-8 text-gray-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="italic">No approved candidates for this position yet.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($positionCandidates as $index => $candidate)
                                @php
                                    $votes = (int) ($voteCountsByCandidate[$candidate->id] ?? 0);
                                    $percentage = $positionTotalVotes > 0 ? ($votes / $positionTotalVotes) * 100 : 0;
                                    $isWinner = $index === 0 && $votes > 0;
                                @endphp

                                <div class="relative">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full font-bold text-sm {{ $isWinner ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $index + 1 }}
                                            </span>

                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-gray-900">
                                                        {{ $candidate->student->first_name }} {{ $candidate->student->last_name }}
                                                    </span>
                                                    @if($isWinner)
                                                        <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-bold uppercase">Leading</span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-gray-500 font-medium">
                                                    {{ $candidate->is_independent ? 'Independent' : ($candidate->partylist ?? 'No Partylist') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="block text-sm font-bold text-gray-900">{{ number_format($votes) }} votes</span>
                                            <span class="block text-xs font-medium text-gray-500">{{ number_format($percentage, 1) }}%</span>
                                        </div>
                                    </div>

                                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000 {{ $isWinner ? 'bg-indigo-600' : 'bg-gray-400' }}"
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
