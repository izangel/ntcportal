<x-app-layout>
<div class="py-6 bg-slate-50 min-h-screen" x-data="{ search: '', statusFilter: 'all' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Student Compliance</h1>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                    {{ $semesterInput }} | AY {{ $academicYears->firstWhere('id', $ayId)->start_year ?? '' }}-{{ $academicYears->firstWhere('id', $ayId)->end_year ?? '' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <input x-model="search" type="text" placeholder="Search student name..." 
                    class="text-xs border-slate-200 rounded-lg focus:ring-blue-500 w-64 shadow-sm">
                
                <select x-model="statusFilter" class="text-xs border-slate-200 rounded-lg focus:ring-blue-500 shadow-sm">
                    <option value="all">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="py-4 px-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Student</th>
                        <th class="py-4 px-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Progress</th>
                        <th class="py-4 px-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Percentage</th>
                        <th class="py-4 px-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($students as $student)
                        @php 
                            $percent = $student->total_subjects > 0 ? ($student->completed_count / $student->total_subjects) * 100 : 0;
                            $status = $student->is_complete ? 'completed' : 'pending';
                        @endphp
                        <tr x-show="(search === '' || '{{ strtolower($student->last_name . ' ' . $student->first_name) }}'.includes(search.toLowerCase())) && (statusFilter === 'all' || statusFilter === '{{ $status }}')"
                            class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $student->last_name }}, {{ $student->first_name }}</div>
                                <div class="text-[9px] text-slate-400 font-bold uppercase">{{ $student->student_id_number }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 bg-slate-100 h-1.5 rounded-full overflow-hidden max-w-[100px]">
                                        <div class="h-full {{ $student->is_complete ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="text-[11px] font-black text-slate-600">{{ $student->completed_count }}/{{ $student->total_subjects }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center text-xs font-bold text-slate-500">
                                {{ number_format($percent, 0) }}%
                            </td>
                            <td class="py-4 px-6 text-right">
                                @if($student->is_complete)
                                    <span class="px-2 py-1 rounded bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase">Complete</span>
                                @else
                                    <span class="px-2 py-1 rounded bg-amber-50 text-amber-600 text-[9px] font-black uppercase">Incomplete</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-app-layout>