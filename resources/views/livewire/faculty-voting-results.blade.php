<div class="py-4 bg-gray-100 min-h-screen" wire:poll.5s>
    <div class="max-w-[98rem] mx-auto px-2 sm:px-4 space-y-4">

        {{-- Flash Message --}}
        @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('message') }}
            </div>
        @endif

        <div class="flex items-center justify-between bg-white px-6 py-3 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center gap-8">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Population</span>
                    <span class="text-2xl font-black text-gray-900 leading-none">{{ number_format($totalVoters) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                {{-- SHS Toggle --}}
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 bg-purple-100 px-2 py-1 rounded">SHS</span>
                    <div class="relative flex h-2.5 w-2.5">
                        <span class="{{ $electionStatusSHS === 'open' ? 'animate-ping bg-green-400' : 'bg-red-400' }} absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $electionStatusSHS === 'open' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    </div>
                    <button wire:click="toggleSHS"
                            class="text-[10px] font-black uppercase tracking-[0.2em] px-3 py-1.5 rounded text-white transition-all shadow-sm {{ $electionStatusSHS === 'open' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                        {{ $electionStatusSHS === 'open' ? 'Close' : 'Open' }}
                    </button>
                </div>

                {{-- College Toggle --}}
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-blue-700 bg-blue-100 px-2 py-1 rounded">College</span>
                    <div class="relative flex h-2.5 w-2.5">
                        <span class="{{ $electionStatusCollege === 'open' ? 'animate-ping bg-green-400' : 'bg-red-400' }} absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $electionStatusCollege === 'open' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    </div>
                    <button wire:click="toggleCollege"
                            class="text-[10px] font-black uppercase tracking-[0.2em] px-3 py-1.5 rounded text-white transition-all shadow-sm {{ $electionStatusCollege === 'open' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                        {{ $electionStatusCollege === 'open' ? 'Close' : 'Open' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($positions as $positionKey => $positionLabel)
                @php
                    // GI-ADD NGA FILTER PARA MA-HIDE ANG ARCHIVED
                    $positionCandidates = ($candidatesByPosition[$positionKey] ?? collect())
                        ->filter(function($candidate) {
                            return empty($candidate->archived_at) && empty($candidate->is_archived);
                        })
                        ->sortByDesc(fn ($candidate) => $voteCountsByCandidate[$candidate->id] ?? 0)
                        ->values();
                        
                    $positionTotalVotes = (int) ($totalVotesByPosition[$positionKey] ?? 0);
                @endphp

                {{-- Kung gusto pud nimo i-hide ang tibuok box kung walay kandidato, i-uncomment kining @if sa ubos ug ang @endif sa pinakaubos --}}
                {{-- @if($positionCandidates->isNotEmpty()) --}}
                <div class="flex flex-col h-full"> 
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-full overflow-hidden">
                        <div class="bg-gray-800 border-b border-gray-700 p-4">
                            <h4 class="text-sm font-black text-white uppercase tracking-wider">
                                {{ $positionLabel }}
                            </h4>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-[10px] font-bold text-indigo-300 uppercase">
                                    {{ $electionStatusSHS === 'open' || $electionStatusCollege === 'open' ? 'Live Tally' : 'Final Results' }}
                                </span>
                                <span class="text-[10px] font-bold text-gray-400">{{ number_format($positionTotalVotes) }} Votes</span>
                            </div>
                        </div>

                        <div class="p-2 flex-grow">
                            <div class="space-y-1 h-full">
                                @php
                                    $maxVotes = $positionCandidates->max(function ($c) use ($voteCountsByCandidate) {
                                        return (int) ($voteCountsByCandidate[$c->id] ?? 0);
                                    });
                                @endphp
                                @forelse($positionCandidates as $index => $candidate)
                                    @php
                                        $votes = (int) ($voteCountsByCandidate[$candidate->id] ?? 0);
                                        $percentage = $positionTotalVotes > 0 ? ($votes / $positionTotalVotes) * 100 : 0;
                                        $isWinner = $votes > 0 && $votes === $maxVotes;
                                        $rank = $positionCandidates->filter(function ($c) use ($voteCountsByCandidate, $votes) {
                                            return (int)($voteCountsByCandidate[$c->id] ?? 0) > $votes;
                                        })->count() + 1;
                                    @endphp

                                    <div class="p-3 rounded-lg border-2 {{ $isWinner ? 'bg-indigo-50/50 border-indigo-200' : 'bg-white border-gray-100' }}">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10pt] font-black w-4 inline-block {{ $isWinner ? 'text-indigo-600' : 'text-slate-400' }}">#{{ $rank }}</span>
                                                    <p class="text-[10pt] font-bold text-gray-800 truncate leading-tight">
                                                        {{ $candidate->student->first_name }} {{ $candidate->student->last_name }}
                                                    </p>
                                                    
                                                    @if($isWinner && $electionStatusSHS !== 'open' && $electionStatusCollege !== 'open')
                                                        <span class="bg-green-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded uppercase">Winner</span>
                                                    @endif
                                                </div>
                                                <p class="text-[10pt] font-medium text-gray-400 uppercase ml-[1.375rem]">
                                                    {{ $candidate->is_independent ? 'IND' : $candidate->partylist }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10pt] font-black text-gray-900 leading-none">{{ number_format($votes) }}</p>
                                                <p class="text-[10pt] font-bold text-gray-400 leading-none mt-1">{{ number_format($percentage, 0) }}%</p>
                                            </div>
                                        </div>
                                        <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                                            <div class="h-full {{ $isWinner ? ($electionStatusSHS === 'open' || $electionStatusCollege === 'open' ? 'bg-indigo-600' : 'bg-green-600') : 'bg-gray-400' }} transition-all duration-700" 
                                                 style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center py-4 text-[10pt] font-bold text-gray-400 italic uppercase">No Candidates</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div> 
                {{-- @endif --}}
            @endforeach
        </div>
    </div>
</div>