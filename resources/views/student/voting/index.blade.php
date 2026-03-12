@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('SSG Voting') }}
        </h2>
        <span class="text-sm text-gray-500 font-medium">{{ now()->format('l, F j, Y') }}</span>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if($hasSubmittedVotes)
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg">
                You already submitted your votes. Your ballot is now locked and cannot be changed.
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Cast Your Vote</h3>
                        <p class="text-sm text-gray-600 mt-1">Select one candidate for each position and submit your ballot.</p>
                        @if($activeAcademicYear)
                            <p class="text-xs text-gray-500 mt-2">
                                Academic Year: {{ $activeAcademicYear->start_year }}-{{ $activeAcademicYear->end_year }}
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('student.voting.results') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                        <i class="fas fa-poll mr-2"></i> View Live Results
                    </a>
                </div>
            </div>

            @if($candidatesByPosition->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-user-slash text-4xl mb-4"></i>
                    <p>No approved candidates are available for voting yet.</p>
                </div>
            @else
                <form action="{{ route('student.voting.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    @foreach($positions as $positionKey => $positionLabel)
                        @php
                            $positionCandidates = $candidatesByPosition[$positionKey] ?? collect();
                        @endphp

                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <h4 class="font-semibold text-gray-900">{{ $positionLabel }}</h4>
                            </div>

                            <div class="p-4">
                                @if($positionCandidates->isEmpty())
                                    <p class="text-sm text-gray-500 italic">No approved candidates yet for this position.</p>
                                @else
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($positionCandidates as $candidate)
                                            @php
                                                $selectedCandidate = old("votes.$positionKey", $selectedVotes[$positionKey] ?? null);
                                                $isChecked = (string) $selectedCandidate === (string) $candidate->id;
                                            @endphp
                                            <label class="flex items-start gap-3 rounded-lg border p-3 transition {{ $hasSubmittedVotes ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} {{ $isChecked ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">
                                                <input
                                                    type="radio"
                                                    name="votes[{{ $positionKey }}]"
                                                    value="{{ $candidate->id }}"
                                                    class="mt-1 text-blue-600 focus:ring-blue-500"
                                                    {{ $isChecked ? 'checked' : '' }}
                                                    {{ $hasSubmittedVotes ? 'disabled' : '' }}
                                                >
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">
                                                        {{ $candidate->student->last_name ?? '' }}, {{ $candidate->student->first_name ?? '' }} {{ $candidate->student->middle_name ?? '' }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        @if($candidate->is_independent)
                                                            Independent
                                                        @else
                                                            {{ $candidate->partylist ?? 'No Partylist' }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    @error("votes.$positionKey")
                                        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if(!$hasSubmittedVotes)
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-semibold">
                                <i class="fas fa-check-circle mr-2"></i> Submit Votes
                            </button>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
