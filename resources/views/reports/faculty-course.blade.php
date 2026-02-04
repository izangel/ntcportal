@extends('layouts.admin')

@section('content')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">{{ $course->code }} Report</h1>
                <p class="text-indigo-600 font-bold uppercase text-xs tracking-widest">{{ $course->name }} | {{ $activeSem->name }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black text-gray-400 uppercase">Total Respondents</p>
                <p class="text-2xl font-black text-gray-900">{{ $evaluations->count() }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                    <h3 class="text-sm font-black text-gray-900 uppercase mb-6 tracking-tighter">Category Performance</h3>
                    
                    <div class="space-y-6">
                        @foreach($reportData as $id => $data)
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-bold text-gray-600">{{ $id }}. {{ $data['name'] }}</span>
                                <span class="text-sm font-black text-indigo-600">{{ number_format($data['avg'], 2) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000" 
                                     style="width: {{ ($data['avg'] / 5) * 100 }}%; 
                                            background-color: {{ $data['avg'] >= 4 ? '#10b981' : ($data['avg'] >= 3 ? '#6366f1' : '#f59e0b') }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                    <h3 class="text-sm font-black text-gray-900 uppercase mb-6 tracking-tighter">Student Qualitative Feedback</h3>
                    
                    <div class="space-y-6">
                        @foreach($evaluations as $eval)
                            @if($eval->aspects_helped || $eval->aspects_improved || $eval->comments)
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                @if($eval->aspects_helped)
                                    <p class="text-[10px] font-black text-emerald-600 uppercase mb-1">What helped most:</p>
                                    <p class="text-sm text-gray-700 mb-4 italic">"{{ $eval->aspects_helped }}"</p>
                                @endif

                                @if($eval->aspects_improved)
                                    <p class="text-[10px] font-black text-amber-600 uppercase mb-1">Needs Improvement:</p>
                                    <p class="text-sm text-gray-700 mb-4 italic">"{{ $eval->aspects_improved }}"</p>
                                @endif

                                @if($eval->comments)
                                    <p class="text-[10px] font-black text-indigo-600 uppercase mb-1">Additional Comments:</p>
                                    <p class="text-sm text-gray-600 italic">"{{ $eval->comments }}"</p>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-indigo-600 p-8 rounded-[2rem] text-white shadow-xl shadow-indigo-100">
                    <p class="text-[10px] font-bold uppercase opacity-80">Overall Course Rating</p>
                    <div class="flex items-baseline gap-2 mt-2">
                        <span class="text-5xl font-black">{{ number_format($evaluations->avg('rating'), 2) }}</span>
                        <span class="text-xl opacity-60">/ 5.0</span>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-indigo-500/50">
                        <p class="text-[10px] font-bold uppercase opacity-80 mb-2">Verdict</p>
                        @php $avg = $evaluations->avg('rating'); @endphp
                        <span class="px-3 py-1 bg-white/20 rounded-lg text-xs font-black uppercase">
                            {{ $avg >= 4.5 ? 'Excellent' : ($avg >= 3.5 ? 'Very Good' : 'Satisfactory') }}
                        </span>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-4">Rating Legend</p>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2 text-[10px] font-bold text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 4.0 - 5.0 Excellent
                        </li>
                        <li class="flex items-center gap-2 text-[10px] font-bold text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 3.0 - 3.9 Satisfactory
                        </li>
                        <li class="flex items-center gap-2 text-[10px] font-bold text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Below 3.0 Needs Improvement
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection