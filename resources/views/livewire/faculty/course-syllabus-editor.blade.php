<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
        <div class="flex items-center gap-2">
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
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
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
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
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
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 flex items-center justify-between gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Grading System</h3>
                            <div class="flex items-center gap-2">
                                @if(!$locked)
                                    <span class="text-xs text-gray-500 font-medium">Use sample:</span>
                                    <button type="button" wire:click="loadGradingPreset('lecture')"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lecture</button>
                                    <button type="button" wire:click="loadGradingPreset('lecture_alt')"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lecture (alt)</button>
                                    <button type="button" wire:click="loadGradingPreset('lab_flat')"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lab</button>
                                    <button type="button" wire:click="loadGradingPreset('lab_split')"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lab (split)</button>
                                    <span class="mx-1 text-gray-300">|</span>
                                    <button type="button" wire:click="addGradingComponent"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">+ Add Assessment</button>
                                @endif
                            </div>
                        </div>
                        <div class="p-5 space-y-3">
                            @php
                                $total = 0;
                                foreach ($grading_components as $gc) { $total += (float) ($gc['percentage'] ?? 0); }
                                $color = abs($total - 100) < 0.001 ? 'text-green-600' : 'text-red-600';
                            @endphp
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs text-gray-500">Enter the assessment/requirement type and its percentage. The total must equal <strong>100%</strong>.</p>
                                <span class="text-sm font-bold {{ $color }}">Total: {{ number_format($total, 2, '.', '') }}%</span>
                            </div>

                            @error('grading_components')
                                <span class="block text-xs text-red-600">{{ $message }}</span>
                            @enderror

                            @foreach($grading_components as $index => $component)
                                <div class="flex items-center gap-3" wire:key="grading-{{ $component['row_id'] ?? $index }}">
                                    <input type="text" wire:model.live="grading_components.{{ $index }}.assessment_type" {{ $locked ? 'readonly disabled' : '' }}
                                        placeholder="e.g., First Exam, Quizzes, Class Participation"
                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}" />
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.01" min="0" max="100" wire:model.live="grading_components.{{ $index }}.percentage" {{ $locked ? 'readonly disabled' : '' }}
                                            placeholder="0" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}" />
                                        <span class="text-gray-500 text-sm">%</span>
                                    </div>
                                    @if(!$locked)
                                        <button type="button" wire:click="removeGradingComponent('{{ $component['row_id'] ?? $index }}')"
                                            class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Textbooks and References</h3>
                            </div>
                            <div class="p-5">
                                <textarea wire:model="textbooks_references" rows="4" placeholder="List textbooks and other references used in the course..." {{ $locked ? 'readonly disabled' : '' }}
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $locked ? 'bg-gray-100 text-gray-600' : '' }}"></textarea>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
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

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
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
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
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

                <div class="flex flex-wrap items-center justify-end gap-3">
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
