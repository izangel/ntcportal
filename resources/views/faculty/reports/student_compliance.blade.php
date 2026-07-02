<x-app-layout>
<style>
    [x-cloak] { display: none !important; }

    /* SCREEN ONLY STYLING */
    .table-container { transition: all 0.3s ease; }
    
    /* PDF / PRINT SPECIFIC STYLING */
    @media print {
        @page { margin: 0.5cm; size: portrait; }
        nav, .sidebar, .no-print, button, form, .filter-section { display: none !important; }
        .py-4 { padding: 0 !important; }
        .max-w-7xl { max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
        body { background: white !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        
        .compact-table { width: 100% !important; border-collapse: collapse !important; table-layout: fixed !important; border: 1px solid #000 !important; }
        .compact-table th { background-color: #f1f5f9 !important; border: 0.5pt solid #cbd5e1 !important; padding: 2px 4px !important; font-size: 8pt !important; text-transform: uppercase !important; }
        .compact-table td { padding: 1px 4px !important; border: 0.5pt solid #e2e8f0 !important; font-size: 8pt !important; line-height: 1.1 !important; vertical-align: middle !important; }
        
        /* Hide expanded details in PDF by default to save paper, 
           UNLESS you want them printed, in which case remove the line below */
        .expanded-details { display: none !important; }
    }
</style>

<div class="py-4 bg-slate-50 min-h-screen font-sans" x-data="{ search: '', statusFilter: 'all' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- HEADER & FILTERS --}}
        <div class="mb-4 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">Student Evaluation Compliance Report</h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase">
                    {{ $semesterInput }} | AY {{ $academicYears->firstWhere('id', $ayId)->start_year ?? '' }}-{{ $academicYears->firstWhere('id', $ayId)->end_year ?? '' }}
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-2 no-print filter-section">
                {{-- Section Filter --}}
                <form action="{{ route('faculty.reports.student_compliance') }}" method="GET" class="flex items-end gap-2">
                    <input type="hidden" name="academic_year_id" value="{{ $ayId }}">
                    <input type="hidden" name="semester" value="{{ $semesterInput }}">
                    <div class="flex flex-col">
                        <label class="text-[9px] font-bold text-slate-400 uppercase mb-1 ml-1">Section</label>
                        <select name="section_id" onchange="this.form.submit()" class="text-[10px] border-slate-200 rounded-md h-8 min-w-[130px] shadow-sm">
                            <option value="">All Sections</option>
                            @foreach($availableSections as $section)
                                <option value="{{ $section->id }}" {{ ($sectionFilter ?? '') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                {{-- Status Filter --}}
                <div class="flex flex-col">
                    <label class="text-[9px] font-bold text-slate-400 uppercase mb-1 ml-1">Status</label>
                    <select x-model="statusFilter" class="text-[10px] border-slate-200 rounded-md h-8 shadow-sm">
                        <option value="all">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="flex flex-col">
                    <label class="text-[9px] font-bold text-slate-400 uppercase mb-1 ml-1">Quick Search</label>
                    <input x-model="search" type="text" placeholder="Name or ID..." class="text-[10px] border-slate-200 rounded-md h-8 w-40 shadow-sm">
                </div>

                <button onclick="window.print()" class="h-8 px-4 bg-slate-900 text-white rounded-md text-[9px] font-black uppercase hover:bg-slate-700 transition-all shadow-md">
                    Generate PDF
                </button>
            </div>
        </div>

        {{-- MAIN DATA TABLE --}}
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
            <table class="compact-table w-full text-left table-fixed border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="py-2 px-3 text-[9px] font-black text-slate-500 uppercase tracking-wider w-1/3">Student Name</th>
                        <th class="py-2 px-3 text-[9px] font-black text-slate-500 uppercase tracking-wider text-center w-24">ID Number</th>
                        <th class="py-2 px-3 text-[9px] font-black text-slate-500 uppercase tracking-wider text-center w-24">Section</th>
                        <th class="py-2 px-3 text-[9px] font-black text-slate-500 uppercase tracking-wider text-center w-32">Progress</th>
                        <th class="py-2 px-3 text-[9px] font-black text-slate-500 uppercase tracking-wider text-right w-20">Status</th>
                    </tr>
                </thead>
                @foreach($students as $student)
                    @php 
                        $percent = $student->total_subjects > 0 ? ($student->completed_count / $student->total_subjects) * 100 : 0;
                        $status = $student->is_complete ? 'completed' : 'pending';
                        $sectionName = $student->current_section_name ?? '---';
                    @endphp
                    
                    {{-- Alpine Data Wrapper for Expansion --}}
                    <tbody x-data="{ open: false }" 
                           x-show="(search === '' || '{{ strtolower($student->last_name . ' ' . $student->first_name) }}'.includes(search.toLowerCase()) || '{{ strtolower($student->student_id_number) }}'.includes(search.toLowerCase())) && (statusFilter === 'all' || statusFilter === '{{ $status }}')"
                           class="border-b border-slate-100 last:border-0">
                        
                        {{-- Clickable Row --}}
                        <tr @click="open = !open" class="hover:bg-blue-50/40 cursor-pointer transition-colors group">
                            <td class="py-1 px-3 text-[11px] font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <svg :class="open ? 'rotate-90' : ''" class="w-2.5 h-2.5 text-slate-400 transition-transform no-print" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                    <span>{{ $student->last_name }}, {{ $student->first_name }}</span>
                                </div>
                            </td>
                            
                            <td class="py-1 px-3 text-[10px] text-slate-500 text-center font-medium">{{ $student->student_id_number }}</td>
                            <td class="py-1 px-3 text-center text-[10px] font-black text-blue-600 uppercase tracking-tighter">{{ $sectionName }}</td>
                            
                            <td class="py-1 px-3">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="flex-1 bg-slate-100 h-1.5 rounded-full overflow-hidden max-w-[50px] no-print">
                                        <div class="h-full {{ $student->is_complete ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-700 tracking-tighter">{{ $student->completed_count }}/{{ $student->total_subjects }}</span>
                                </div>
                            </td>

                            <td class="py-1 px-3 text-right">
                                <span class="text-[9px] font-black uppercase tracking-tighter {{ $student->is_complete ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $student->is_complete ? 'Complete' : 'Pending' }}
                                </span>
                            </td>
                        </tr>

                        {{-- Expanded Detail Drawer --}}
                        <tr x-show="open" x-cloak x-collapse class="bg-slate-50/50 expanded-details no-print">
                            <td colspan="5" class="px-8 py-2 border-l-2 border-blue-400">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    @foreach($student->subjects as $subject)
                                        <div class="bg-white p-1.5 rounded border border-slate-200 flex items-center justify-between shadow-sm">
                                            <div class="truncate mr-2">
                                                <p class="text-[9px] font-black text-slate-800 truncate leading-none mb-1">{{ $subject->course->code ?? 'N/A' }}</p>
                                                <p class="text-[8px] text-slate-400 truncate font-bold uppercase">{{ $subject->faculty->last_name ?? 'No Instructor' }}</p>
                                            </div>
                                            <div class="shrink-0">
                                                @if($subject->has_been_evaluated)
                                                    <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                @else
                                                    <svg class="w-3 h-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </table>
        </div>

        <div class="mt-4 hidden print:block text-right">
            <p class="text-[8px] font-bold text-slate-400 italic uppercase tracking-widest">Report Date: {{ now()->format('M d, Y') }}</p>
        </div>
    </div>
</div>
</x-app-layout>