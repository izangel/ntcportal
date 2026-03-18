@extends('admin.admin') {{-- Or your main layout file --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Course Evaluation Report') }}
    </h2>
@endsection
@section('content')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4">
        
        <div class="mb-6">
            <a href="{{ route('faculty.evaluations.index') }}" class="inline-flex items-center text-xs font-bold text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Selection
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 mb-8 border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-lg mb-3 inline-block">
                    {{ $academicYear->name }} • {{ $semester }} Semester
                </span>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">{{ $course->code }}: {{ $course->name }}</h1>
                <p class="text-gray-500 font-medium mt-1">Detailed Faculty Performance Report</p>
            </div>
            
            <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-3xl border border-gray-100">
                <div class="text-center px-4 border-r border-gray-200">
                    <p class="text-[10px] font-black text-gray-400 uppercase">Respondents</p>
                    <p class="text-2xl font-black text-gray-900">{{ $evaluations->count() }}</p>
                </div>
                <div class="text-center px-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase">Mean Score</p>
                    <p class="text-2xl font-black text-indigo-600">{{ number_format($evaluations->avg('rating'), 2) }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-8">
                
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <h3 class="text-sm font-black text-gray-900 uppercase mb-8 tracking-tighter flex items-center gap-2">
                        <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                        Category Performance Breakdown
                    </h3>
                    
                    <div class="space-y-8">
                        @foreach($reportData as $id => $data)
                        <div>
                            <div class="flex justify-between items-end mb-3">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Section {{ $id }}</span>
                                    <p class="text-sm font-bold text-gray-700">{{ $data['name'] }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-black text-gray-900">{{ number_format($data['avg'], 2) }}</span>
                                    <span class="text-[10px] font-bold text-gray-400">/ 5.00</span>
                                </div>
                            </div>
                            
                            <div class="w-full bg-gray-100 h-4 rounded-full overflow-hidden flex">
                                <div class="h-full transition-all duration-1000 ease-out rounded-full" 
                                     style="width: {{ ($data['avg'] / 5) * 100 }}%; 
                                            background-color: {{ $data['avg'] >= 4.5 ? '#10b981' : ($data['avg'] >= 3.5 ? '#6366f1' : ($data['avg'] >= 2.5 ? '#f59e0b' : '#ef4444')) }}">
                                </div>
                            </div>
                            
                            <div class="flex justify-between mt-2 px-1">
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Poor</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Fair</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Satisfactory</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Very Good</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Excellent</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <h3 class="text-sm font-black text-gray-900 uppercase mb-8 tracking-tighter flex items-center gap-2">
                        <span class="w-2 h-5 bg-emerald-500 rounded-full"></span>
                        Direct Student Feedback
                    </h3>

                    <div class="space-y-6 max-h-[800px] overflow-y-auto pr-2">
                        @foreach($evaluations as $index => $eval)
                            @if($eval->aspects_helped || $eval->aspects_improved || $eval->comments)
                            <div class="p-6 bg-gray-50 rounded-[1.5rem] border border-gray-100 relative">
                                <span class="absolute top-4 right-6 text-[10px] font-black text-gray-300 uppercase">Entry #{{ $index + 1 }}</span>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    @if($eval->aspects_helped)
                                    <div>
                                        <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-1">What helped the most</p>
                                        <p class="text-sm text-gray-700 leading-relaxed font-medium">"{{ $eval->aspects_helped }}"</p>
                                    </div>
                                    @endif

                                    @if($eval->aspects_improved)
                                    <div>
                                        <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-1">Areas for improvement</p>
                                        <p class="text-sm text-gray-700 leading-relaxed font-medium">"{{ $eval->aspects_improved }}"</p>
                                    </div>
                                    @endif

                                    @if($eval->comments)
                                    <div class="pt-4 border-t border-gray-200/60">
                                        <p class="text-[9px] font-black text-indigo-600 uppercase tracking-widest mb-1">Additional Suggestions</p>
                                        <p class="text-sm text-gray-600 leading-relaxed italic">"{{ $eval->comments }}"</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                
                <div class="bg-indigo-600 p-8 rounded-[2.5rem] text-white shadow-xl shadow-indigo-200">
                    <p class="text-[11px] font-bold uppercase opacity-70 tracking-widest">Overall Performance</p>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-6xl font-black">{{ number_format($evaluations->avg('rating'), 2) }}</span>
                        <span class="text-xl opacity-50">/ 5.0</span>
                    </div>
                    
                    @php $finalAvg = $evaluations->avg('rating'); @endphp
                    <div class="mt-8 p-4 bg-white/10 rounded-2xl border border-white/20">
                        <p class="text-[10px] font-black uppercase opacity-80 mb-1">Descriptive Rating</p>
                        <p class="text-xl font-black">
                            @if($finalAvg >= 4.5) Outstanding
                            @elseif($finalAvg >= 3.5) Very Satisfactory
                            @elseif($finalAvg >= 2.5) Satisfactory
                            @else Needs Improvement
                            @endif
                        </p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Rating Scale Guide</h4>
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                            <div>
                                <p class="text-xs font-black text-gray-700">4.50 - 5.00</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Outstanding</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-3 h-3 rounded-full bg-indigo-500"></div>
                            <div>
                                <p class="text-xs font-black text-gray-700">3.50 - 4.49</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Very Satisfactory</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div>
                                <p class="text-xs font-black text-gray-700">2.50 - 3.49</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Satisfactory</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div>
                                <p class="text-xs font-black text-gray-700">Below 2.50</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Poor / Warning</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button onclick="window.print()" class="w-full py-4 bg-gray-900 text-white font-black rounded-2xl text-[10px] uppercase tracking-widest hover:bg-black transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Official Report
                </button>

            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .bg-gray-50 { background-color: white !important; }
        nav, .mb-6, button { display: none !important; }
        .shadow-sm, .shadow-xl { shadow: none !important; border: 1px solid #eee !important; }
        .max-w-6xl { max-width: 100% !important; }
    }
</style>
@endsection