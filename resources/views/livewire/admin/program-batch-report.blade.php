<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">OBE Program Report</h1>
            <p class="text-sm text-gray-600">
                Read-only OBE report per program and batch — PEOs, POs, assigned courses with CLO / PO attainment, assessment tasks and faculty.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <label class="text-xs font-semibold text-gray-700">Target Threshold (%):</label>
            <input type="number" wire:model.live="thresholdPercentage" class="w-16 rounded-md border-gray-300 text-xs text-center shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Degree Program</label>
            <select wire:model.live="selectedProgramId" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- Choose a Program --</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}">{{ $program->code }} - {{ $program->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Batch / Cohort</label>
            <select wire:model.live="selectedBatchYear" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Batches (unversioned)</option>
                @foreach($batchOptions as $batchOption)
                    <option value="{{ $batchOption }}">Batch {{ $batchOption }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if(!$selectedProgramId)
        <div class="bg-white border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center text-gray-500 space-y-3">
            <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-6m0 0V6a2 2 0 114 0v5m-4 0h6a2 2 0 012 2v4m-8-6H6a2 2 0 00-2 2v4a2 2 0 002 2h4m6 0h2a2 2 0 002-2v-4a2 2 0 00-2-2h-4"/>
            </svg>
            <h3 class="text-sm font-bold text-gray-700">Select a Program</h3>
            <p class="text-xs text-gray-400 max-w-sm mx-auto">Choose a degree program above to view its OBE report per batch.</p>
        </div>
    @else
        {{-- PEOs & POs --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex flex-col">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 mb-3">
                    <h2 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        Program Educational Objectives (PEOs)
                    </h2>
                    <span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700">{{ $peos->count() }}</span>
                </div>
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @forelse($peos as $peo)
                        <div class="p-2.5 bg-gray-50/80 rounded-xl border border-gray-100">
                            <span class="font-bold text-indigo-600 text-xs">{{ $peo->code }}</span>
                            <p class="text-xs text-gray-700 mt-0.5 leading-relaxed">{{ $peo->description }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">No PEOs configured for this program{{ $selectedBatchYear ? ' and batch' : '' }}.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex flex-col">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 mb-3">
                    <h2 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        Program Outcomes (POs)
                    </h2>
                    <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">{{ $programOutcomes->count() }}</span>
                </div>
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @forelse($programOutcomes as $po)
                        <div class="p-2.5 bg-gray-50/80 rounded-xl border border-gray-100 flex items-start gap-2.5">
                            <span class="font-bold text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md shrink-0">{{ $po->code }}</span>
                            <p class="text-xs text-gray-700 leading-relaxed">{{ $po->description }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">No POs configured for this program{{ $selectedBatchYear ? ' and batch' : '' }}.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Assigned Courses --}}
        <div>
            <div class="flex items-center gap-3 mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $selectedBatchYear ? 'Assigned Courses — Batch ' . $selectedBatchYear : 'Assigned Courses — All Batches' }}
                </h2>
                <span class="rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-0.5">{{ $currentBatchCourses->count() }} course(s)</span>
                <span class="flex-1 h-px bg-gray-200"></span>
            </div>

            @if($currentBatchCourses->isNotEmpty())
                <div class="space-y-6">
                @foreach($currentBatchCourses as $course)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" wire:key="report-course-{{ $course->id }}">
                        {{-- Course header --}}
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-5 py-4 bg-gray-50 border-b border-gray-200">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-indigo-700 font-extrabold text-lg">{{ $course->code }}</span>
                                    <span class="text-gray-900 font-semibold text-sm">{{ $course->name }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-500">
                                    <span><strong class="text-gray-700">{{ $course->courseBlocks->sum(fn ($b) => $b->students->count()) }}</strong> students</span>
                                    <span><strong class="text-gray-700">{{ $course->courseBlocks->count() }}</strong> block(s)</span>
                                    <span><strong class="text-gray-700">{{ $course->learningOutcomes->count() }}</strong> CLO(s)</span>
                                    <span><strong class="text-gray-700">{{ $course->assessmentTasks->count() }}</strong> task(s)</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[10px] uppercase font-bold text-gray-500 block">Course Attainment</span>
                                @if(!is_null($course->completion_rate))
                                    <span class="inline-block font-extrabold text-lg {{ $course->completion_rate >= $thresholdPercentage ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ number_format($course->completion_rate, 1) }}%
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
                                @forelse($course->learningOutcomes as $clo)
                                    <div class="rounded-lg border border-gray-200 p-3" wire:key="clo-{{ $clo->id }}" x-data="{ open: false }">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="font-bold text-indigo-700 text-xs">{{ $clo->code }}</span>
                                                @if($clo->bloomsTaxonomy)
                                                    <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-semibold text-gray-500">{{ $clo->bloomsTaxonomy->code }}</span>
                                                @endif
                                                <div class="mt-1 text-[11px] text-gray-600 leading-relaxed">{{ $clo->description }}</div>
                                            </div>
                                            <span class="shrink-0 font-extrabold text-xs {{ !is_null($clo->completion_rate) && $clo->completion_rate >= $thresholdPercentage ? 'text-emerald-700' : 'text-amber-700' }}">
                                                {{ !is_null($clo->completion_rate) ? number_format($clo->completion_rate, 1) . '%' : 'N/A' }}
                                            </span>
                                        </div>

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
                                            </div>
                                        @else
                                            <div class="mt-2 text-[10px] italic text-gray-400">No assessment marks recorded for this CLO.</div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-[11px] italic text-gray-400">No CLOs configured for this course.</p>
                                @endforelse
                            </div>

                            {{-- PO mapping & Assessment Tasks --}}
                            <div class="bg-white p-4 space-y-3">
                                <h4 class="text-[11px] font-bold text-gray-700 uppercase tracking-wider">CLO → PO Mapping</h4>
                                @forelse($course->learningOutcomes as $clo)
                                    <div class="rounded-lg border border-gray-200 p-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-bold text-indigo-700 text-xs">{{ $clo->code }}</span>
                                        </div>
                                        @if($clo->programOutcomes->isNotEmpty())
                                            <div class="mt-1.5 flex flex-wrap gap-1">
                                                @foreach($clo->programOutcomes as $po)
                                                    @php
                                                        $level = $po->pivot?->level ?? '';
                                                        $levelStyle = match ($level) {
                                                            'I' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                            'G' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                            'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                            default => 'bg-gray-100 text-gray-500 border-gray-200',
                                                        };
                                                        $levelLabel = match ($level) {
                                                            'I' => 'I',
                                                            'G' => 'E',
                                                            'A' => 'D',
                                                            default => '-',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1 rounded border px-1.5 py-0.5 text-[9px] font-bold {{ $levelStyle }}" title="{{ $po->description }}">
                                                        {{ $po->code }} ({{ $levelLabel }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="mt-1 text-[10px] italic text-gray-400">Not mapped to any PO.</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-[11px] italic text-gray-400">No CLOs configured for this course.</p>
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
                                    <div class="rounded-lg border border-gray-200 p-3" wire:key="report-block-{{ $block->id }}">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-bold text-gray-900 text-xs">Block #{{ $block->id }}</span>
                                            <span class="shrink-0 rounded bg-indigo-50 px-1.5 py-0.5 text-[9px] font-bold text-indigo-700">{{ $block->students->count() }} students</span>
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
                                            @if($block->room_name || $block->schedule_string)
                                                <div>
                                                    <span class="font-semibold text-gray-600">Schedule:</span>
                                                    {{ trim($block->room_name . ($block->schedule_string ? ' | ' . $block->schedule_string : '')) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-[11px] italic text-gray-400">No course blocks for the selected batch.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            @else
                <div class="bg-white border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center text-gray-500 space-y-3">
                    <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-700">No Courses Assigned</h3>
                    <p class="text-xs text-gray-400 max-w-sm mx-auto">
                        No courses are assigned to {{ $selectedProgram?->name }} for {{ $selectedBatchYear ? 'batch ' . $selectedBatchYear : 'the unversioned curriculum' }}.
                    </p>
                </div>
            @endif
        </div>

        {{-- CLO to PO Mapping Matrix --}}
        @if($batchClos->isNotEmpty() && $programOutcomes->isNotEmpty())
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-800">CLO to PO Mapping</h3>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $selectedProgram?->name }} — {{ $selectedBatchYear ? 'Batch ' . $selectedBatchYear : 'All batches (unversioned)' }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-48 border-r border-gray-200 px-3 py-3 text-left font-bold uppercase tracking-wider text-gray-500">Course</th>
                                <th class="w-64 border-r border-gray-200 px-3 py-3 text-left font-bold uppercase tracking-wider text-gray-500">Course Learning Outcomes (CLOs)</th>
                                @foreach($programOutcomes as $po)
                                    <th class="min-w-[100px] border-r border-gray-200 px-2 py-3 text-center font-bold uppercase tracking-wider text-gray-700" title="{{ $po->description }}">{{ $po->code }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($currentBatchCourses as $course)
                                @foreach($course->learningOutcomes as $clo)
                                    <tr class="hover:bg-gray-50">
                                        @if($loop->first)
                                            <td rowspan="{{ $course->learningOutcomes->count() }}" class="border-r border-gray-200 px-3 py-3 align-top">
                                                <div class="font-bold text-gray-900">{{ $course->code }}</div>
                                                <div class="mt-1 text-gray-600">{{ $course->name }}</div>
                                            </td>
                                        @endif
                                        <td class="border-r border-gray-200 px-3 py-3 align-top">
                                            <div class="font-bold text-indigo-600">{{ $clo->code }}</div>
                                            <div class="mt-1 text-gray-600">{{ $clo->description }}</div>
                                            @if($clo->bloomsTaxonomy)
                                                <span class="mt-2 inline-block rounded bg-gray-100 px-2 py-1 text-[10px] font-semibold text-gray-600">{{ $clo->bloomsTaxonomy->code }}: {{ $clo->bloomsTaxonomy->level }}</span>
                                            @endif
                                        </td>
                                        @foreach($programOutcomes as $po)
                                            @php
                                                $mappedPo = $clo->programOutcomes->firstWhere('id', $po->id);
                                                $currentLevel = $mappedPo?->pivot?->level ?? '';
                                                $levelStyle = match ($currentLevel) {
                                                    'I' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    'G' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                    'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                    default => 'bg-gray-50 text-gray-300 border-gray-100',
                                                };
                                                $levelLabel = match ($currentLevel) {
                                                    'I' => 'I',
                                                    'G' => 'E',
                                                    'A' => 'D',
                                                    default => '-',
                                                };
                                            @endphp
                                            <td class="border-r border-gray-200 px-2 py-3 text-center align-middle">
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded border text-[10px] font-bold {{ $levelStyle }}">{{ $levelLabel }}</span>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($selectedBatchYear && $batchClos->isNotEmpty())
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">No effective POs are configured for Batch {{ $selectedBatchYear }} yet.</div>
        @endif
    @endif
</div>
