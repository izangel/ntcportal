@extends('layouts.admin')

@section('header')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li><span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Reports</span></li>
                    <li><svg class="h-3 w-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg></li>
                    <li><span class="text-[10px] font-black uppercase tracking-widest text-indigo-600">360° Performance</span></li>
                </ol>
            </nav>
            <h2 class="font-black text-3xl text-gray-900 tracking-tight">Consolidated Faculty Report</h2>
            <p class="text-sm text-gray-500 font-bold uppercase tracking-widest">{{ $activeSem->name }} | {{ $semester }}</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="bg-white px-5 py-3 rounded-2xl border border-gray-100 shadow-sm text-right">
                <span class="text-[10px] font-black text-gray-400 uppercase block">Faculty Member</span>
                <span class="text-sm font-black text-gray-900">{{ $employee->name }}</span>
            </div>
            <button onclick="window.print()" class="p-3 bg-gray-900 text-white rounded-2xl hover:bg-indigo-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z"/></svg>
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="py-8" x-data="{ activeTab: 'student' }">
    <div class="max-w-7xl mx-auto">

        <div class="bg-gray-900 rounded-[3rem] p-10 mb-10 text-white shadow-2xl shadow-indigo-200/20 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-400 mb-2">Final Weighted Rating (360°)</p>
                    <div class="flex items-baseline gap-4">
                        <h1 class="text-8xl font-black tracking-tighter">{{ number_format($finalScore, 2) }}</h1>
                        <span class="text-2xl font-bold opacity-30">/ 5.00</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 w-full md:w-auto">
                    @foreach(['student', 'peer', 'self', 'supervisor'] as $type)
                    <div class="bg-white/10 border border-white/10 p-4 rounded-3xl backdrop-blur-md">
                        <p class="text-[9px] font-black uppercase tracking-widest text-indigo-300 mb-1">{{ $type }}</p>
                        <p class="text-xl font-black">{{ number_format($groupScores[$type] ?? 0, 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex border-b border-gray-50 px-8 py-4 gap-6 bg-gray-50/30 overflow-x-auto">
                        @foreach($allQuestions as $type => $qs)
                        <button @click="activeTab = '{{ $type }}'" 
                            :class="activeTab === '{{ $type }}' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-400'"
                            class="pb-2 text-[10px] font-black uppercase tracking-widest transition-all outline-none whitespace-nowrap">
                            {{ ucfirst($type) }} Results
                        </button>
                        @endforeach
                    </div>

                    <div class="p-8">
                        @foreach($allQuestions as $type => $questions)
                        <div x-show="activeTab === '{{ $type }}'" x-transition:enter="transition ease-out duration-300" x-cloak>
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-[10px] font-black text-gray-300 uppercase tracking-widest border-b border-gray-50">
                                        <th class="py-4">Performance Metric / Question</th>
                                        <th class="py-4 text-right">Mean Score</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($questions as $key => $text)
                                    <tr class="group hover:bg-gray-50/50 transition-all">
                                        <td class="py-5 pr-6">
                                            <span class="text-[9px] font-bold text-indigo-400 uppercase block mb-1">Question {{ $loop->iteration }}</span>
                                            <p class="text-sm font-semibold text-gray-700 leading-snug group-hover:text-gray-900">{{ $text }}</p>
                                        </td>
                                        <td class="py-5 text-right">
                                            <span class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 text-xs font-black rounded-xl border border-indigo-100">
                                                {{ number_format($evals->where('evaluator_type', $type)->avg("ratings.$key"), 2) ?: '0.00' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h3 class="font-black text-gray-900 uppercase tracking-widest text-[11px]">Qualitative Feedback</h3>
                    <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-2 py-1 rounded-lg">{{ $comments->count() }} Comments</span>
                </div>
                
                <div class="space-y-4 max-h-[800px] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($comments as $comment)
                    <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm relative group">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                            <span class="text-[9px] font-black text-gray-400 uppercase">{{ $comment->evaluator_type }} feedback</span>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed italic font-medium">"{{ $comment->comments }}"</p>
                        <div class="mt-4 flex justify-between items-center border-t border-gray-50 pt-4">
                            <div class="flex gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3 h-3 {{ $i <= $comment->mean_score ? 'text-amber-400' : 'text-gray-100' }} fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                @endfor
                            </div>
                            <span class="text-[9px] text-gray-300 font-bold uppercase tracking-tighter">{{ $comment->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="bg-gray-50 border-2 border-dashed border-gray-100 rounded-[2rem] p-10 text-center">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">No written feedback for this term.</p>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none; }
        body { background: white; }
        .rounded-[3rem], .rounded-[2.5rem], .rounded-[2rem] { border-radius: 1rem !important; }
        .shadow-sm, .shadow-xl, .shadow-2xl { shadow: none !important; }
    }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 10px; }
</style>
@endsection