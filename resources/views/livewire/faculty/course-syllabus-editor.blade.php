<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8" x-data="syllabusTour()" x-cloak>
    {{-- Guided tour trigger --}}
    <button type="button" x-show="!active" @click="start()"
            class="fixed bottom-6 right-6 z-50 inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg hover:bg-indigo-700 transition">
        <i class="fas fa-shoe-prints"></i> Take the guided tour
    </button>

    {{-- Tour overlay dims the page and punches a hole around the current section --}}
    <template x-if="active">
        <div class="fixed inset-0 z-40 bg-gray-900/60" :style="{ clipPath: overlayClip }" @click="next()"></div>
    </template>

    {{-- Step tooltip --}}
    <template x-if="active && rect">
        <div class="fixed z-50 w-[320px] max-w-[calc(100vw-24px)]" :style="tipStyle">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-2xl">
                <div class="flex items-start justify-between gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-indigo-700">
                        <i class="fas fa-shoe-prints"></i> Step <span x-text="step + 1"></span> of <span x-text="steps.length"></span>
                    </span>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="stop()"><i class="fas fa-xmark"></i></button>
                </div>
                <p class="mt-2 text-sm font-bold text-gray-900" x-text="current.title"></p>
                <p class="mt-1 text-xs leading-relaxed text-gray-600" x-text="current.desc"></p>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <button type="button" x-show="step > 0" @click="back()"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Back</button>
                    <button type="button" @click="next()"
                            class="ml-auto inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                        <span x-text="step < steps.length - 1 ? 'Next' : 'Finish'"></span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>
    @php
        $block = $data?->block();
        $course = $block?->course;
        $program = $data?->program();
        $peos = $data?->peos() ?? collect();
        $pos = $data?->programOutcomes() ?? collect();
        $clos = $data?->courseLearningOutcomes() ?? collect();
        $tasks = $data?->assessmentTasks() ?? collect();
        $batch = $data?->batchYear();
        $locked = !is_null($submittedAt);
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Course Syllabus</h1>
            <p class="text-sm text-gray-600">Prepare the syllabus for your assigned course block.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('guides.teacher.assessment-tasks') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-sm font-semibold hover:bg-amber-100">
                <i class="fas fa-list-check"></i>Assessment Tasks Guide
            </a>
            <a href="{{ route('faculty.syllabus.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200">
                <i class="fas fa-arrow-left"></i>Back to Courses
            </a>
            @if($data)
                <a href="{{ route('faculty.syllabus.print', [$block->id, $data->program()->id]) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-bold hover:bg-gray-800">
                    <i class="fas fa-print"></i>Print Syllabus
                </a>
            @endif
        </div>
    </div>

    @if($programs->count() > 1)
        <div class="mb-4 bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mr-1">Program Syllabus:</span>
            @foreach($programs as $p)
                <a href="{{ route('faculty.syllabus.edit', [$courseBlockId, $p->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold {{ $p->id == $programId ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-graduation-cap"></i>{{ $p->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if($locked)
        <div class="mb-4 bg-amber-50 border border-amber-300 text-amber-900 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-lock mr-2"></i>
            <strong>Submitted and locked.</strong> This syllabus is final and can no longer be edited. The schedule, learning plan contents, and assessment tasks are fixed and must be followed as stated.
        </div>
    @endif

    @if($data)
        @php
            $taskWeightTotal = (float) $tasks->sum('weight_percentage');
            $gradingSummary = $tasks
                ->sortBy('id')
                ->groupBy(fn ($t) => trim((string) $t->type) !== '' ? (string) $t->type : 'Others')
                ->map(fn ($group, $type) => ['type' => $type, 'percentage' => (float) $group->sum('weight_percentage'), 'count' => $group->count()])
                ->values();
            $cloRuleViolations = $ruleViolations->filter(fn ($v) => ! str_contains($v, 'weight') && ! str_contains($v, 'marks') && ! str_starts_with($v, 'Assessment') && ! str_contains($v, 'must map to a CLO'));
            $taskCloRuleViolations = $ruleViolations->filter(fn ($v) => str_contains($v, 'must map to a CLO'));
            $steps = [
                'copo' => [
                    'title' => 'Review Course Outcomes & CO-PO Mapping',
                    'hint' => 'Confirm the course outcomes and their mapping to program outcomes are configured.',
                    'done' => $clos->isNotEmpty() && $cloRuleViolations->isEmpty(),
                    'anchor' => '#section-copo',
                ],
                'tasks' => [
                    'title' => 'Set up Assessment Tasks',
                    'hint' => 'Create assessment tasks and map each item to a CLO (you can open the Assessment Tasks Guide for full steps).',
                    'done' => $tasks->contains(fn ($t) => $t->items->isNotEmpty()),
                    'anchor' => '#section-tasks',
                ],
                'grading' => [
                    'title' => 'Review the Grading Summary',
                    'hint' => 'Auto-computed from your assessment tasks — just confirm it looks right.',
                    'done' => $tasks->isNotEmpty(),
                    'anchor' => '#section-grading',
                ],
                'textbooks' => [
                    'title' => 'Add Textbooks and References',
                    'hint' => 'List the books and reference materials for the course.',
                    'done' => trim((string) $textbooks_references) !== '',
                    'anchor' => '#section-textbooks',
                ],
                'requirements' => [
                    'title' => 'Add Course Requirements',
                    'hint' => 'Specify the major requirements students must complete.',
                    'done' => trim((string) $course_requirements) !== '',
                    'anchor' => '#section-requirements',
                ],
                'policies' => [
                    'title' => 'Set Classroom Policies & Procedures',
                    'hint' => 'Attendance, late submissions, academic integrity, and similar policies.',
                    'done' => trim((string) $classroom_policies) !== '',
                    'anchor' => '#section-policies',
                ],
                'learningPlan' => [
                    'title' => 'Complete the 18-week Learning Plan',
                    'hint' => 'Fill every teaching week (examination weeks are pre-filled).',
                    'done' => collect($items)->every(function ($item, $index) use ($examWeeks) {
                        if (isset($examWeeks[$index + 1])) { return true; }
                        return trim((string) ($item['learning_outcomes'] ?? '')) !== ''
                            && trim((string) ($item['topics_readings'] ?? '')) !== ''
                            && trim((string) ($item['learning_activities'] ?? '')) !== ''
                            && trim((string) ($item['assessment_tools'] ?? '')) !== '';
                    }),
                    'anchor' => '#section-learning-plan',
                ],
                'submit' => [
                    'title' => 'Save Draft & Submit',
                    'hint' => 'Save your progress anytime; submit only when every step is complete.',
                    'done' => (bool) $locked,
                    'anchor' => '#section-submit',
                ],
            ];
            $doneCount = collect($steps)->where('done', true)->count();
        @endphp

        <div id="section-checklist" class="mb-6 rounded-xl border-2 border-indigo-100 bg-indigo-50/50 p-5 scroll-mt-16">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-full bg-indigo-600 text-white text-sm font-black shrink-0"><i class="fas fa-shoe-prints"></i></span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Syllabus checklist — do these in order</h2>
                        <p class="text-xs text-gray-600">Work through the steps below from top to bottom. Completed items are marked automatically; click a step to jump to that section.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-indigo-200 px-3 py-1 text-xs font-bold text-indigo-700">
                    <i class="fas fa-circle-check"></i>{{ $doneCount }}/{{ count($steps) }} complete
                </span>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-2">
                @foreach($steps as $step)
                    <a href="{{ $step['anchor'] }}"
                       class="group flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 hover:border-indigo-300 hover:shadow-sm transition {{ $step['done'] ? 'opacity-90' : '' }}">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full mt-0.5 shrink-0 text-xs font-black {{ $step['done'] ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-600 text-white' }}">
                            {!! $step['done'] ? '<i class="fas fa-check"></i>' : $loop->iteration !!}
                        </span>
                        <span>
                            <span class="block text-xs font-bold {{ $step['done'] ? 'text-gray-500 line-through' : 'text-gray-800' }}">{{ $loop->iteration }}. {{ $step['title'] }}</span>
                            <span class="mt-0.5 block text-[11px] leading-snug text-gray-500">{{ $step['hint'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-full bg-gray-800 text-white text-sm font-black shrink-0"><i class="fas fa-list-check"></i></span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Mandatory syllabus rules</h2>
                        <p class="text-xs text-gray-600">These rules are checked before you can submit. Fix any that are marked below as not satisfied.</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex items-start gap-3 rounded-lg border p-3 {{ $tasks->isNotEmpty() && abs((float) $tasks->sum('weight_percentage') - 100) < 0.001 ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }}">
                    <i class="fas {{ $tasks->isNotEmpty() && abs((float) $tasks->sum('weight_percentage') - 100) < 0.001 ? 'fa-circle-check' : 'fa-circle-xmark' }} mt-0.5 {{ $tasks->isNotEmpty() && abs((float) $tasks->sum('weight_percentage') - 100) < 0.001 ? 'text-emerald-600' : 'text-rose-600' }}"></i>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-800">Assessment task weights must total 100%</p>
                        <p class="text-xs text-gray-600">The sum of all assessment task <strong>weight percentages</strong> for this course &amp; batch must equal exactly 100%.</p>
                        @php
                            $taskTotal = (float) $tasks->sum('weight_percentage');
                        @endphp
                        @if($tasks->isNotEmpty())
                            <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-bold {{ abs($taskTotal - 100) < 0.001 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                Current total: {{ number_format($taskTotal, 2) }}%
                            </span>
                        @else
                            <span class="mt-1 inline-block rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700">No assessment tasks yet</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border p-3 {{ $cloRuleViolations->isEmpty() ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }}">
                    <i class="fas {{ $cloRuleViolations->isEmpty() ? 'fa-circle-check' : 'fa-circle-xmark' }} mt-0.5 {{ $cloRuleViolations->isEmpty() ? 'text-emerald-600' : 'text-rose-600' }}"></i>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-800">Every CLO must be mapped to a Program Outcome and to an assessment item</p>
                        <p class="text-xs text-gray-600">Each active course learning outcome must appear in the CO-PO matrix <strong>and</strong> be covered by at least one assessment item, or you cannot submit.</p>
                        @if($cloRuleViolations->isEmpty())
                            <span class="mt-1 inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">All mapping rules satisfied</span>
                        @else
                            <ul class="mt-1 space-y-0.5">
                                @foreach($cloRuleViolations as $violation)
                                    <li class="text-[11px] text-rose-700"><i class="fas fa-circle-xmark mr-1"></i>{{ $violation }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border p-3 {{ $taskCloRuleViolations->isEmpty() ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }}">
                    <i class="fas {{ $taskCloRuleViolations->isEmpty() ? 'fa-circle-check' : 'fa-circle-xmark' }} mt-0.5 {{ $taskCloRuleViolations->isEmpty() ? 'text-emerald-600' : 'text-rose-600' }}"></i>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-800">Every assessment task must map to a CLO</p>
                        <p class="text-xs text-gray-600">Each assessment task needs at least one item mapped to a CLO, so every assessment is relevant to a course learning outcome. A task with no items produces a "has no mapped assessment item" message and blocks submission.</p>
                        @if($taskCloRuleViolations->isEmpty())
                            <span class="mt-1 inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">All assessment tasks are mapped to a CLO</span>
                        @else
                            <ul class="mt-1 space-y-0.5">
                                @foreach($taskCloRuleViolations as $violation)
                                    <li class="text-[11px] text-rose-700"><i class="fas fa-circle-xmark mr-1"></i>{{ $violation }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            <p class="mt-3 text-[11px] text-gray-500">
                <i class="fas fa-lightbulb mr-1"></i>
                The <strong>Grading System</strong> section is generated automatically from your assessment tasks — there is nothing separate to fill in.
                Open the <a href="{{ route('guides.teacher.assessment-tasks') }}" target="_blank" class="font-bold text-indigo-600 hover:underline">Assessment Tasks Guide</a> for the full walkthrough.
            </p>
        </div>

        @if($revisionRequestedAt && !$locked)
            <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <div class="flex items-start gap-3">
                    <i class="fas fa-triangle-exclamation mt-0.5 text-amber-600"></i>
                    <div>
                        <p class="font-bold">Returned for revision by {{ $revisionRequestedBy }} ({{ $revisionRequestedAt->format('M d, Y h:i A') }}).</p>
                        <p class="mt-1"><strong>Remarks:</strong> {{ $revisionRemarks }}</p>
                        <p class="mt-1 text-xs text-amber-700">Edit the syllabus as requested, then submit it again for re-review.</p>
                    </div>
                </div>
            </div>
        @endif
        <form wire:submit.prevent="save">
            <div class="space-y-6">

                {{-- Course descriptive data --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Course Information</h3>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Course Code</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $course->code }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Course Name</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $course->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Credit Units</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $course->units ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Pre-requisite</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $course->prerequisite_label ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Program</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $program->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Sections</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $data->sectionLabels() ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Schedule</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $block->schedule_string ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">School Year</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ optional($block->academicYear)->label }} — {{ $block->semester }}</p>
                        </div>
                        @if($course->description)
                            <div class="md:col-span-2 lg:col-span-4">
                                <p class="text-xs font-semibold text-gray-500 uppercase">Course Description</p>
                                <p class="mt-1 text-sm text-gray-700">{{ $course->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- PEO --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Program Educational Objectives (PEO)</h3>
                    </div>
                    <div class="p-5">
                        @forelse($peos as $i => $peo)
                            <div class="flex gap-3 py-1.5 {{ $i > 0 ? 'border-t border-gray-100' : '' }}">
                                <span class="inline-flex h-fit shrink-0 items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700">{{ $peo->code }}</span>
                                <span class="text-sm text-gray-700">{{ $peo->description }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 italic">No PEOs configured for this program.</p>
                        @endforelse
                    </div>
                </div>

                {{-- PO --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Program Outcomes (PO)</h3>
                    </div>
                    <div class="p-5">
                        @forelse($pos as $i => $po)
                            <div class="flex gap-3 py-1.5 {{ $i > 0 ? 'border-t border-gray-100' : '' }}">
                                <span class="inline-flex h-fit shrink-0 items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">{{ $po->code }}</span>
                                <span class="text-sm text-gray-700">{{ $po->description }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 italic">No Program Outcomes configured for this program.</p>
                        @endforelse
                    </div>
                </div>

                {{-- COs + CO-PO Mapping + Assessment Tasks --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden scroll-mt-16" id="section-copo">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Course Outcomes &amp; CO-PO Mapping with Assessment Tasks</h3>
                        <p class="mt-0.5 text-xs text-gray-500">I — Introduced, E — Enabling, D — Demonstrating</p>
                    </div>
                    @if($ruleViolations->isNotEmpty())
                        <div class="mx-5 mt-5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3">
                            <p class="text-sm font-bold text-amber-800"><i class="fas fa-triangle-exclamation mr-2"></i>Fix the following before saving:</p>
                            <ul class="mt-2 space-y-1 text-sm text-amber-800">
                                @foreach($ruleViolations as $violation)
                                    <li class="flex gap-2"><i class="fas fa-circle-xmark mt-0.5 text-amber-600"></i>{{ $violation }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="mx-5 mt-5 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3">
                            <p class="text-sm font-semibold text-emerald-800"><i class="fas fa-circle-check mr-2"></i>All CLO/PO/assessment mapping rules are satisfied.</p>
                        </div>
                    @endif
                    <div class="overflow-x-auto p-5">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Course Outcomes</th>
                                    @foreach($pos as $po)
                                        <th class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500" title="{{ $po->description }}">{{ $po->code }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Assessment Task</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($clos as $clo)
                                    <tr>
                                        <td class="px-4 py-3 align-top">
                                            <div class="flex items-start gap-3">
                                                <span class="w-20 shrink-0 text-xs font-bold text-indigo-600">{{ $clo->code }}</span>
                                                <div>
                                                    <p class="text-sm text-gray-700">{{ $clo->description }}</p>
                                                    @if($clo->bloomsTaxonomy)
                                                        <span class="mt-1 inline-block rounded bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">
                                                            {{ $clo->bloomsTaxonomy->code }}: {{ $clo->bloomsTaxonomy->level }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        @foreach($pos as $po)
                                            @php
                                                $level = $data->coPoLevel($clo, $po);
                                                $style = match ($level) {
                                                    'I' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    'G' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                    'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                    default => 'text-gray-400',
                                                };
                                                $label = match ($level) {
                                                    'I' => 'I', 'G' => 'E', 'A' => 'D', default => '—',
                                                };
                                            @endphp
                                            <td class="px-3 py-3 text-center align-top">
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded border text-xs font-bold {{ $style }}" title="{{ $po->code }}: {{ $po->description }}">{{ $label }}</span>
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 align-top">
                                            @php
                                                $cloTasks = $data->tasksForClo($clo);
                                            @endphp
                                            @forelse($cloTasks as $task)
                                                <div class="mb-1.5 last:mb-0">
                                                    <span class="inline-flex items-center gap-1.5 rounded border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-semibold text-gray-700">
                                                        <i class="fas fa-clipboard-check text-indigo-500"></i>{{ $task->title }}
                                                        <span class="text-[10px] font-normal text-gray-400">({{ $task->weight_percentage }}%)</span>
                                                    </span>
                                                </div>
                                            @empty
                                                <span class="text-xs text-gray-400 italic">No assessment mapped</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 2 + $pos->count() }}" class="px-4 py-8 text-center text-sm text-gray-400 italic">
                                            No Course Outcomes configured for this course yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

                {{-- Teacher-entered fields --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden scroll-mt-16" id="section-tasks">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Assessment Tasks Setup</h3>
                        <p class="mt-0.5 text-xs text-gray-500">Create assessment tasks and map each item to a course outcome so the CO-PO mapping above is populated.</p>
                    </div>
                    <div class="p-5">
                        @if($tasks->isEmpty())
                            <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                <i class="fas fa-triangle-exclamation mr-2"></i>
                                <strong>Assessment tasks required.</strong> No assessment tasks exist for this course yet — set them up below.
                            </div>
                        @endif
                        <livewire:faculty.assessment-task-setup
                            :courseBlockId="$block->id"
                            :locked="$locked"
                            wire:key="assessment-tasks-{{ $block->id }}-{{ $programId }}" />
                    </div>
                </div>

                <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden scroll-mt-16" id="section-grading">
                        <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 flex items-center justify-between gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Grading System</h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700 rounded-full px-2 py-1">Auto-computed</span>
                        </div>
                        <div class="p-5 space-y-3">
                            <p class="text-xs text-gray-500">This is your grade recipe, derived automatically from the assessment tasks above — each task's weight grouped by type. Keep task weights totaling <strong>100%</strong> and this table follows.</p>
                            @if($gradingSummary->isEmpty())
                                <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    <i class="fas fa-triangle-exclamation mr-2"></i>
                                    No assessment tasks yet — set them up in the Assessment Tasks section and this summary will fill in automatically.
                                </div>
                            @else
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            <th class="py-2">Assessment Type</th>
                                            <th class="py-2 text-center">Tasks</th>
                                            <th class="py-2 text-right">Weight</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($gradingSummary as $group)
                                            <tr>
                                                <td class="py-2 font-semibold text-gray-800">{{ $group['type'] }}</td>
                                                <td class="py-2 text-center text-gray-500">{{ $group['count'] }}</td>
                                                <td class="py-2 text-right font-bold text-gray-800">{{ number_format($group['percentage'], 2) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t-2 border-gray-200">
                                            <td class="py-2 text-xs font-bold uppercase tracking-wider text-gray-500" colspan="2">Total</td>
                                            <td class="py-2 text-right font-black {{ abs($taskWeightTotal - 100) < 0.001 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($taskWeightTotal, 2) }}%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden scroll-mt-16" id="section-textbooks">
                            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Textbooks and References</h3>
                            </div>
                            <div class="p-5">
                                <textarea wire:model="textbooks_references" rows="4" placeholder="List textbooks and other references used in the course..." {{ $locked ? 'readonly disabled' : '' }}
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}"></textarea>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden scroll-mt-16" id="section-requirements">
                            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Course Requirements</h3>
                            </div>
                            <div class="p-5">
                                <textarea wire:model="course_requirements" rows="4" placeholder="Required outputs, projects, and submissions for the course..." {{ $locked ? 'readonly disabled' : '' }}
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden scroll-mt-16" id="section-policies">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Classroom Policies and Procedures</h3>
                        @if(!$locked)
                            <button type="button" wire:click="loadClassroomPoliciesPreset"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Use sample</button>
                        @endif
                    </div>
                    <div class="p-5">
                        <textarea wire:model="classroom_policies" rows="6" placeholder="Attendance, late submissions, academic integrity, classroom decorum, etc..." {{ $locked ? 'readonly disabled' : '' }}
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}"></textarea>
                    </div>
                </div>

                {{-- Learning Plan --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden scroll-mt-16" id="section-learning-plan">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Learning Plan</h3>
                        <p class="mt-0.5 text-xs text-gray-500">One row per week (Weeks 1–18). All weeks must be completed before the syllabus can be submitted.</p>
                    </div>
                    @error('learning_plan')
                        <div class="mx-5 mt-5 rounded-lg border border-red-300 bg-red-50 px-4 py-3">
                            <p class="text-sm font-bold text-red-800"><i class="fas fa-triangle-exclamation mr-2"></i>Complete the learning plan before submitting:</p>
                            <ul class="mt-2 space-y-1 text-sm text-red-700">
                            @foreach($errors->get('learning_plan') as $message)
                                <li class="flex gap-2"><i class="fas fa-circle-xmark mt-0.5 text-red-600"></i>{{ $message }}</li>
                            @endforeach
                            </ul>
                        </div>
                    @enderror
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-300 text-sm">
                            <thead>
                                <tr>
                                    <th class="w-28 border border-gray-300 bg-gray-50 px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-gray-600">Schedule</th>
                                    <th class="w-52 border border-gray-300 bg-gray-50 px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-gray-600">Learning Outcomes</th>
                                    <th class="w-52 border border-gray-300 bg-gray-50 px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-gray-600">Topics &amp; Readings</th>
                                    <th class="w-52 border border-gray-300 bg-gray-50 px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-gray-600">Learning Activities</th>
                                    <th class="w-48 border border-gray-300 bg-gray-50 px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-gray-600">Assessment Tools</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach($items as $i => $item)
                                    @php $week = $i + 1; $exam = $examWeeks[$week] ?? null; @endphp
                                    @if($exam)
                                        <tr>
                                            <td class="h-[100px] w-28 border border-gray-300 bg-gray-50 p-0 text-center align-middle text-xs font-bold uppercase tracking-wide text-gray-600 select-none">
                                                {{ $item['schedule'] }}
                                            </td>
                                            <td colspan="4" class="h-[100px] border border-gray-300 bg-amber-50 p-0 text-center align-middle text-sm font-black uppercase tracking-widest text-amber-800">
                                                {{ $exam }}
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="h-[200px] w-28 border border-gray-300 bg-gray-50 p-0 text-center align-middle text-xs font-bold uppercase tracking-wide text-gray-600 select-none">
                                                {{ $item['schedule'] }}
                                            </td>
                                            <td class="h-[200px] border border-gray-300 p-0 align-top">
                                                <textarea wire:model="items.{{ $i }}.learning_outcomes" {{ $locked ? 'readonly disabled' : '' }}
                                                    class="block h-full w-full resize-none border-0 bg-transparent px-2.5 py-1.5 text-sm leading-snug focus:outline-none focus:ring-0 {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}"
                                                    placeholder="Enter each learning outcome on its own line, e.g.&#10;Explain the key concepts...&#10;Apply the theories..."></textarea>
                                            </td>
                                            <td class="h-[200px] border border-gray-300 p-0 align-top">
                                                <textarea wire:model="items.{{ $i }}.topics_readings" {{ $locked ? 'readonly disabled' : '' }}
                                                    class="block h-full w-full resize-none border-0 bg-transparent px-2.5 py-1.5 text-sm leading-snug focus:outline-none focus:ring-0 {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}"
                                                    placeholder="Enter each topic/reading on its own line, e.g.&#10;Ch. 1: Introduction&#10;Ch. 2: Methods"></textarea>
                                            </td>
                                            <td class="h-[200px] border border-gray-300 p-0 align-top">
                                                <textarea wire:model="items.{{ $i }}.learning_activities" {{ $locked ? 'readonly disabled' : '' }}
                                                    class="block h-full w-full resize-none border-0 bg-transparent px-2.5 py-1.5 text-sm leading-snug focus:outline-none focus:ring-0 {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}"
                                                    placeholder="e.g., Lecture, discussion"></textarea>
                                            </td>
                                            <td class="h-[200px] border border-gray-300 p-0 align-top">
                                                <textarea wire:model="items.{{ $i }}.assessment_tools" {{ $locked ? 'readonly disabled' : '' }}
                                                    class="block h-full w-full resize-none border-0 bg-transparent px-2.5 py-1.5 text-sm leading-snug focus:outline-none focus:ring-0 {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}"
                                                    placeholder="e.g., Quiz 1"></textarea>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 scroll-mt-16" id="section-submit">
                    <a href="{{ route('faculty.syllabus.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-200">Cancel</a>
                    @if($locked)
                        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md bg-amber-50 border border-amber-300 text-amber-800 text-xs font-bold">
                            <i class="fas fa-lock"></i>Submitted &amp; Locked — {{ $submittedAt->format('M d, Y h:i A') }}
                        </span>
                    @else
                        <button type="submit" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-200">
                            <i class="fas fa-save mr-1"></i>Save Draft
                        </button>
                        <button type="button" wire:click="openSubmitConfirmation" class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700">
                            <i class="fas fa-paper-plane mr-1"></i>Submit
                        </button>
                    @endif
                </div>
                @error('submission')
                    <div class="mt-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <i class="fas fa-triangle-exclamation mr-2"></i>{{ $message }}
                    </div>
                @enderror
                @error('syllabus_rules')
                    <div class="mt-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <i class="fas fa-triangle-exclamation mr-2"></i>{{ $message }}
                    </div>
                @enderror
                </div>
        </form>

        @if($confirmSubmit)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" wire:click="cancelSubmitConfirmation"></div>
                <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-bold text-gray-900">Confirm Final Submission</h3>
                        <p class="mt-0.5 text-sm text-gray-500">Once submitted, the syllabus is final and <strong>cannot be edited</strong>.</p>
                    </div>
                    <div class="px-6 py-4 space-y-3 text-sm text-gray-700">
                        <p class="font-semibold text-gray-900">I have reviewed the syllabus and confirm that:</p>
                        <ul class="list-disc list-inside space-y-1.5 text-gray-600">
                            <li>The schedule is accurate and will be followed as stated.</li>
                            <li>The learning plan contents are final and will be followed.</li>
                            <li>The assessment tasks and grading system are final.</li>
                            <li>The syllabus is complete and ready for approval.</li>
                        </ul>
                        <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 cursor-pointer">
                            <input type="checkbox" wire:model="confirmFinal" class="mt-0.5 h-4 w-4 text-indigo-600 rounded">
                            <span>I confirm that this syllabus is <strong>final</strong> and I understand it cannot be edited after submission.</span>
                        </label>
                        @error('confirm_final')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4">
                        <button type="button" wire:click="cancelSubmitConfirmation"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-200">Cancel</button>
                        <button type="button" wire:click="submit"
                            class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700">
                            <i class="fas fa-lock mr-1"></i>Confirm &amp; Submit
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-file-lines text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">Course block not found or not assigned to you.</p>
        </div>
    @endif
</div>
