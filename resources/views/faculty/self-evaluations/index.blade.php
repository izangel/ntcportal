@extends('admin.admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8 antialiased">
    <div class="max-w-5xl mx-auto px-4">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                        <li>Performance</li>
                        <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg></li>
                        <li class="text-indigo-600">Self-Evaluation</li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900">My Self-Assessments</h1>
                <p class="text-sm text-gray-500 font-medium">Manage and track your personal performance reflections.</p>
            </div>

            @if(!$hasSubmittedCurrent)
                <a href="{{ route('faculty.self-evaluations.create') }}" 
                   class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-indigo-100 group">
                    Start New Assessment
                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-bold rounded-2xl flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Current Status</p>
                @if($hasSubmittedCurrent)
                    <span class="text-sm font-bold text-emerald-600 flex items-center">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span> Completed
                    </span>
                @else
                    <span class="text-sm font-bold text-amber-500 flex items-center">
                        <span class="w-2 h-2 bg-amber-500 rounded-full mr-2"></span> Pending for {{ $currentSemester }}
                    </span>
                @endif
            </div>
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Submissions</p>
                <span class="text-xl font-bold text-gray-900">{{ $evaluations->count() }}</span>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Last Mean Score</p>
                <span class="text-xl font-bold text-indigo-600">
                    {{ $evaluations->first() ? number_format($evaluations->first()->mean_score, 2) : '0.00' }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Assessment History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                            <th class="px-6 py-4">Academic Period</th>
                            <th class="px-6 py-4">Date Submitted</th>
                            <th class="px-6 py-4 text-center">Mean Score</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($evaluations as $eval)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-800">{{ $eval->academicYear->name }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">{{ $eval->semester }} Semester</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium">
                                {{ $eval->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-black rounded-lg border border-indigo-100">
                                    {{ number_format($eval->mean_score, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-gray-400 hover:text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No evaluation records found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection