@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Approved Candidates') }}
        </h2>
        <span class="text-sm text-gray-500 font-medium">{{ now()->format('l, F j, Y') }}</span>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            
            {{-- Header --}}
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">SSG Election - Official Candidates</h3>
                <p class="mt-1 text-sm text-gray-500">List of all approved candidates for the upcoming election.</p>
            </div>

            {{-- Candidates Grid --}}
            <div class="p-6">
                @php
                    $positions = $candidates->groupBy('position_applied');
                @endphp

                @forelse($positions as $position => $positionCandidates)
                    <div class="mb-8">
                        <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-star text-yellow-500 mr-2"></i>
                            {{ ucwords(str_replace('_', ' ', $position)) }}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($positionCandidates as $candidate)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-blue-600 font-bold">{{ substr($candidate->student->first_name ?? '', 0, 1) }}{{ substr($candidate->student->last_name ?? '', 0, 1) }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="font-semibold text-gray-900">{{ $candidate->student->last_name ?? '' }}, {{ $candidate->student->first_name ?? '' }}</p>
                                            <p class="text-sm text-gray-500">
                                                @if($candidate->is_independent)
                                                    <span class="italic">Independent</span>
                                                @else
                                                    {{ $candidate->partylist ?? 'No Partylist' }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-user-slash text-4xl mb-4"></i>
                        <p>No approved candidates yet.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $candidates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
