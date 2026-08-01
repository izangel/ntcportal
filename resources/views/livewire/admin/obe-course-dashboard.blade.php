<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">OBE Course Attainment Dashboard</h1>
            <p class="text-sm text-gray-600">
                @if($isAdminView)
                    All courses across programs with CLO / PO attainment, course blocks, students and faculty.
                @else
                    Your assigned courses — CLO / PO attainment, course blocks, students and assessment tasks.
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <label class="text-xs font-semibold text-gray-700">Target Threshold (%):</label>
            <input type="number" wire:model.live="thresholdPercentage" class="w-16 rounded-md border-gray-300 text-xs text-center shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @if($courses->isNotEmpty())
                <button type="button" wire:click="exportCsv"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-file-csv"></i>
                    Export CSV
                </button>
            @endif
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Program</label>
            <select wire:model.live="selectedProgramId" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- All Programs --</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}">{{ $program->code }} - {{ $program->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Academic Year</label>
            <select wire:model.live="selectedAcademicYearId" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- All Years --</option>
                @foreach($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}">{{ $academicYear->start_year }} - {{ $academicYear->end_year }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Semester</label>
            <select wire:model.live="selectedSemester" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- All Semesters --</option>
                @foreach($semesters as $semester)
                    <option value="{{ $semester }}">{{ $semester }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Courses</p>
            <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalCourses }}</h3>
            <p class="text-[10px] text-gray-400 mt-0.5">shown in this view</p>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Total Students</p>
            <h3 class="text-2xl font-extrabold text-blue-700 mt-1">{{ $totalStudents }}</h3>
            <p class="text-[10px] text-gray-400 mt-0.5">distinct across courses</p>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">CLOs</p>
            <h3 class="text-2xl font-extrabold text-indigo-700 mt-1">{{ $totalClos }}</h3>
            <p class="text-[10px] text-gray-400 mt-0.5">configured</p>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">CLOs Attained</p>
            <h3 class="text-2xl font-extrabold text-emerald-700 mt-1">
                {{ $attainedClos }}<span class="text-sm text-gray-400"> / {{ $totalClos }}</span>
            </h3>
            <p class="text-[10px] text-gray-400 mt-0.5">meet &ge; {{ $thresholdPercentage }}%</p>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Overall CLO Attainment</p>
            <h3 class="text-2xl font-extrabold {{ !is_null($overallAttainment) && $overallAttainment >= $thresholdPercentage ? 'text-emerald-700' : 'text-amber-700' }} mt-1">
                {{ !is_null($overallAttainment) ? number_format($overallAttainment, 1) . '%' : 'N/A' }}
            </h3>
            <p class="text-[10px] text-gray-400 mt-0.5">avg across assessed CLOs</p>
        </div>
    </div>

    {{-- Program Outcome Summary --}}
    @if($overallPoAttainments->isNotEmpty())
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-800">Program Outcome Attainment Summary</h3>
                <span class="text-[10px] text-gray-400">avg across filtered courses</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                @foreach($overallPoAttainments as $po)
                    <div class="rounded-lg border border-gray-200 p-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-emerald-700 text-xs">{{ $po['code'] }}</span>
                            <span class="shrink-0 font-extrabold text-xs {{ !is_null($po['score']) && $po['attained'] ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ !is_null($po['score']) ? number_format($po['score'], 1) . '%' : 'N/A' }}
                            </span>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500 leading-relaxed line-clamp-2">{{ $po['description'] }}</p>
                        @if(!is_null($po['score']))
                            <div class="mt-2 h-1.5 w-full rounded-full bg-gray-200">
                                <div class="h-1.5 rounded-full {{ $po['attained'] ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min($po['score'], 100) }}%"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Course Cards --}}
    @if($selectedAcademicYearId && $totalBlocks === 0)
        @php
            $selectedAy = $academicYears->firstWhere('id', (int) $selectedAcademicYearId);
            $latestAyWithData = $latestAyWithDataId ? $academicYears->firstWhere('id', (int) $latestAyWithDataId) : null;
            $ayHasDataGap = $selectedAy && $latestAyWithData && $latestAyWithData->id !== $selectedAy->id;
        @endphp
        <div class="bg-white border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center text-gray-500 space-y-3">
            <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="text-sm font-bold text-gray-700">No Course Blocks Found</h3>
            @if($ayHasDataGap)
                <p class="text-xs text-gray-400 max-w-sm mx-auto">
                    No course blocks exist for AY {{ $selectedAy->start_year }} - {{ $selectedAy->end_year }}.
                    The most recent academic year with course blocks is {{ $latestAyWithData->start_year }} - {{ $latestAyWithData->end_year }}.
                </p>
                <button type="button" wire:click="$set('selectedAcademicYearId', {{ $latestAyWithData->id }})"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-arrow-right"></i>
                    Go to AY {{ $latestAyWithData->start_year }} - {{ $latestAyWithData->end_year }}
                </button>
            @else
                <p class="text-xs text-gray-400 max-w-sm mx-auto">
                    No course blocks match the selected filters{{ $isAdminView ? '' : ' for your assigned load' }}.
                    Try changing the program or semester.
                </p>
            @endif
        </div>
    @else
    @forelse($batchGroups as $group)
        <div>
            <div class="flex items-center gap-3 mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    @if($group['batch'] === 'Legacy')
                        Legacy / No Batch
                    @else
                        Batch {{ $group['batch'] }}
                    @endif
                </h2>
                <span class="rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-0.5">{{ $group['courses']->count() }} course(s)</span>
                <span class="flex-1 h-px bg-gray-200"></span>
            </div>
            <div class="space-y-6">
            @foreach($group['courses'] as $course)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" wire:key="course-{{ $group['batch'] }}-{{ $course->id }}">
            {{-- Course header --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-5 py-4 bg-gray-50 border-b border-gray-200">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-indigo-700 font-extrabold text-lg">{{ $course->code }}</span>
                        <span class="text-gray-900 font-semibold text-sm">{{ $course->name }}</span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-500">
                        <span><strong class="text-gray-700">{{ $course->total_students }}</strong> students</span>
                        <span><strong class="text-gray-700">{{ $course->courseBlocks->count() }}</strong> block(s)</span>
                        <span><strong class="text-gray-700">{{ $course->clo_attainments->count() }}</strong> CLO(s)</span>
                        <span><strong class="text-gray-700">{{ $course->assessmentTasks->count() }}</strong> task(s)</span>
                        @if($course->faculty_names->isNotEmpty())
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-chalkboard-user text-gray-400"></i>
                                {{ $course->faculty_names->implode(', ') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[10px] uppercase font-bold text-gray-500 block">Course Attainment</span>
                    @if(!is_null($course->computed_completion_rate))
                        <span class="inline-block font-extrabold text-lg {{ $course->computed_completion_rate >= $thresholdPercentage ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ number_format($course->computed_completion_rate, 1) }}%
                        </span>
                    @else
                        <span class="inline-block font-bold text-sm text-gray-400 italic">N/A</span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-px bg-gray-100">
                {{-- CLOs --}}
                <div class="bg-white p-4 space-y-3">
                    <h4 class="text-[11px] font-bold text-gray-700 uppercase tracking-wider">Course Learning Outcomes</h4>
                    @forelse($course->clo_attainments as $clo)
                        <div class="rounded-lg border border-gray-200 p-3" wire:key="clo-{{ $clo->id }}" x-data="{ open: false }">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="font-bold text-indigo-700 text-xs">{{ $clo->code }}</span>
                                    @if($clo->bloomsTaxonomy)
                                        <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-semibold text-gray-500">{{ $clo->bloomsTaxonomy->code }}</span>
                                    @endif
                                </div>
                                <span class="shrink-0 font-extrabold text-xs {{ !is_null($clo->completion_rate) && $clo->completion_rate >= $thresholdPercentage ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ !is_null($clo->completion_rate) ? number_format($clo->completion_rate, 1) . '%' : 'N/A' }}
                                </span>
                            </div>
                            <p class="mt-1 text-[11px] text-gray-600 leading-relaxed">{{ $clo->description }}</p>

                            @if(!is_null($clo->completion_rate))
                                <div class="mt-2 h-1.5 w-full rounded-full bg-gray-200">
                                    <div class="h-1.5 rounded-full {{ $clo->completion_rate >= $thresholdPercentage ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min($clo->completion_rate, 100) }}%"></div>
                                </div>
                            @endif

                            <div class="mt-2 text-[10px] text-gray-500">
                                Assessed <strong>{{ $clo->students_assessed }}</strong> / <strong>{{ $clo->total_students }}</strong> students
                            </div>

                            @if($clo->student_breakdown->isNotEmpty())
                                <button type="button" x-on:click="open = !open" class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-indigo-600 hover:text-indigo-800">
                                    <span x-text="open ? '▾' : '▸'"></span>
                                    <span x-text="open ? 'Hide marks' : 'View student marks'"></span>
                                </button>

                                <div x-show="open" x-cloak class="mt-2 space-y-2">
                                    <div class="overflow-x-auto max-h-48 rounded-md border border-gray-200">
                                        <table class="w-full text-[10px]">
                                            <thead class="bg-gray-100 text-gray-600 uppercase">
                                                <tr>
                                                    <th class="px-2 py-1 text-left">Student</th>
                                                    @foreach($clo->assessment_items as $item)
                                                        <th class="px-2 py-1 text-center" title="{{ $item->task_title ?? '' }} - {{ $item->item_name }}">{{ $item->item_name }}</th>
                                                    @endforeach
                                                    <th class="px-2 py-1 text-center">Total</th>
                                                    <th class="px-2 py-1 text-right">%</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($clo->student_breakdown as $score)
                                                    <tr>
                                                        <td class="px-2 py-1">
                                                            <span class="font-semibold text-gray-800">{{ $score['student_name'] }}</span>
                                                            <span class="block font-mono text-[9px] text-gray-400">{{ $score['student_number'] }}</span>
                                                        </td>
                                                        @foreach($clo->assessment_items as $item)
                                                            @php
                                                                $itemMark = collect($score['marks'])->firstWhere('item_id', $item->id);
                                                            @endphp
                                                            <td class="px-2 py-1 text-center text-gray-500">
                                                                {{ $itemMark ? $itemMark['marks_obtained'] . '/' . $itemMark['max_marks'] : '—' }}
                                                            </td>
                                                        @endforeach
                                                        <td class="px-2 py-1 text-center font-semibold text-gray-800">{{ round($score['total_obtained'], 1) }}/{{ round($score['total_max'], 1) }}</td>
                                                        <td class="px-2 py-1 text-right font-bold {{ $score['percentage'] >= $thresholdPercentage ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($score['percentage'], 1) }}%</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-[10px] text-gray-500">
                                        Attainment = (<span class="font-mono">{{ $clo->student_breakdown->pluck('percentage')->map(fn ($p) => number_format($p, 1))->implode(' + ') }}</span>) ÷ <strong>{{ $clo->student_breakdown->count() }}</strong> = <strong class="text-indigo-700">{{ number_format($clo->completion_rate, 1) }}%</strong>
                                    </div>
                                </div>
                            @else
                                <div class="mt-2 text-[10px] italic text-gray-400">No assessment marks recorded for this CLO.</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-[11px] italic text-gray-400">No CLOs configured for this course.</p>
                    @endforelse
                </div>

                {{-- PO Attainment --}}
                <div class="bg-white p-4 space-y-3">
                    <h4 class="text-[11px] font-bold text-gray-700 uppercase tracking-wider">Program Outcome Attainment</h4>
                    @forelse($course->po_attainments as $po)
                        <div class="rounded-lg border border-gray-200 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-emerald-700 text-xs">{{ $po['code'] }}</span>
                                <span class="shrink-0 font-extrabold text-xs {{ !is_null($po['score']) && $po['attained'] ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ !is_null($po['score']) ? number_format($po['score'], 1) . '%' : 'N/A' }}
                                </span>
                            </div>
                            <p class="mt-1 text-[11px] text-gray-600 leading-relaxed">{{ $po['description'] }}</p>
                            @if(!is_null($po['score']))
                                <div class="mt-2 h-1.5 w-full rounded-full bg-gray-200">
                                    <div class="h-1.5 rounded-full {{ $po['attained'] ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min($po['score'], 100) }}%"></div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-[11px] italic text-gray-400">No POs mapped to this course's CLOs.</p>
                    @endforelse

                    <h4 class="pt-3 text-[11px] font-bold text-gray-700 uppercase tracking-wider border-t border-gray-100">Assessment Tasks</h4>
                    @forelse($course->assessmentTasks as $task)
                        <div class="rounded-lg border border-gray-200 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-semibold text-gray-900 text-xs">{{ $task->title }}</span>
                                <span class="shrink-0 rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-bold text-gray-600">{{ $task->weight_percentage }}%</span>
                            </div>
                            <div class="mt-1 text-[10px] text-gray-500">
                                {{ $task->type }} | {{ $task->total_marks }} marks | {{ $task->items->count() }} item(s)
                            </div>
                            @if($task->items->isNotEmpty())
                                <div class="mt-1.5 space-y-0.5">
                                    @foreach($task->items as $item)
                                        <div class="text-[10px] text-gray-500">
                                            <span class="text-indigo-600">{{ $item->clo?->code ?? 'CLO' }}</span>
                                            <span class="mx-1">-</span>{{ $item->item_name }}
                                            <span class="text-gray-400">({{ $item->max_marks }} marks)</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-[11px] italic text-gray-400">No assessment tasks configured.</p>
                    @endforelse
                </div>

                {{-- Course Blocks & Faculty --}}
                <div class="bg-white p-4 space-y-3">
                    <h4 class="text-[11px] font-bold text-gray-700 uppercase tracking-wider">Course Blocks & Faculty</h4>
                    @forelse($course->courseBlocks as $block)
                        <div class="rounded-lg border border-gray-200 p-3" wire:key="block-{{ $block->id }}" x-data="{ open: false }">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-gray-900 text-xs">Block #{{ $block->id }}</span>
                                <div class="flex items-center gap-1.5">
                                    @if(!is_null($block->attainment))
                                        <span class="shrink-0 rounded px-1.5 py-0.5 text-[9px] font-bold {{ $block->attainment >= $thresholdPercentage ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ number_format($block->attainment, 1) }}%</span>
                                    @endif
                                    <span class="shrink-0 rounded bg-indigo-50 px-1.5 py-0.5 text-[9px] font-bold text-indigo-700">{{ $block->student_count }} students</span>
                                </div>
                            </div>
                            <div class="mt-1.5 space-y-1 text-[10px] text-gray-500">
                                <div>
                                    <span class="font-semibold text-gray-600">Sections:</span>
                                    {{ $block->sections->pluck('name')->filter()->unique()->implode(', ') ?: '—' }}
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-600">Faculty:</span>
                                    {{ trim(($block->faculty->first_name ?? '') . ' ' . ($block->faculty->last_name ?? '')) ?: 'Unassigned' }}
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-600">Term:</span>
                                    {{ $block->academicYear ? $block->academicYear->start_year . ' - ' . $block->academicYear->end_year : '—' }}
                                    <span class="mx-1">|</span> {{ $block->semester }}
                                </div>
                                @if(!is_null($block->attainment))
                                    <div>
                                        <span class="font-semibold text-gray-600">Assessed:</span>
                                        {{ $block->assessed_students }} / {{ $block->student_count }} students
                                    </div>
                                @endif
                                @if($block->room_name || $block->schedule_string)
                                    <div>
                                        <span class="font-semibold text-gray-600">Schedule:</span>
                                        {{ trim($block->room_name . ($block->schedule_string ? ' | ' . $block->schedule_string : '')) }}
                                    </div>
                                @endif
                            </div>

                            @if($block->student_details->isNotEmpty())
                                <button type="button" x-on:click="open = !open"
                                    class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-indigo-600 hover:text-indigo-800">
                                    <span x-text="open ? '▾' : '▸'"></span>
                                    <span x-text="open ? 'Hide students' : 'View students'"></span>
                                </button>

                                <div x-show="open" x-cloak class="mt-2">
                                    <div class="overflow-x-auto max-h-56 rounded-md border border-gray-200">
                                        <table class="w-full text-[10px]">
                                            <thead class="bg-gray-100 text-gray-600 uppercase">
                                                <tr>
                                                    <th class="px-2 py-1 text-left">Student</th>
                                                    <th class="px-2 py-1 text-center">CLOs Assessed</th>
                                                    <th class="px-2 py-1 text-right">Attainment</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($block->student_details as $student)
                                                    <tr>
                                                        <td class="px-2 py-1">
                                                            <span class="font-semibold text-gray-800">{{ $student['student_name'] }}</span>
                                                            <span class="block font-mono text-[9px] text-gray-400">{{ $student['student_number'] }}</span>
                                                        </td>
                                                        <td class="px-2 py-1 text-center text-gray-500">
                                                            {{ $student['clo_count'] }} / {{ $course->clo_attainments->count() }}
                                                        </td>
                                                        <td class="px-2 py-1 text-right font-bold {{ !is_null($student['percentage']) && $student['percentage'] >= $thresholdPercentage ? 'text-emerald-600' : 'text-rose-600' }}">
                                                            {{ !is_null($student['percentage']) ? number_format($student['percentage'], 1) . '%' : 'Not assessed' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-[11px] italic text-gray-400">No course blocks for the selected filters.</p>
                    @endforelse

                    <h4 class="pt-3 text-[11px] font-bold text-gray-700 uppercase tracking-wider border-t border-gray-100">Faculty Assigned</h4>
                    @forelse($course->faculty_names as $name)
                        <div class="flex items-center gap-2 text-xs text-gray-700">
                            <i class="fas fa-chalkboard-user text-gray-400"></i>
                            {{ $name }}
                        </div>
                    @empty
                        <p class="text-[11px] italic text-gray-400">Unassigned</p>
                    @endforelse
                </div>
            </div>
        </div>
            @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center text-gray-500 space-y-3">
            <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="text-sm font-bold text-gray-700">No Courses Found</h3>
            <p class="text-xs text-gray-400 max-w-sm mx-auto">
                No courses match the selected filters{{ $isAdminView ? '' : ' for your assigned load' }}.
                Try changing the program, academic year or semester.
            </p>
        </div>
    @endforelse
    @endif
</div>
