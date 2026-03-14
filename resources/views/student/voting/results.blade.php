@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                {{ __('Voting Results') }}
            </h2>
            <p class="text-sm text-gray-500 font-medium">
                @if($activeAcademicYear)
                    Academic Year {{ $activeAcademicYear->start_year }}-{{ $activeAcademicYear->end_year }}
                @else
                    All-time Records
                @endif
            </p>
        </div>
        <div class="text-right">
            <a href="{{ route('student.voting.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150">
                <i class="fas fa-vote-yea mr-2 text-indigo-500"></i> Back to Ballot
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="px-6 lg:px-8 max-w-6xl mx-auto pt-6">
    @if(session('success'))
        <div class="bg-white border-l-4 border-green-500 shadow-sm p-4 rounded-r-xl flex items-center gap-4 animate-fade-in-down mb-4">
            <div class="bg-green-100 p-2 rounded-full">
                <i class="fas fa-check text-green-600"></i>
            </div>
            <div>
                <p class="text-green-800 font-bold text-sm">Action Successful</p>
                <p class="text-green-600 text-xs">{{ session('success') }}</p>
            </div>
        </div>
    @endif
</div>
<livewire:student-voting-results />
@endsection