<div class="border border-slate-100 rounded-xl overflow-hidden shadow-sm">
    <table class="w-full text-left border-collapse bg-white">
        <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
                <th class="py-2 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest w-16">ID</th>
                <th class="py-2 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Assessment Criteria</th>
                <th class="py-2 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right w-24">Score</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @foreach($questions as $q)
                @php
                    // 1. Extract Key and Text
                    $key = is_array($q) ? ($q['k'] ?? $loop->iteration) : $loop->iteration;
                    $text = is_array($q) ? ($q['t'] ?? 'Question text missing') : $q;

                    // 2. Calculate the average for this specific question across all evaluations of this type
                    $qAvg = $allEvals->where('evaluator_type', $type)
                        ->map(function($e) use ($key) {
                            // Ensure ratings is an array (handles both JSON and Array casts)
                            $ratings = is_array($e->ratings) ? $e->ratings : json_decode($e->ratings, true);
                            
                            // Flexible lookup: checks for 'q1', '1', or raw $key
                            $val = $ratings[$key] ?? $ratings[(string)$key] ?? null;
                            
                            return is_numeric($val) ? (float)$val : null;
                        })
                        ->filter(fn($v) => !is_null($v))
                        ->average() ?? 0;
                @endphp

                <tr class="hover:bg-slate-50/80 transition-colors group">
                    <td class="py-3 px-4">
                        <span class="text-[10px] font-black text-slate-400 group-hover:text-blue-600 transition-colors uppercase">
                            {{ $key }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-xs text-slate-600 leading-normal font-medium">
                            {{ $text }}
                        </p>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-black {{ $qAvg >= 4 ? 'text-emerald-600' : ($qAvg >= 3 ? 'text-blue-600' : 'text-amber-600') }}">
                                {{ number_format($qAvg, 2) }}
                            </span>
                            {{-- Optional mini-bar for visual density --}}
                            <div class="w-12 h-0.5 bg-slate-100 mt-1 rounded-full overflow-hidden">
                                <div class="h-full {{ $qAvg >= 4 ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ ($qAvg/5)*100 }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>