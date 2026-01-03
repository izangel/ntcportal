@extends('layouts.admin')

@section('header')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="font-black text-2xl text-gray-900 tracking-tight">Personal Faculty Report</h2>
            <p class="text-sm text-indigo-600 font-bold uppercase tracking-widest">{{ $activeSem->name }}</p>
        </div>
        <div class="bg-indigo-50 px-4 py-2 rounded-2xl border border-indigo-100">
            <span class="text-[10px] font-black text-indigo-400 uppercase block">Instructor</span>
            <span class="text-sm font-bold text-indigo-900">{{ $employee->name }}</span>
        </div>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Overall Average</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-gray-900">{{ number_format($reports->avg('average_rating'), 1) }}</span>
                    <span class="text-sm font-bold text-amber-500">/ 5.0</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Responses</p>
                <span class="text-3xl font-black text-gray-900">{{ $reports->sum('total_responses') }}</span>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Courses Evaluated</p>
                <span class="text-3xl font-black text-indigo-600">{{ $reports->count() }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="font-black text-gray-900 uppercase tracking-tighter">Evaluation per Subject</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-8 py-4 text-left">Subject Code</th>
                                    <th class="px-8 py-4 text-left">Rating Average</th>
                                    <th class="px-8 py-4 text-center">Volume</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($reports as $report)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="text-sm font-black text-gray-900">{{ $report->course->code }}</div>
                                        <div class="text-[10px] text-gray-400 font-medium truncate max-w-[200px]">{{ $report->course->name }}</div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex gap-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-4 h-4" 
                                                        style="fill: {{ $i <= round($report->average_rating) ? '#fbbf24' : '#E5E7EB' }};" 
                                                        viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="text-sm font-black text-gray-700">{{ number_format($report->average_rating, 1) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 uppercase">
                                            {{ $report->total_responses }} Students
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-8 py-10 text-center text-gray-400 italic text-sm">No evaluation data found for this term.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="font-black text-gray-900 uppercase tracking-tighter px-2">Student Comments</h3>
                
                @forelse($comments as $comment)
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 relative overflow-hidden group hover:border-indigo-200 transition-all">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-gray-50 -mr-8 -mt-8 rounded-full transition-colors group-hover:bg-indigo-50"></div>
                    
                    <div class="relative">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-black text-indigo-500 uppercase bg-indigo-50 px-2 py-0.5 rounded-lg">{{ $comment->course->code }}</span>
                            <div class="flex gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3 h-3 {{ $i <= $comment->rating ? 'text-amber-400' : 'text-gray-200' }} fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed italic font-medium">"{{ $comment->comments }}"</p>
                        <div class="mt-4 flex justify-end">
                            <span class="text-[10px] text-gray-300 font-bold uppercase">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-400 italic text-sm py-10">No written feedback yet.</p>
                @endforelse
            </div>

        </div>
    </div>
</div>
@endsection