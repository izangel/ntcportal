@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                {{ __('SSG Voting') }}
            </h2>
            <p class="text-sm text-gray-500 font-medium">Official Digital Ballot</p>
        </div>
        <div class="text-right">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Election Status</span>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-sm font-semibold text-gray-700">Open for Voting</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="py-10">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-r-lg shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($hasSubmittedVotes)
            <div class="bg-blue-600 rounded-2xl shadow-lg p-8 text-white flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-lock text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold">Ballot Submitted & Locked</h3>
                <p class="text-blue-100 mt-2 max-w-md">Thank you for participating! You have already cast your vote for this academic year. Your choices have been securely recorded.</p>
                <a href="{{ route('student.voting.results') }}" class="mt-6 px-6 py-2 bg-white text-blue-600 rounded-full font-bold text-sm hover:bg-blue-50 transition">
                    View Live Tallies
                </a>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Cast Your Vote</h3>
                        <p class="text-gray-500 mt-1">Select one candidate for each position below.</p>
                    </div>
                    @if(!$hasSubmittedVotes)
                        <div class="hidden md:block">
                            <span class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-xs font-bold uppercase tracking-wide">
                                {{ $activeAcademicYear->start_year }}-{{ $activeAcademicYear->end_year }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            @if($candidatesByPosition->isEmpty())
                <div class="p-20 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-50 text-gray-300 rounded-full mb-4">
                        <i class="fas fa-inbox text-3xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium italic">No candidates have been approved for this election yet.</p>
                </div>
            @else
                <form action="{{ route('student.voting.store') }}" method="POST" class="p-8 space-y-12">
                    @csrf

                    @foreach($positions as $positionKey => $positionLabel)
                        @php
                            $positionCandidates = $candidatesByPosition[$positionKey] ?? collect();
                        @endphp

                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <h4 class="text-lg font-bold text-gray-800 uppercase tracking-wide">{{ $positionLabel }}</h4>
                                <div class="h-px flex-1 bg-gray-100"></div>
                            </div>

                            @if($positionCandidates->isEmpty())
                                <p class="text-sm text-gray-400 italic py-4">No candidates available.</p>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($positionCandidates as $candidate)
                                        @php
                                            $selectedCandidate = old("votes.$positionKey", $selectedVotes[$positionKey] ?? null);
                                            $isChecked = (string) $selectedCandidate === (string) $candidate->id;
                                        @endphp
                                        
                                        <label class="group relative flex items-center p-5 rounded-2xl border-2 transition-all duration-200 
                                            {{ $hasSubmittedVotes ? 'cursor-not-allowed grayscale' : 'cursor-pointer' }} 
                                            {{ $isChecked ? 'border-indigo-600 bg-indigo-50/50 ring-4 ring-indigo-50' : 'border-gray-100 hover:border-indigo-200 hover:bg-gray-50' }}">
                                            
                                            <input
                                                type="radio"
                                                name="votes[{{ $positionKey }}]"
                                                value="{{ $candidate->id }}"
                                                class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                                {{ $isChecked ? 'checked' : '' }}
                                                {{ $hasSubmittedVotes ? 'disabled' : '' }}
                                            >

                                            <div class="ml-4">
                                                <p class="text-base font-bold text-gray-900 group-hover:text-indigo-900">
                                                    {{ $candidate->student->first_name }} {{ $candidate->student->last_name }}
                                                </p>
                                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mt-1">
                                                    {{ $candidate->is_independent ? 'Independent' : ($candidate->partylist ?? 'No Partylist') }}
                                                </p>
                                            </div>

                                            @if($isChecked)
                                                <div class="absolute right-5 text-indigo-600">
                                                    <i class="fas fa-check-circle text-xl"></i>
                                                </div>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>

                                @error("votes.$positionKey")
                                    <p class="text-xs font-bold text-red-500 mt-2 flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @enderror
                            @endif
                        </div>
                    @endforeach

                    @if(!$hasSubmittedVotes)
                        <div class="pt-8 border-t border-gray-100">
                            <button type="submit" class="w-full md:w-auto px-10 py-4 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 text-base font-bold flex items-center justify-center gap-3">
                                Finalize and Submit Ballot
                                <i class="fas fa-paper-plane text-xs opacity-70"></i>
                            </button>
                            <p class="text-center md:text-left text-xs text-gray-400 mt-4">
                                Note: You cannot change your votes after submission.
                            </p>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    </div>
</div>
@endsection