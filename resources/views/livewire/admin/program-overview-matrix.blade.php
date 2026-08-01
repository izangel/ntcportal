<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    
    <!-- 1. HEADER, PROGRAM & BATCH SELECTORS -->
    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gray-200 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Outcome Attainment Matrix</h1>
            <p class="text-xs text-gray-500 mt-1">Course Outcomes mapped to Program Outcomes with integrated completion and attainment metrics.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
            <!-- Program Dropdown -->
            <div class="w-full sm:w-64">
                <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">Select Program</label>
                <select wire:model.live="selectedProgramId" class="w-full rounded-xl border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs py-2">
                    <option value="">-- Choose Program --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->code ?? '' }} - {{ $program->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Batch Year Dropdown -->
            <div class="w-full sm:w-48">
                <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">Batch / Cohort</label>
                <select wire:model.live="selectedBatchYear" class="w-full rounded-xl border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs py-2">
                    <option value="">All Batches</option>
                    @foreach($batchYears as $year)
                        <option value="{{ $year }}">Batch {{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- 1.5 DEBUG INSPECTION TABLE: STUDENT ROSTER -->
    @if($selectedBatchYear)
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-200 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Debug Inspection: Batch {{ $selectedBatchYear }} Roster (Total: {{ $debugStudents->count() }})
                </h3>
            </div>

            @if($debugStudents->isNotEmpty())
                <div class="overflow-x-auto max-h-60 border border-gray-200 rounded-xl">
                    <table class="w-full text-left text-xs bg-white border-collapse">
                        <thead class="bg-gray-100 text-gray-700 uppercase font-semibold text-[11px] border-b border-gray-200 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-2 border-r border-gray-200">Student ID</th>
                                <th class="px-3 py-2 border-r border-gray-200">Last Name</th>
                                <th class="px-3 py-2 border-r border-gray-200">First Name</th>
                                <th class="px-3 py-2 border-r border-gray-200">Program</th>
                                <th class="px-3 py-2">Section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($debugStudents as $student)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-3 py-2 font-mono border-r border-gray-100">{{ $student->student_number ?? $student->id }}</td>
                                    <td class="px-3 py-2 font-medium border-r border-gray-100">{{ $student->last_name ?? '-' }}</td>
                                    <td class="px-3 py-2 font-medium border-r border-gray-100">{{ $student->first_name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-600 border-r border-gray-100">
                                        {{ $student->sections->map(fn($s) => $s->program->code ?? $s->program->name ?? 'N/A')->unique()->implode(', ') }}
                                    </td>
                                    <td class="px-3 py-2 font-bold text-indigo-600">
                                        {{ $student->sections->pluck('name')->implode(', ') ?: 'No Section' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-gray-500 italic p-2">No students matched batch "{{ $selectedBatchYear }}".</p>
            @endif
        </div>
    @endif

    @if($selectedProgram)
        <!-- 2. PEO & PO OVERVIEW PANELS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- PEO Panel -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-200 flex flex-col">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 mb-3">
                    <h2 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        Program Educational Objectives (PEOs)
                    </h2>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    @forelse($selectedProgram->programEducationalObjectives ?? [] as $peo)
                        <div class="p-2.5 bg-gray-50/80 rounded-xl border border-gray-100">
                            <span class="font-bold text-indigo-600 text-xs">{{ $peo->code ?? 'PEO' }}</span>
                            <p class="text-xs text-gray-700 mt-0.5 leading-relaxed">{{ $peo->description }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">No PEOs configured for this program.</p>
                    @endforelse
                </div>
            </div>

            <!-- PO Panel -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-200 flex flex-col">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 mb-3">
                    <h2 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        Program Outcomes (POs)
                    </h2>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    @forelse($selectedProgram->programOutcomes as $po)
                        <div class="p-2.5 bg-gray-50/80 rounded-xl border border-gray-100 flex items-start gap-2.5">
                            <span class="font-bold text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md shrink-0">{{ $po->code }}</span>
                            <p class="text-xs text-gray-700 leading-relaxed">{{ $po->description }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">No POs configured for this program.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- MATRIX LEGEND BANNER -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-indigo-900 text-white px-5 py-3 rounded-xl shadow-xs text-xs">
            <div class="flex items-center gap-2 font-medium">
                <span class="font-bold uppercase tracking-wider text-indigo-200">Active Filter:</span>
                <span class="bg-indigo-800 border border-indigo-700 px-2.5 py-0.5 rounded-md font-bold text-indigo-100">
                    {{ $selectedBatchYear ? 'Batch ' . $selectedBatchYear : 'All Batches' }}
                </span>
            </div>

            <!-- I, G, A Map Legend -->
            <div class="flex items-center gap-3 text-[11px]">
                <span class="text-indigo-300 font-bold uppercase tracking-wider">Mapping Levels:</span>
                <span class="inline-flex items-center gap-1 font-semibold"><span class="w-5 h-5 bg-blue-100 text-blue-900 rounded inline-flex items-center justify-center text-[10px] font-extrabold">I</span> Introductory</span>
                <span class="inline-flex items-center gap-1 font-semibold"><span class="w-5 h-5 bg-amber-100 text-amber-900 rounded inline-flex items-center justify-center text-[10px] font-extrabold">G</span> Enabling</span>
                <span class="inline-flex items-center gap-1 font-semibold"><span class="w-5 h-5 bg-emerald-100 text-emerald-900 rounded inline-flex items-center justify-center text-[10px] font-extrabold">A</span> Demonstrative</span>
            </div>
        </div>

        <!-- 3. MAIN MATRIX TABLE -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-300 overflow-hidden">
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full border-collapse text-xs text-left">
                    <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider border-b border-gray-300 sticky top-0 z-10 shadow-2xs">
                        <tr>
                            <!-- Course Header -->
                            <th class="px-4 py-3 border-r border-gray-300 w-64 bg-gray-100">Course Overview</th>
                            
                            <!-- CLO Header -->
                            <th class="px-4 py-3 border-r border-gray-300 min-w-[320px] bg-gray-100">Course Learning Outcomes (CLO) & Attainment</th>
                            
                            <!-- Dynamic PO Columns -->
                            @foreach($selectedProgram->programOutcomes as $po)
                                <th class="px-2 py-3 text-center border-r border-gray-300 min-w-[55px] bg-gray-100" title="{{ $po->description }}">
                                    {{ $po->code }}
                                </th>
                            @endforeach

                            <!-- Assessment Tasks Header -->
                            <th class="px-4 py-3 min-w-[200px] bg-gray-100">Assessment Tasks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300 bg-white">
                        @forelse($courses as $course)
                            @php
                                $cloCount = max(1, count($course->learningOutcomes));
                            @endphp

                            @forelse($course->learningOutcomes as $index => $clo)
                                <tr class="hover:bg-gray-50/60 transition" wire:key="course-clo-row-{{ $course->id }}-{{ $clo->id }}">
                                    <!-- 1. COURSE DETAILS + MATCHED COURSE BLOCKS & SECTIONS + ENROLLED + COMPLETION RATE -->
                                <!-- 1. COURSE DETAILS + MATCHED COURSE BLOCKS + STUDENT STATS + COMPLETION RATE -->
                                @if($index === 0)
                                    <td rowspan="{{ $cloCount }}" class="px-4 py-4 align-top border-r border-gray-300 bg-gray-50/40 space-y-3">
                                        <div>
                                            <span class="text-indigo-700 font-extrabold text-sm block">{{ $course->code }}</span>
                                            <span class="text-gray-900 font-bold text-xs mt-0.5 block leading-tight">{{ $course->title ?? $course->name }}</span>
                                        </div>

                                        <!-- COURSE BLOCKS & SECTIONS -->
                                        <div class="pt-2 border-t border-gray-200/80 space-y-1.5">
                                            <span class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Course Block & Section:</span>

                                            @forelse($course->matched_course_blocks ?? [] as $block)
                                                <div class="p-2 bg-white rounded-lg border border-gray-200 shadow-2xs text-[11px] space-y-1">
                                                    <div class="font-bold text-indigo-900 flex items-center justify-between">
                                                        <span>Block: {{ $block->name ?? $block->code ?? 'Block #'.$block->id }}</span>
                                                        <span class="text-[9px] bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-mono">ID: {{ $block->id }}</span>
                                                    </div>

                                                    @forelse($block->sections as $section)
                                                        <div class="text-[10px] text-gray-600 bg-gray-50 p-1.5 rounded border border-gray-100 flex flex-col gap-0.5">
                                                            <div class="text-[9.5px] font-bold text-gray-500 uppercase tracking-wider">
                                                                Program: <span class="text-gray-900 font-semibold">{{ $section->program->code ?? $section->program->name ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="font-bold text-indigo-600 text-xs">
                                                                Section: <span class="text-gray-800">{{ $section->name }}</span>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <span class="text-[10px] text-gray-400 italic block">No active sections assigned</span>
                                                    @endforelse
                                                </div>
                                            @empty
                                                <span class="text-[10px] text-gray-400 italic">No matching course block</span>
                                            @endforelse
                                        </div>
                                        
                                        <!-- STUDENT METRICS & CLO ATTAINMENT -->
                                        <div class="pt-3 border-t border-gray-200/80 space-y-2">
                                            <!-- Total Students -->
                                            <div class="flex items-center justify-between bg-white px-2.5 py-1.5 rounded-md border border-gray-200 shadow-2xs text-[11px]">
                                                <span class="text-gray-600 font-medium">Total Students:</span>
                                                <span class="font-extrabold text-gray-900">{{ $course->total_students ?? 0 }}</span>
                                            </div>

                                            <!-- Students with Marks -->
                                            <div class="flex items-center justify-between bg-white px-2.5 py-1.5 rounded-md border border-gray-200 shadow-2xs text-[11px]">
                                                <span class="text-gray-600 font-medium">With Marks:</span>
                                                <span class="font-extrabold text-indigo-700">{{ $course->total_students_with_marks ?? 0 }}</span>
                                            </div>

                                            <!-- Overall CLO Attainment / Completion Rate -->
                                                                                        
                                            <div>
                                                <span class="text-[10px] uppercase font-bold text-gray-500 block mb-1">CLO Attainment Rate:</span>
                                                
                                                @if(($course->total_students_with_marks ?? 0) > 0 && !is_null($course->completion_rate))
                                                    <span class="inline-block font-extrabold text-xs w-full text-center {{ $course->completion_rate >= 75 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-amber-700 bg-amber-50 border-amber-200' }} border px-2.5 py-1.5 rounded-md shadow-2xs">
                                                        {{ number_format($course->completion_rate, 1) }}%
                                                    </span>
                                                @else
                                                    <span class="inline-block font-bold text-xs w-full text-center text-gray-500 bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-md italic shadow-2xs">
                                                        N/A
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                @endif

                                    <!-- 2. CLO DESCRIPTION + SCOPED BATCH ATTAINMENT + STUDENT BREAKDOWN -->
                                    <td class="px-4 py-3 align-top border-r border-gray-300 space-y-3">
                                        <div>
                                            <span class="font-bold text-indigo-600 block sm:inline">{{ $clo->code }}:</span>
                                            <span class="text-gray-800 font-medium leading-relaxed">{{ $clo->description }}</span>
                                        </div>

                                        <!-- Attainment Badge -->
                                        <div class="pt-1">
                                            @if(isset($clo->student_breakdown) && $clo->student_breakdown->isNotEmpty() && !is_null($clo->attainment))
                                                @php
                                                    $attainment = $clo->attainment;
                                                    $badgeStyle = $attainment >= 75 
                                                        ? 'bg-emerald-50 border-emerald-200 text-emerald-800' 
                                                        : ($attainment >= 50 ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-rose-50 border-rose-200 text-rose-800');
                                                @endphp
                                                <div class="inline-flex items-center gap-1.5 border px-2.5 py-1 rounded-md text-[11px] {{ $badgeStyle }}">
                                                    <span class="text-gray-500 font-medium">Batch Attainment:</span>
                                                    <span class="font-extrabold">{{ number_format($attainment, 1) }}%</span>
                                                </div>
                                            @else
                                                <div class="inline-flex items-center gap-1.5 border px-2.5 py-1 rounded-md text-[11px] bg-gray-50 border-gray-200 text-gray-500">
                                                    <span class="font-medium">Batch Attainment:</span>
                                                    <span class="font-bold italic">N/A</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Student Breakdown Table & List -->
                                        @if(isset($clo->student_breakdown) && $clo->student_breakdown->isNotEmpty())
                                            <div class="mt-2 pt-2 border-t border-gray-100 space-y-1.5">
                                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">
                                                    Student Marks Breakdown ({{ $clo->student_breakdown->count() }} Recorded):
                                                </span>

                                                <div class="overflow-x-auto max-h-36 rounded-lg border border-gray-200 bg-gray-50/50">
                                                    <table class="w-full text-[10px] text-left">
                                                        <thead class="bg-gray-100 text-gray-600 font-bold border-b border-gray-200 uppercase">
                                                            <tr>
                                                                <th class="px-2 py-1">Student ID</th>
                                                                <th class="px-2 py-1 text-center">Score</th>
                                                                <th class="px-2 py-1 text-right">% Score</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100">
                                                            @foreach($clo->student_breakdown as $studentScore)
                                                                <tr class="hover:bg-white">
                                                                    <td class="px-2 py-1 font-mono font-bold text-indigo-700">
                                                                        #{{ $studentScore['student_id'] }}
                                                                    </td>
                                                                    <td class="px-2 py-1 text-center font-medium text-gray-600">
                                                                        {{ round($studentScore['total_obtained'], 1) }} / {{ round($studentScore['total_max'], 1) }}
                                                                    </td>
                                                                    <td class="px-2 py-1 text-right font-extrabold {{ $studentScore['percentage'] >= 75 ? 'text-emerald-600' : ($studentScore['percentage'] >= 50 ? 'text-amber-600' : 'text-rose-600') }}">
                                                                        {{ number_format($studentScore['percentage'], 1) }}%
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-[10px] text-gray-400 italic pt-1">
                                                No assessment marks recorded for this CLO.
                                            </div>
                                        @endif
                                    </td>

                                    <!-- 3. DYNAMIC PO MATRIX COLUMNS (I, G, A) -->
                                    @foreach($selectedProgram->programOutcomes as $po)
                                        @php
                                            $mappedPo = $clo->programOutcomes->firstWhere('id', $po->id);
                                            $level = $mappedPo->pivot->level ?? null;
                                        @endphp
                                        <td class="px-2 py-3 text-center align-middle border-r border-gray-300">
                                            @if($level)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md font-extrabold text-xs shadow-2xs
                                                    {{ $level === 'I' ? 'bg-blue-100 text-blue-900 border border-blue-200' : '' }}
                                                    {{ $level === 'G' ? 'bg-amber-100 text-amber-900 border border-amber-200' : '' }}
                                                    {{ $level === 'A' ? 'bg-emerald-100 text-emerald-900 border border-emerald-200' : '' }}">
                                                    {{ $level }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <!-- 4. ASSESSMENT TASKS -->
                                    <td class="px-4 py-3 align-middle">
                                        @forelse($course->assessmentTasks as $task)
                                            <div class="text-[11px] py-1 flex justify-between items-center border-b border-gray-100 last:border-0">
                                                <span class="font-medium text-gray-700">{{ $task->title }}</span>
                                                <span class="text-[10px] bg-gray-100 text-gray-600 font-bold px-1.5 py-0.5 rounded-md border border-gray-200">{{ $task->weight_percentage }}%</span>
                                            </div>
                                        @empty
                                            <span class="text-gray-400 italic text-[11px]">-</span>
                                        @endforelse
                                    </td>

                                </tr>
                            @empty
                                <!-- Course with No CLOs Defined -->
                                <tr wire:key="course-empty-row-{{ $course->id }}">
                                    <td class="px-4 py-4 align-top border-r border-gray-300 bg-gray-50/40 space-y-2">
                                        <div class="text-indigo-700 font-bold text-sm">{{ $course->code }}</div>
                                        <div class="text-gray-800 text-xs font-medium">{{ $course->title ?? $course->name }}</div>
                                        <div class="pt-2 border-t border-gray-200">
                                            <span class="text-[10px] font-bold text-gray-500 uppercase block">Total Enrolled {{ $selectedBatchYear ? "(Batch {$selectedBatchYear})" : '' }}:</span>
                                            <span class="font-bold text-xs text-gray-800 bg-white px-2 py-0.5 rounded border border-gray-200 inline-block mt-0.5">
                                                {{ $course->total_enrolled_students ?? 0 }} Students
                                            </span>
                                        </div>
                                    </td>
                                    <td colspan="{{ count($selectedProgram->programOutcomes) + 2 }}" class="px-4 py-4 text-center text-gray-400 italic">
                                        No Learning Outcomes (CLOs) defined for this course.
                                    </td>
                                </tr>
                            @endforelse
                        @empty
                            <tr>
                                <td colspan="{{ count($selectedProgram->programOutcomes) + 3 }}" class="px-6 py-12 text-center text-gray-400 italic">
                                    No courses mapped to this program yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Unselected State -->
        <div class="bg-white border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center text-gray-500 space-y-3">
            <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-sm font-bold text-gray-700">No Program Selected</h3>
            <p class="text-xs text-gray-400 max-w-xs mx-auto">Select a program and batch cohort from the menu above to generate the matrix.</p>
        </div>
    @endif

</div>