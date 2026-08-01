<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Curriculum Mapping: Assign Courses to Programs</h1>
        <p class="text-sm text-gray-600">Link core or elective courses (e.g., CC101) to one or multiple degree programs (e.g., DIT, BSIS).</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-4 text-sm text-emerald-800 bg-emerald-100 rounded-lg border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 text-sm text-rose-800 bg-rose-100 rounded-lg border border-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <!-- Step 1: Select Program -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">1. Select Degree Program</label>
                <select wire:model.live="selectedProgramId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Choose a Program (e.g., BSIS or DIT) --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">2. Select Batch / Cohort</label>
                <select wire:model.live="selectedBatchYear" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Batches</option>
                    @foreach($batchOptions as $batchOption)
                        <option value="{{ $batchOption }}">Batch {{ $batchOption }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($selectedProgramId)
            @php
                $selectedProgram = $programs->firstWhere('id', $selectedProgramId);
            @endphp

            <div class="mt-6 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Program Objectives & Outcomes</h3>
                        <p class="mt-0.5 text-xs text-gray-500">
                            {{ $selectedProgram?->name }} — {{ $selectedBatchYear ? 'Batch ' . $selectedBatchYear . ' curriculum outcomes' : 'All batches (unversioned outcomes)' }}
                        </p>
                    </div>
                </div>

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
            </div>

            <hr class="my-6 border-gray-200">

            @if($selectedBatchYear)
                <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">Assigned Courses for Batch {{ $selectedBatchYear }}</h3>
                        <span class="text-xs font-medium text-gray-500">{{ $currentBatchCourses->count() }} course(s)</span>
                    </div>

                    @if($currentBatchCourses->isNotEmpty())
                        <div class="overflow-hidden rounded-md border border-gray-200 bg-white">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Course</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">CLOs and CLO Attainment</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Assessment Tasks / Faculty</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($currentBatchCourses as $course)
                                        <tr>
                                            <td class="px-3 py-2 align-top">
                                                <div class="font-bold text-gray-900">{{ $course->code }}</div>
                                                <div class="mt-1 text-gray-600">{{ $course->name }}</div>
                                                <div class="mt-3 border-t border-gray-100 pt-2 text-xs">
                                                    <div class="font-semibold uppercase tracking-wider text-gray-500">Course Completion Rate</div>
                                                    <div class="mt-1 font-extrabold {{ !is_null($course->completion_rate) && $course->completion_rate >= 75 ? 'text-emerald-700' : 'text-amber-700' }}">
                                                        {{ !is_null($course->completion_rate) ? number_format($course->completion_rate, 1) . '%' : 'N/A' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                @forelse($course->learningOutcomes as $clo)
                                                    <div class="mb-2 border-b border-gray-100 pb-2 last:border-0 last:pb-0" wire:key="clo-{{ $clo->id }}" x-data="{ breakdownOpen: false }">
                                                        <div class="grid grid-cols-1 gap-2 md:grid-cols-[minmax(0,1fr)_150px] md:items-start">
                                                            <div class="flex items-start justify-between gap-2 text-xs">
                                                                <div>
                                                                    <span class="font-semibold text-indigo-700">{{ $clo->code }}</span>
                                                                    <span class="text-gray-600">{{ $clo->description }}</span>
                                                                </div>
                                                                <button type="button" wire:click="editClo({{ $clo->id }})" class="shrink-0 font-semibold text-indigo-600 hover:text-indigo-800">
                                                                    Edit
                                                                </button>
                                                            </div>
                                                            <div class="rounded border border-gray-200 bg-gray-50 p-2 text-xs">
                                                                <div class="text-[10px] text-gray-600">
                                                                    Students: <strong>{{ $clo->total_students ?? 0 }}</strong>
                                                                    <span class="mx-1">|</span>
                                                                    Assessed: <strong>{{ $clo->students_assessed ?? 0 }}</strong>
                                                                </div>
                                                                <div class="mt-1 font-bold {{ !is_null($clo->completion_rate) && $clo->completion_rate >= 75 ? 'text-emerald-700' : 'text-amber-700' }}">
                                                                    Attainment: 
                                                                </div>

                                                                @php
                                                                    $attainment = $clo->completion_rate;
                                                                    $badgeStyle = $attainment >= 75 
                                                                        ? 'bg-emerald-50 border-emerald-200 text-emerald-800' 
                                                                        : ($attainment >= 50 ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-rose-50 border-rose-200 text-rose-800');
                                                                @endphp

                                                                <div class="inline-flex items-center gap-1.5 border px-2.5 py-1 rounded-md text-[11px] {{ $badgeStyle }}">
                                                                    <span class="text-gray-500 font-medium">Batch Attainment:</span>
                                                                    <span class="font-extrabold">{{ !is_null($attainment) ? number_format($attainment, 1) . '%' : 'N/A' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if(isset($clo->student_breakdown) && $clo->student_breakdown->isNotEmpty())
                                                            <button type="button" x-on:click="breakdownOpen = !breakdownOpen" class="mt-2 inline-flex items-center gap-1 rounded border border-gray-200 bg-gray-50 px-2 py-1 text-[10px] font-semibold text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200">
                                                                <span class="inline-block w-3 text-center" x-text="breakdownOpen ? '▾' : '▸'"></span>
                                                                <span x-text="breakdownOpen ? 'Hide Student Marks Breakdown' : 'View Student Marks Breakdown'"></span>
                                                                <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[9px] font-bold text-indigo-700">{{ $clo->student_breakdown->count() }}</span>
                                                            </button>

                                                            <div x-show="breakdownOpen" x-cloak x-transition class="mt-2 rounded border border-indigo-100 bg-indigo-50/30 p-2">
                                                                <div class="mb-2 text-[10px] leading-relaxed text-gray-600">
                                                                    <span class="font-bold text-gray-800">How it's computed:</span>
                                                                    Per student: percentage = (total marks obtained on this CLO's assessment items ÷ total max marks) × 100.
                                                                    CLO attainment = average of all assessed students' percentages.
                                                                </div>
                                                                <div class="overflow-x-auto max-h-64 rounded-md border border-gray-200 bg-white">
                                                                    <table class="w-full text-[10px]">
                                                                        <thead class="bg-gray-100 text-gray-700 uppercase">
                                                                            <tr>
                                                                                <th class="px-2 py-1.5 text-left">Student</th>
                                                                                @foreach($clo->assessment_items as $item)
                                                                                    <th class="px-2 py-1.5 text-center" title="{{ $item->task_title }} - {{ $item->item_name }}">{{ $item->task_title }} - {{ $item->item_name }}</th>
                                                                                @endforeach
                                                                                <th class="px-2 py-1.5 text-center">Total</th>
                                                                                <th class="px-2 py-1.5 text-right">% Score</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody class="divide-y divide-gray-100">
                                                                            @foreach($clo->student_breakdown as $score)
                                                                                <tr class="hover:bg-gray-50">
                                                                                    <td class="px-2 py-1">
                                                                                        <div class="font-bold text-gray-900">{{ $score['student_name'] }}</div>
                                                                                        <div class="font-mono text-[9px] text-gray-500">{{ $score['student_number'] }}</div>
                                                                                    </td>
                                                                                    @foreach($clo->assessment_items as $item)
                                                                                        @php
                                                                                            $itemMark = collect($score['marks'])->firstWhere('item_id', $item->id);
                                                                                        @endphp
                                                                                        <td class="px-2 py-1 text-center text-gray-600">
                                                                                            {{ $itemMark ? number_format($itemMark['marks_obtained'], 1) . '/' . number_format($itemMark['max_marks'], 1) : '—' }}
                                                                                        </td>
                                                                                    @endforeach
                                                                                    <td class="px-2 py-1 text-center font-semibold text-gray-800">
                                                                                        {{ number_format($score['total_obtained'], 1) }}/{{ number_format($score['total_max'], 1) }}
                                                                                    </td>
                                                                                    <td class="px-2 py-1 text-right font-extrabold {{ $score['percentage'] >= 75 ? 'text-emerald-600' : ($score['percentage'] >= 50 ? 'text-amber-600' : 'text-rose-600') }}">
                                                                                        {{ number_format($score['percentage'], 1) }}%
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div class="mt-1.5 text-[10px] text-gray-600">
                                                                    Attainment = (<span class="font-mono">{{ $clo->student_breakdown->pluck('percentage')->map(fn ($p) => number_format($p, 1))->implode(' + ') }}</span>) ÷ <strong>{{ $clo->student_breakdown->count() }}</strong> = <strong class="text-indigo-700">{{ number_format($clo->completion_rate, 1) }}%</strong>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="mt-1 text-[10px] italic text-gray-400">
                                                                No assessment marks recorded for this CLO.
                                                            </div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <span class="text-xs italic text-gray-400">No CLOs assigned</span>
                                                @endforelse

                                                <button type="button" wire:click="$set('cloCourseId', {{ $course->id }})" class="mt-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                                    + Assign CLO
                                                </button>
                                            </td>
                                            <td class="px-3 py-2 align-top">
                                                @php
                                                    $courseCloIds = $course->learningOutcomes->pluck('id');
                                                    $courseTasks = $course->assessmentTasks->filter(function ($task) use ($courseCloIds) {
                                                        return $task->items->contains(function ($item) use ($courseCloIds) {
                                                            return $courseCloIds->contains($item->course_learning_outcome_id);
                                                        });
                                                    });
                                                    $facultySections = $course->courseBlocks->map(function ($block) {
                                                        $faculty = trim(($block->faculty->first_name ?? '') . ' ' . ($block->faculty->last_name ?? ''));
                                                        $sections = $block->sections->pluck('name')->filter()->unique()->implode(', ');
                                                        return 'Block #' . $block->id . ' | ' . ($sections ?: 'Section') . ': ' . ($faculty ?: 'Unassigned');
                                                    })->filter()->unique()->values();
                                                @endphp

                                                @forelse($courseTasks as $task)
                                                    <div class="mb-2 rounded border border-gray-200 bg-gray-50 p-2 text-xs">
                                                        <div class="font-semibold text-gray-900">{{ $task->title }}</div>
                                                        <div class="text-[10px] text-gray-500">
                                                            {{ $task->type }} | {{ $task->weight_percentage }}% | {{ $task->total_marks }} marks
                                                        </div>
                                                    </div>
                                                @empty
                                                    <span class="text-xs italic text-gray-400">No task mapped</span>
                                                @endforelse

                                                <div class="mt-1 text-[10px] text-indigo-700">
                                                    <span class="font-semibold">Faculty by section:</span>
                                                    @if($facultySections->isNotEmpty())
                                                        <div class="mt-1 space-y-0.5">
                                                            @foreach($facultySections as $facultySection)
                                                                <div>{{ $facultySection }}</div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        Unassigned
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <p class="text-sm text-gray-500">No courses assigned for this batch yet.</p>

                            @if($previousBatchWithCourses)
                                <button
                                    type="button"
                                    wire:click="carryForwardCoursesFromPreviousBatch"
                                    wire:confirm="Carry forward courses and their CLOs from Batch {{ $previousBatchWithCourses }} to Batch {{ $selectedBatchYear }}?"
                                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2"/>
                                    </svg>
                                    Carry Forward Courses & CLOs from Batch {{ $previousBatchWithCourses }}
                                </button>
                                <p class="mt-2 text-[11px] text-gray-400">Courses and CLOs are carried forward from the last batch that has assignments. CLO-to-PO mappings are only re-linked to matching POs of this batch.</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            @php
                $batchClos = $currentBatchCourses
                    ->flatMap(fn ($course) => $course->learningOutcomes)
                    ->unique('id')
                    ->values();
            @endphp

            @if($batchClos->isNotEmpty() && $programOutcomes->isNotEmpty())
                <div class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-800">CLO to PO Mapping</h3>
                        <p class="mt-0.5 text-xs text-gray-500">Batch {{ $selectedBatchYear }} curriculum mapping</p>
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
                                        <tr wire:key="compact-clo-po-row-{{ $clo->id }}" class="hover:bg-gray-50">
                                            @if($loop->first)
                                                <td rowspan="{{ $course->learningOutcomes->count() }}" class="border-r border-gray-200 px-3 py-3 align-top">
                                                    <div class="font-bold text-gray-900">{{ $course->code }}</div>
                                                    <div class="mt-1 text-gray-600">{{ $course->name }}</div>
                                                </td>
                                            @endif
                                            <td class="border-r border-gray-200 px-3 py-3 align-top">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <div class="font-bold text-indigo-600">{{ $clo->code }}</div>
                                                    <div class="mt-1 text-gray-600">{{ $clo->description }}</div>
                                                    @if($clo->bloomsTaxonomy)
                                                        <span class="mt-2 inline-block rounded bg-gray-100 px-2 py-1 text-[10px] font-semibold text-gray-600">{{ $clo->bloomsTaxonomy->code }}: {{ $clo->bloomsTaxonomy->level }}</span>
                                                    @endif
                                                </div>
                                                <button type="button" wire:click="editClo({{ $clo->id }})" class="shrink-0 text-[10px] font-semibold text-indigo-600 hover:text-indigo-800">Edit</button>
                                            </div>
                                        </td>
                                        @foreach($programOutcomes as $po)
                                            @php
                                                $mappedPo = $clo->programOutcomes->firstWhere('id', $po->id);
                                                $currentLevel = $mappedPo?->pivot?->level ?? '';
                                                $levelStyle = match ($currentLevel) {
                                                    'I' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    'G' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                    'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                    default => 'bg-white text-gray-400 border-gray-200',
                                                };
                                                $levelLabel = match ($currentLevel) {
                                                    'I' => 'I',
                                                    'G' => 'E',
                                                    'A' => 'D',
                                                    default => '-',
                                                };
                                            @endphp
                                            <td class="border-r border-gray-200 px-2 py-3 text-center align-middle">
                                                <select wire:change="updateCloPoMapping({{ $clo->id }}, {{ $po->id }}, $event.target.value)" class="w-full rounded-md border px-2 py-2 text-[10px] font-bold {{ $levelStyle }}">
                                                    <option value="" {{ $currentLevel === '' ? 'selected' : '' }}>-</option>
                                                    <option value="I" {{ $currentLevel === 'I' ? 'selected' : '' }}>I</option>
                                                    <option value="G" {{ $currentLevel === 'G' ? 'selected' : '' }}>E</option>
                                                    <option value="A" {{ $currentLevel === 'A' ? 'selected' : '' }}>D</option>
                                                </select>
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
                <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">No effective POs are configured for Batch {{ $selectedBatchYear }} yet.</div>
            @endif

            @if($cloCourseId)
                <div class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50/40 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-indigo-900">
                            {{ $editingCloId ? 'Edit CLO' : 'Assign CLO to Course' }}
                        </h3>
                        <button type="button" wire:click="resetCloForm" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Cancel</button>
                    </div>

                    <form wire:submit.prevent="saveClo" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">CLO Code</label>
                            <input type="text" wire:model="cloCode" placeholder="CLO-01" class="mt-1 w-full rounded-md border-gray-300 text-xs shadow-sm">
                            @error('cloCode') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700">Description</label>
                            <input type="text" wire:model="cloDescription" placeholder="Describe the learning outcome" class="mt-1 w-full rounded-md border-gray-300 text-xs shadow-sm">
                            @error('cloDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Bloom Taxonomy</label>
                            <select wire:model="cloTaxonomyId" class="mt-1 w-full rounded-md border-gray-300 text-xs shadow-sm">
                                <option value="">-- Select --</option>
                                @foreach($taxonomies as $taxonomy)
                                    <option value="{{ $taxonomy->id }}">{{ $taxonomy->code }} - {{ $taxonomy->level }}</option>
                                @endforeach
                            </select>
                            @error('cloTaxonomyId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-4 text-xs text-gray-600">
                            Course: <strong>{{ optional($currentBatchCourses->firstWhere('id', $cloCourseId))->code }}</strong>
                            @if($selectedBatchYear)
                                | Batch: <strong>{{ $selectedBatchYear }}</strong>
                            @endif
                        </div>
                        <button type="submit" class="md:col-span-4 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                            {{ $editingCloId ? 'Update CLO' : 'Save CLO' }}
                        </button>
                    </form>
                </div>
            @endif

            <!-- Step 2: Select Courses -->
            <form wire:submit.prevent="saveAssignments">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-md font-semibold text-gray-800">3. Select Attached Courses</h3>
                        <p class="text-xs text-gray-500">Check all courses that belong to this program's curriculum.</p>
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white font-medium text-sm rounded-lg hover:bg-indigo-700 transition shadow-sm">
                        Save Program Curriculum
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($courses as $course)
                        <label class="relative flex items-start p-4 rounded-lg border cursor-pointer hover:border-indigo-300 transition {{ in_array($course->id, $selectedCourseIds) ? 'bg-indigo-50/50 border-indigo-500' : 'bg-gray-50 border-gray-200' }}">
                            <div class="flex items-center h-5">
                                <input 
                                    type="checkbox" 
                                    value="{{ $course->id }}" 
                                    wire:model="selectedCourseIds" 
                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-bold text-gray-900">{{ $course->code }}</span>
                                <p class="text-gray-600">{{ $course->name }}</p>
                            </div>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500 col-span-3">No courses found in the system. Create courses first!</p>
                    @endforelse
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white font-medium text-sm rounded-lg hover:bg-indigo-700 transition shadow-sm">
                        Save Program Curriculum
                    </button>
                </div>
            </form>
        @else
            <div class="p-8 text-center text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                Please select a program above to view and assign courses.
            </div>
        @endif
    </div>
</div>