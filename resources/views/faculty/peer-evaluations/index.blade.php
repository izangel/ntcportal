@extends('admin.admin')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 py-12">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Peer Evaluations</h1>
            <p class="text-sm text-gray-500">Provide constructive feedback for your assigned colleagues.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-4">
            @forelse($tasks as $task)
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                            {{ substr($task->teacher->first_name, 0, 1) }}{{ substr($task->teacher->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $task->teacher->last_name }}, {{ $task->teacher->first_name }}</h3>
                            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider">
                                {{ $task->semester }} | {{ $task->academicYear->start_year }}-{{ $task->academicYear->end_year }}
                            </p>
                        </div>
                    </div>

                    <div>
                        @if($task->is_completed)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                Completed
                            </span>
                        @else
                            <a href="{{ route('faculty.peer-evaluations.create', $task) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors">
                                Start Evaluation
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white border-2 border-dashed border-gray-200 rounded-2xl p-12 text-center">
                    <p class="text-gray-400 font-medium">No peer evaluation assignments found for this period.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection