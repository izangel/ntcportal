<x-app-layout>
<div class="py-6 bg-slate-50 min-h-screen font-sans" x-data="{ search: '', activeTab: 'all' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header & Filters --}}
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="h-px w-6 bg-blue-600"></span>
                        <span class="text-[9px] font-bold uppercase text-blue-600 tracking-widest">Administrative Portal</span>
                    </div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Faculty Evaluation Summary</h1>
                    <p class="text-xs font-medium text-slate-500">
                        Showing results for: 
                        <span class="text-blue-600 font-bold">
                            {{ $semesterInput }} Semester, AY 
                            @foreach($academicYears as $ay)
                                @if($ay->id == $ayId) {{ $ay->start_year }}-{{ $ay->end_year }} @endif
                            @endforeach
                        </span>
                    </p>
                    <p class="text-xs font-medium text-slate-500">Overview of 360° performance scores across all departments.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('faculty.reports.summary') }}" method="GET" class="flex items-center gap-2 bg-white p-1.5 rounded-xl shadow-sm border border-slate-200">
                        <select name="academic_year_id" class="text-[11px] font-bold border-none focus:ring-0 bg-transparent text-slate-600 uppercase tracking-tighter">
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $ayId == $ay->id ? 'selected' : '' }}>
                                    AY {{ $ay->start_year }}-{{ $ay->end_year }}
                                </option>
                            @endforeach
                        </select>
                        <div class="h-4 w-px bg-slate-200"></div>
                        <select name="semester" class="text-[11px] font-bold border-none focus:ring-0 bg-transparent text-slate-600 uppercase tracking-tighter">
                            <option value="First Semester" {{ $semesterInput == 'First Semester' ? 'selected' : '' }}>First Semester</option>
                            <option value="Second Semester" {{ $semesterInput == 'Second Semester' ? 'selected' : '' }}>Second Semester</option>
                            <option value="Summer" {{ $semesterInput == 'Summer' ? 'selected' : '' }}>Summer</option>
                        </select>
                        <button type="submit" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Search & Quick Stats --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                <div class="lg:col-span-2 relative">
                    <input x-model="search" type="text" placeholder="Search by faculty name or department..." 
                        class="w-full bg-white border-slate-200 rounded-xl py-3 pl-11 text-sm focus:ring-blue-500 shadow-sm placeholder:text-slate-400">
                    <svg class="w-5 h-5 absolute left-4 top-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <div class="bg-blue-600 rounded-xl shadow-sm border border-blue-700 p-3 flex items-center justify-between text-white">
                    <div>
                        <p class="text-[9px] font-bold uppercase opacity-80 leading-none mb-1">Total Faculty</p>
                        <p class="text-xl font-black leading-none">{{ $faculties->count() }}</p>
                    </div>
                    <svg class="w-8 h-8 opacity-20" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="py-4 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Faculty Member</th>
                            <th class="py-4 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Student</th>
                            <th class="py-4 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Peer</th>
                            <th class="py-4 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Supervisor</th>
                            <th class="py-4 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Self</th>
                            <th class="py-4 px-6 text-[10px] font-bold text-blue-600 uppercase tracking-widest text-right">Overall Mean</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($faculties as $faculty)
                            @php
                                $fullName = strtolower($faculty->last_name . ' ' . $faculty->first_name);
                                $deptName = strtolower($faculty->department->name ?? '');
                            @endphp
                            <tr x-show="search === '' || '{{ $fullName }}'.includes(search.toLowerCase()) || '{{ $deptName }}'.includes(search.toLowerCase())"
                                class="hover:bg-slate-50 transition-all cursor-pointer group"
                                onclick="window.location='{{ route('faculty.reports.view', ['faculty_id' => $faculty->id, 
    'academic_year_id' => $ayId, 
    'semester' => $semesterInput]) }}'">
                                
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs uppercase group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                                            {{ substr($faculty->first_name, 0, 1) }}{{ substr($faculty->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors leading-none mb-1">
                                                {{ $faculty->last_name }}, {{ $faculty->first_name }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-medium uppercase tracking-tight">
                                                {{ $faculty->department->name ?? 'Unassigned' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                @foreach(['student', 'peer', 'supervisor', 'self'] as $type)
                                    <td class="py-4 px-4 text-center">
                                        @php $score = $faculty->group_scores[$type] ?? 0; @endphp
                                        <span class="text-xs font-bold {{ $score > 0 ? 'text-slate-700' : 'text-slate-300 italic' }}">
                                            {{ $score > 0 ? number_format($score, 2) : '—' }}
                                        </span>
                                    </td>
                                @endforeach

                                <td class="py-4 px-6 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-sm font-black text-slate-900">
                                            {{ number_format($faculty->final_mean, 2) }}
                                        </span>
                                        <div class="w-16 bg-slate-100 h-1 rounded-full mt-1 overflow-hidden">
                                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ ($faculty->final_mean / 5) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-20 text-center">
                                    <p class="text-sm text-slate-400 italic">No faculty records found for the selected period.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
</x-app-layout>