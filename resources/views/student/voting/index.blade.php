@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-3xl text-gray-900 leading-tight tracking-tight">
                {{ __('SSG Voting') }}
            </h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase rounded">Academic Year {{ $activeAcademicYear->start_year }}-{{ $activeAcademicYear->end_year }}</span>
                <span class="text-sm text-gray-400 font-medium">| Official Digital Ballot</span>
            </div>
        </div>
        <div class="text-right hidden sm:block">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Election Status</span>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-green-50 border border-green-100 rounded-full">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-xs font-bold text-green-700 uppercase">Live & Open</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="py-10 bg-gray-50/50 min-h-screen">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        {{-- Flash Success Message --}}
        @if(session('success'))
            <div class="bg-white border-l-4 border-green-500 shadow-sm p-4 rounded-r-xl flex items-center gap-4 animate-fade-in-down">
                <div class="bg-green-100 p-2 rounded-full">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div>
                    <p class="text-green-800 font-bold text-sm">Action Successful</p>
                    <p class="text-green-600 text-xs">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Locked State: Already Voted --}}
        @if($hasSubmittedVotes)
            <div class="relative overflow-hidden bg-indigo-700 rounded-3xl shadow-2xl p-10 text-white">
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mb-6 border border-white/20 shadow-inner">
                        <i class="fas fa-vote-yea text-3xl"></i>
                    </div>
                    <h3 class="text-3xl font-black tracking-tight">Your Vote is Secured</h3>
                    <p class="text-indigo-100 mt-3 max-w-md leading-relaxed">
                        Thank you for exercising your right to vote! Your ballot for the <strong>{{ $activeAcademicYear->start_year }} Election</strong> has been encrypted and recorded.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4 mt-8">
                        <a href="{{ route('student.voting.results') }}" class="px-8 py-3 bg-white text-indigo-700 rounded-xl font-bold text-sm hover:bg-indigo-50 transition-all shadow-lg hover:shadow-white/10">
                            <i class="fas fa-chart-bar mr-2"></i> View Live Results
                        </a>
                    </div>
                </div>
                {{-- Decorative background elements --}}
                <div class="absolute top-0 right-0 -mt-20 -mr-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-indigo-900/50 rounded-full blur-3xl"></div>
            </div>
        @endif

        {{-- Main Voting Form --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <div class="p-10 border-b border-gray-50">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Selection Area</h3>
                        <p class="text-gray-500 mt-1 font-medium">Please review each candidate carefully before making your choice.</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-tighter bg-gray-50 px-3 py-2 rounded-lg">
                        <i class="fas fa-info-circle text-indigo-400"></i>
                        One vote per position
                    </div>
                </div>
            </div>

            @if($candidatesByPosition->isEmpty())
                <div class="py-24 text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-50 text-gray-200 rounded-full mb-6">
                        <i class="fas fa-user-slash text-4xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-400">No Candidates Found</h4>
                    <p class="text-gray-400 mt-2">The election roster is currently empty.</p>
                </div>
            @else
                <form action="{{ route('student.voting.store') }}" method="POST" class="p-10 space-y-16">
                    @csrf

                    @foreach($positions as $positionKey => $positionLabel)
                        @php
                            $positionCandidates = $candidatesByPosition[$positionKey] ?? collect();
                        @endphp

                        <div class="relative">
                            <div class="flex items-center gap-6 mb-8">
                                <span class="flex-none text-sm font-black text-indigo-600 bg-indigo-50 px-4 py-1 rounded-full uppercase tracking-widest border border-indigo-100">
                                    {{ $positionLabel }}
                                </span>
                                <div class="h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent"></div>
                            </div>

                            @if($positionCandidates->isEmpty())
                                <div class="bg-gray-50 border-2 border-dashed border-gray-100 rounded-3xl p-8 text-center">
                                    <p class="text-sm text-gray-400 font-medium italic">Uncontested position: No candidates available.</p>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @foreach($positionCandidates as $candidate)
                                        @php
                                            $selectedCandidate = old("votes.$positionKey", $selectedVotes[$positionKey] ?? null);
                                            $isChecked = (string) $selectedCandidate === (string) $candidate->id;
                                        @endphp
                                        
                                        <label class="group relative flex items-center p-6 rounded-3xl border-2 transition-all duration-300 
                                            {{ $hasSubmittedVotes ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} 
                                            {{ $isChecked ? 'border-indigo-600 bg-indigo-50/30 shadow-lg shadow-indigo-100' : 'border-gray-100 hover:border-indigo-200 hover:bg-white hover:shadow-md' }}">
                                            
                                            <input
                                                type="radio"
                                                name="votes[{{ $positionKey }}]"
                                                value="{{ $candidate->id }}"
                                                class="w-6 h-6 text-indigo-600 focus:ring-offset-2 focus:ring-indigo-500 border-gray-300 transition-all cursor-pointer"
                                                {{ $isChecked ? 'checked' : '' }}
                                                {{ $hasSubmittedVotes ? 'disabled' : '' }}
                                            >

                                            <div class="ml-5 flex-1">
                                                <p class="text-lg font-bold text-gray-900 group-hover:text-indigo-700 transition-colors">
                                                    {{ $candidate->student->first_name }} {{ $candidate->student->last_name }}
                                                </p>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="inline-block w-1.5 h-1.5 rounded-full {{ $candidate->is_independent ? 'bg-gray-400' : 'bg-indigo-400' }}"></span>
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-gray-500">
                                                        {{ $candidate->is_independent ? 'Independent' : ($candidate->partylist ?? 'No Partylist') }}
                                                    </p>
                                                </div>
                                            </div>

                                            @if($isChecked)
                                                <div class="absolute -top-3 -right-3 bg-indigo-600 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg border-4 border-white animate-bounce-short">
                                                    <i class="fas fa-check text-[10px]"></i>
                                                </div>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>

                                @error("votes.$positionKey")
                                    <div class="mt-4 flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-xl border border-red-100 animate-pulse">
                                        <i class="fas fa-exclamation-triangle text-xs"></i>
                                        <span class="text-xs font-bold">{{ $message }}</span>
                                    </div>
                                @enderror
                            @endif
                        </div>
                    @endforeach

                    {{-- Submit Button Section --}}
                    @if(!$hasSubmittedVotes)
                        <div class="pt-10">
                            <div class="bg-gray-900 rounded-[2rem] p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-8 shadow-2xl">
                                <div class="text-center md:text-left">
                                    <h4 class="text-xl font-bold text-white">Ready to finalize?</h4>
                                    <p class="text-gray-400 text-sm mt-1 leading-relaxed">Ensure all selections are correct. <br class="hidden md:block"> Once submitted, you cannot change your choices.</p>
                                </div>
                                <button type="submit" class="group relative w-full md:w-auto px-10 py-5 bg-indigo-500 text-white rounded-2xl hover:bg-indigo-400 transition-all duration-300 shadow-xl hover:shadow-indigo-500/20 active:scale-95 overflow-hidden">
                                    <span class="relative z-10 font-black text-lg flex items-center justify-center gap-3">
                                        Cast Secure Ballot
                                        <i class="fas fa-shield-alt group-hover:rotate-12 transition-transform"></i>
                                    </span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                </button>
                            </div>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    </div>
</div>

<style>
    @keyframes bounce-short {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    .animate-bounce-short {
        animation: bounce-short 2s ease-in-out infinite;
    }
</style>
@endsection