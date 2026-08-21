{{-- resources/views/academic_years/setup.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Academic Year Setup') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(!$activeAy)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">No active academic year is set. Create and activate one to begin setup.</span>
                    </div>
                    <a href="{{ route('academic_years.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Create Academic Year
                    </a>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Setup Checklist for A.Y. {{ $activeAy->start_year }} - {{ $activeAy->end_year }}
                        </h3>
                        <a href="{{ route('academic_years.edit', $activeAy) }}" class="text-xs text-indigo-600 hover:text-indigo-900">
                            Change active year
                        </a>
                    </div>

                    @if($checklist['complete'])
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">
                                <i class="fas fa-circle-check mr-1"></i>
                                All setup tasks are complete for this academic year.
                            </span>
                        </div>
                    @else
                        <div class="bg-amber-100 border border-amber-400 text-amber-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">
                                <i class="fas fa-triangle-exclamation mr-1"></i>
                                {{ $checklist['done_count'] }} of {{ $checklist['total'] }} setup tasks done. Complete them so students can be enrolled correctly.
                            </span>
                        </div>
                    @endif

                    {{-- Progress bar --}}
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-6">
                        @php $pct = $checklist['total'] > 0 ? round($checklist['done_count'] / $checklist['total'] * 100) : 0; @endphp
                        <div class="bg-green-500 h-3 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>

                    <div class="space-y-4">
                        @foreach($checklist['items'] as $item)
                            <div class="flex items-start justify-between gap-4 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 text-lg">
                                        @if($item['done'])
                                            <i class="fas fa-circle-check text-green-600"></i>
                                        @else
                                            <i class="fas fa-circle-xmark text-red-500"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $item['label'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $item['description'] }}</p>
                                    </div>
                                </div>
                                <a href="{{ route($item['route']) }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white {{ $item['done'] ? 'bg-gray-400' : 'bg-indigo-600 hover:bg-indigo-700' }} rounded-md">
                                    <i class="fas {{ $item['icon'] }} mr-1"></i>
                                    {{ $item['done'] ? 'View' : 'Set Up' }}
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {{-- Quick Setup Shortcuts --}}
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Quick Setup Shortcuts</h4>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('sections.copy.form') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-800 text-white text-xs font-bold rounded-md hover:bg-gray-700">
                                <i class="fas fa-copy"></i>
                                Copy Sections From Previous Year
                            </a>
                            <a href="{{ route('sections.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-800 text-white text-xs font-bold rounded-md hover:bg-gray-700">
                                <i class="fas fa-eye"></i>
                                View All Sections
                            </a>
                            <a href="{{ route('semesters.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-800 text-white text-xs font-bold rounded-md hover:bg-gray-700">
                                <i class="fas fa-plus"></i>
                                Create a Semester
                            </a>
                            <a href="{{ route('course-blocks.bulk-uploader') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-800 text-white text-xs font-bold rounded-md hover:bg-gray-700">
                                <i class="fas fa-cloud-arrow-up"></i>
                                Bulk Upload Course Blocks
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection