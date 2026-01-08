<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="lg:col-span-2 bg-gray-900 rounded-[3rem] p-12 text-white shadow-2xl shadow-gray-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-50">Overall 360° Weighted Rating</p>
                        <div class="flex items-baseline gap-4 mt-4">
                            <h1 class="text-8xl font-black tracking-tighter">{{ number_format($finalScore, 2) }}</h1>
                            <span class="text-2xl font-bold opacity-30">/ 5.00</span>
                        </div>
                        <div class="mt-8 flex items-center gap-4">
                            <span class="px-4 py-2 bg-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest border border-white/20">
                                Status: {{ $finalScore >= 4.0 ? 'Outstanding' : 'Satisfactory' }}
                            </span>
                            <p class="text-xs text-gray-400 font-medium italic">*Calculated at 25% weight per group.</p>
                        </div>
                    </div>
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl"></div>
                </div>

                <div class="bg-white rounded-[3rem] p-8 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Source Breakdown</h4>
                    <div class="space-y-4">
                        @foreach($groupScores as $type => $score)
                            <div>
                                <div class="flex justify-between text-[11px] font-black uppercase mb-2">
                                    <span class="text-gray-400">{{ $type }}</span>
                                    <span class="text-gray-900">{{ number_format($score, 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-50 h-2 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-600 rounded-full" style="width: {{ ($score/5)*100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div x-data="{ activeTab: 'peer' }" class="bg-white rounded-[3rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex border-b border-gray-50 px-10 py-6 gap-8 bg-gray-50/30 overflow-x-auto">
                    @foreach(['peer', 'self', 'supervisor', 'student'] as $type)
                        <button @click="activeTab = '{{ $type }}'" 
                                :class="activeTab === '{{ $type }}' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-400'"
                                class="pb-2 text-[10px] font-black uppercase tracking-[0.2em] transition-all outline-none">
                            {{ $type === 'student' ? 'Client/Student' : ucfirst($type) }}
                        </button>
                    @endforeach
                </div>

                <div class="p-10">
                    
                    @foreach($allQuestions as $type => $questions)
                        <div x-show="activeTab === '{{ $type }}'" x-transition x-cloak>
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-[10px] font-black text-gray-300 uppercase tracking-widest border-b border-gray-50">
                                        <th class="py-4">Competency / Question Description</th>
                                        <th class="py-4 text-right">Weighted Avg</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($questions as $key => $text)
                                        <tr>
                                            <td class="py-5 pr-10">
                                                <p class="text-sm font-bold text-gray-700 leading-snug">{{ $text }}</p>
                                            </td>
                                            <td class="py-5 text-right">
                                                <span class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 text-xs font-black rounded-xl">
                                                    {{ number_format($evals->where('evaluator_type', $type)->avg("ratings.$key"), 2) ?: '0.00' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="py-10 text-center text-gray-400">No data submitted for this category.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-4">
                <button onclick="window.print()" class="px-8 py-4 bg-white border border-gray-200 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-all">
                    Download PDF Report
                </button>
            </div>
        </div>
    </div>
</x-app-layout>