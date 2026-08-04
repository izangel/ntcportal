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

    @if(session()->has('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-circle-check mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($data)
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
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $course->prerequisite ?: '—' }}</p>
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
                                <span class="w-16 shrink-0 text-xs font-bold text-indigo-600">{{ $peo->code }}</span>
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
                                <span class="w-16 shrink-0 text-xs font-bold text-indigo-600">{{ $po->code }}</span>
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
                            wire:key="assessment-tasks-{{ $block->id }}-{{ $programId }}" />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Grading System</h3>
                    </div>
                    <div class="p-5">
                        <textarea wire:model="grading_system" rows="4" placeholder="Describe the grading system, e.g., major exams 40%, quizzes 30%, assignments 30%..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Textbooks and References</h3>
                    </div>
                    <div class="p-5">
                        <textarea wire:model="textbooks_references" rows="4" placeholder="List textbooks and other references used in the course..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Classroom Policies and Procedures</h3>
                    </div>
                    <div class="p-5">
                        <textarea wire:model="classroom_policies" rows="4" placeholder="Attendance, late submissions, academic integrity, classroom decorum, etc..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                {{-- Learning Plan --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Learning Plan</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Learning outcomes, topics &amp; readings, schedule, learning activities, and assessment tools.</p>
                        </div>
                        <button type="button" wire:click="addRow" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-bold hover:bg-indigo-700">
                            <i class="fas fa-plus"></i>Add Row
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-40 px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Learning Outcomes</th>
                                    <th class="w-52 px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Topics &amp; Readings</th>
                                    <th class="w-32 px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Schedule</th>
                                    <th class="w-52 px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Learning Activities</th>
                                    <th class="w-48 px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Assessment Tools</th>
                                    <th class="w-12 px-2 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($items as $i => $item)
                                    <tr>
                                        <td class="px-4 py-3 align-top">
                                            <textarea wire:model="items.{{ $i }}.learning_outcomes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="e.g., Explain the key concepts..."></textarea>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <textarea wire:model="items.{{ $i }}.topics_readings" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="e.g., Ch. 1: Introduction"></textarea>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="text" wire:model="items.{{ $i }}.schedule" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="e.g., Week 1-3">
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <textarea wire:model="items.{{ $i }}.learning_activities" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="e.g., Lecture, discussion"></textarea>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <textarea wire:model="items.{{ $i }}.assessment_tools" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="e.g., Quiz 1"></textarea>
                                        </td>
                                        <td class="px-2 py-3 text-center align-top">
                                            <button type="button" wire:click="removeRow({{ $i }})" class="text-red-500 hover:text-red-700" title="Remove row">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('faculty.syllabus.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-200">Cancel</a>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700">
                        <i class="fas fa-save mr-1"></i>Save Syllabus
                    </button>
                </div>
                @error('syllabus_rules')
                    <div class="mt-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <i class="fas fa-triangle-exclamation mr-2"></i>{{ $message }}
                    </div>
                @enderror
            </div>
        </form>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-file-lines text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">Course block not found or not assigned to you.</p>
        </div>
    @endif
</div>
