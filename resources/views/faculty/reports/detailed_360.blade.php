<x-app-layout>
<div class="py-6 bg-slate-50 min-h-screen font-sans" x-data="{ activeTab: 'overall' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Slimmed Header --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="h-px w-6 bg-blue-600"></span>
                    <span class="text-[9px] font-bold uppercase text-blue-600 tracking-widest">Faculty Analytics</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $employee->first_name }} {{ $employee->last_name }}
                </h1>
                <p class="text-xs font-medium text-slate-500">
                    {{ $semesterInput }} | AY {{ $activeSem->academicYear->start_year }}-{{ $activeSem->academicYear->end_year }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-white px-5 py-2.5 rounded-xl shadow-sm border border-slate-200 flex items-center gap-4">
                    <div class="text-left">
                        <p class="text-[9px] font-bold text-slate-400 uppercase leading-none mb-1">Mean Score</p>
                        <p class="text-2xl font-black text-slate-900 leading-none">{{ number_format($finalScore, 2) }}</p>
                    </div>
                    <div class="h-8 w-px bg-slate-100"></div>
                    <button onclick="window.print()" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Compact Navigation --}}
        <div class="inline-flex p-1 bg-slate-200/50 rounded-lg mb-6">
            @foreach(['overall', 'student', 'peer', 'supervisor', 'self'] as $tab)
                <button @click="activeTab = '{{ $tab }}'" 
                    :class="activeTab === '{{ $tab }}' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                    class="px-5 py-1.5 rounded-md text-[11px] font-bold uppercase tracking-wide transition-all">
                    {{ $tab === 'student' ? 'Student' : ucfirst($tab) }}
                </button>
            @endforeach
        </div>

        {{-- Content Container --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            {{-- OVERALL SUMMARY --}}
            <div x-show="activeTab === 'overall'" class="p-6 lg:p-8 animate-fadeIn" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($groupScores as $type => $score)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase mb-1">{{ $type }}</h4>
                            <div class="flex items-baseline gap-2 mb-3">
                                <span class="text-xl font-black text-slate-800">{{ number_format($score, 2) }}</span>
                                <span class="text-[9px] text-slate-400 font-bold">/ 5.00</span>
                            </div>
                            <div class="w-full bg-slate-200 h-1 rounded-full">
                                <div class="h-full bg-blue-500 rounded-full" style="width: {{ ($score/5)*100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- DETAILED TABS --}}
            @foreach(['student', 'peer', 'supervisor', 'self'] as $type)
                <div x-show="activeTab === '{{ $type }}'" class="p-6 lg:p-8" x-cloak x-transition>
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-50">
                        <h3 class="text-lg font-bold text-slate-800 capitalize">{{ $type }} Evaluation Details</h3>
                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-lg">Mean: {{ number_format($groupScores[$type], 2) }}</span>
                    </div>

                    @php $categories = $allQuestions[$type] ?? []; @endphp
                    @foreach($categories as $category)
                        <div class="mb-8 last:mb-0">
                            @if(is_array($category) && isset($category['title']))
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-[10px] font-bold bg-slate-800 text-white px-2 py-0.5 rounded">{{ $category['id'] ?? '?' }}</span>
                                    <h4 class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ $category['title'] }}</h4>
                                </div>
                            @endif

                            <div class="border border-slate-100 rounded-xl overflow-hidden">
                                <table class="w-full text-left border-collapse">
                                    <tbody class="divide-y divide-slate-50">
                                        @php 
                                            $questionsToLoop = (is_array($category) && isset($category['questions'])) ? $category['questions'] : [$category]; 
                                        @endphp
                                        @foreach($questionsToLoop as $index => $q)
                                            @php
                                                $key = is_array($q) ? ($q['k'] ?? $index) : $index;
                                                $text = is_array($q) ? ($q['t'] ?? 'Missing Text') : $q;
                                                $qAvg = $allEvals->where('evaluator_type', $type)
                                                    ->map(fn($e) => (is_array($e->ratings) ? $e->ratings : json_decode($e->ratings, true))[$key] ?? null)
                                                    ->filter(fn($v) => !is_null($v))->average() ?? 0;
                                            @endphp
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="py-2.5 px-4 text-xs text-slate-600 leading-snug">{{ $text }}</td>
                                                <td class="py-2.5 px-4 text-right w-20 text-xs font-bold text-slate-900">{{ number_format($qAvg, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    {{-- Feedback Section --}}
                    <div class="mt-10 pt-6 border-t border-slate-100">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase mb-4 tracking-widest">Qualitative Feedback</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $typeComments = $allEvals->where('evaluator_type', $type)
                                    ->filter(fn($e) => !empty($e->comments) || !empty($e->aspects_helped) || !empty($e->aspects_improved));
                            @endphp
                            @forelse($typeComments as $eval)
                                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/30">
                                    <p class="text-xs text-slate-700 italic leading-relaxed mb-2">"<span class="font-bold uppercase">Overall:</span> {{ $eval->comments }}"</p>
                                    @if($type === 'student' && ($eval->aspects_helped || $eval->aspects_improved))
                                        <div class="flex flex-col gap-1.5 mt-2 border-t border-slate-100 pt-2">
                                            @if($eval->aspects_helped)
                                                <p class="text-[10px] text-blue-600"><span class="font-bold uppercase">Impact:</span> {{ $eval->aspects_helped }}</p>
                                            @endif
                                            @if($eval->aspects_improved)
                                                <p class="text-[10px] text-amber-600"><span class="font-bold uppercase">Growth Opportunity:</span> {{ $eval->aspects_improved }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 italic">No feedback entries.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
</x-app-layout>