@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                {{ __('SSG Election Results') }}
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
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold uppercase tracking-widest">Live Results</span>
            <p class="text-xs text-gray-400 mt-1">{{ now()->format('l, F j, Y | g:i A') }}</p>
        </div>
    </div>
@endsection

@section('content')
<livewire:faculty-voting-results />
@endsection