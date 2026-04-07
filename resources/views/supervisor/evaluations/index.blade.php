@extends('layouts.admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-5xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Subordinate Evaluations</h1>
            <p class="text-sm text-gray-500">Provide administrative performance reviews for your department staff.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        <th class="px-6 py-4">Employee Name</th>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subordinates as $sub)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $sub->teacher->last_name }}, {{ $sub->teacher->first_name }}</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase">{{ $sub->teacher->department->name ?? 'No Dept' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold text-gray-600">{{ $sub->semester }} | {{ $sub->academicYear->start_year }}-{{ $sub->academicYear->end_year }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($sub->is_completed)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-600 uppercase border border-emerald-100">Completed</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-600 uppercase border border-amber-100">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if(!$sub->is_completed)
                                <a href="{{ route('supervisor.evaluations.create', $sub) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-indigo-700 transition-all">
                                    Evaluate
                                </a>
                            @else
                                <button class="text-gray-300 cursor-not-allowed text-[10px] font-black uppercase">Finalized</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 text-sm font-medium">No subordinates assigned for evaluation.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection