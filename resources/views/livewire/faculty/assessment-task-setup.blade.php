<div class="max-w-7xl mx-auto space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Assessment Tasks by CLO</h1>
        <p class="mt-1 text-sm text-gray-500">Create assessment tasks and map each item to a course learning outcome.</p>
    </div>

    @if($locked)
        <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
            <i class="fas fa-lock mr-2"></i><strong>Assessment tasks are locked.</strong> They are final and can no longer be changed after syllabus submission.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-3">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">Academic Year</label>
            <select wire:model.live="academicYearId" class="w-full rounded-lg border-gray-300 text-sm">
                @foreach($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}">{{ $academicYear->start_year }} - {{ $academicYear->end_year }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">Semester</label>
            <select wire:model.live="semester" class="w-full rounded-lg border-gray-300 text-sm">
                @foreach($semesters as $semesterOption)
                    <option value="{{ $semesterOption }}">{{ $semesterOption }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">Assigned Course Block</label>
            <select wire:model.live="selectedCourseBlockId" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="">-- Choose Course Block --</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->id }}">{{ $block->course->code }} - {{ $block->course->name }} | {{ $block->section->name ?? 'Section' }}</option>
                @endforeach
            </select>
            @error('selectedCourseBlockId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
    </div>

    @if($selectedBlock)
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
            <strong>{{ $selectedBlock->course->code }} - {{ $selectedBlock->course->name }}</strong>
            <span class="ml-2">Batch {{ $selectedBlock->academicYear->start_year }} | {{ $selectedBlock->semester }} | {{ $selectedBlock->section->name ?? 'Section' }}</span>
        </div>

        <details class="rounded-xl border border-indigo-200 bg-white overflow-hidden">
            <summary class="cursor-pointer select-none px-5 py-3 text-sm font-bold text-indigo-700 flex items-center gap-2 hover:bg-indigo-50">
                <i class="fas fa-eye"></i>
                See a sample setup — example assessment tasks &amp; items
            </summary>
            <div class="px-5 pb-5 pt-1 border-t border-indigo-100">
                <p class="mt-3 text-xs text-gray-600 leading-relaxed">Here is how a typical course could look. Weights total <strong>100%</strong>, every item maps to <strong>one CLO</strong>, and each task's total marks are simply the sum of its items — note the four exams and that an exam may have a laboratory part.</p>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-xs text-left border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50 text-[10px] uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 font-bold w-1/4">Assessment Task</th>
                                <th class="px-3 py-2 font-bold">Assessment Items (marks)</th>
                                <th class="px-3 py-2 font-bold">Mapped CLO</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <tr>
                                <td class="px-3 py-2 align-top font-semibold" rowspan="3"><i class="fas fa-clipboard-check text-indigo-500 mr-1"></i>Quizzes<br><span class="text-[10px] text-gray-500">Quiz · 20%</span></td>
                                <td class="px-3 py-2">Quiz 1 <span class="text-gray-400">· 10 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 1</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Quiz 2 <span class="text-gray-400">· 15 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 2</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Quiz 3 <span class="text-gray-400">· 15 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 3</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 align-top font-semibold" rowspan="2"><i class="fas fa-file-pen text-indigo-500 mr-1"></i>Prelim Exam<br><span class="text-[10px] text-gray-500">Exam · 20% · 100 marks</span></td>
                                <td class="px-3 py-2">Part I: Multiple Choice <span class="text-gray-400">· 40 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 1</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Part II: Problem Solving <span class="text-gray-400">· 60 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 2</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 align-top font-semibold" rowspan="2"><i class="fas fa-flask text-indigo-500 mr-1"></i>Midterm Lab Exam<br><span class="text-[10px] text-gray-500">Exam · 20% · 100 marks · written + laboratory</span></td>
                                <td class="px-3 py-2">Written Part <span class="text-gray-400">· 50 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 3</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Laboratory / Practical Part <span class="text-gray-400">· 50 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 4</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 align-top font-semibold" rowspan="2"><i class="fas fa-file-pen text-indigo-500 mr-1"></i>Prefinal Exam<br><span class="text-[10px] text-gray-500">Exam · 15% · 100 marks</span></td>
                                <td class="px-3 py-2">Part I: Multiple Choice <span class="text-gray-400">· 40 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 3</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Part II: Essay / Analysis <span class="text-gray-400">· 60 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 4</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 align-top font-semibold" rowspan="3"><i class="fas fa-file-signature text-indigo-500 mr-1"></i>Final Exam<br><span class="text-[10px] text-gray-500">Exam · 25% · 100 marks · written + laboratory</span></td>
                                <td class="px-3 py-2">Part I: Multiple Choice <span class="text-gray-400">· 40 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 1</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Part II: Problem Solving <span class="text-gray-400">· 40 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 2</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Part III: Laboratory / Practical Part <span class="text-gray-400">· 20 marks</span></td>
                                <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 4</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-[11px] text-gray-500">
                    <i class="fas fa-lightbulb mr-1"></i>
                    Four exams (Prelim, Midterm, Prefinal, Final) plus a "Quizzes" task add up to <strong>100%</strong>.
                    An exam may include a <strong>laboratory part</strong> — the Midterm Lab Exam and Final Exam each have
                    written <em>and</em> laboratory items. Decide the quiz count from your course plan — usually one per
                    major unit — and enter everything <em>before</em> submitting; tasks and items lock once the syllabus is finalized.
                </p>
            </div>
        </details>

        @if(!$locked)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="mb-2 text-base font-bold text-gray-900">
                    {{ $editingTaskId ? 'Edit Assessment Task' : 'Create Assessment Task' }}
                </h2>
                <div class="mb-4 rounded-lg border border-indigo-100 bg-indigo-50 p-3 text-xs leading-relaxed text-gray-600">
                    <p><i class="fas fa-circle-info mr-1 text-indigo-500"></i><strong class="text-gray-800">Weight %</strong> is this task's share of the final grade — all tasks combined must total exactly <strong>100%</strong> before the syllabus can be submitted.</p>
                    <p class="mt-1"><strong class="text-gray-800">Total marks</strong> are computed for you from the items you map — you don't enter them. Create <em>one</em> "Quizzes" task (e.g., 20%) and add one item per quiz you plan to give.</p>
                    <p class="mt-1"><strong class="text-gray-800">How many quizzes?</strong> Decide the count from your course plan — usually one quiz per topic — and enter those items <em>before</em> submitting. Assessment tasks and items are locked once the syllabus is finalized.</p>
                    @php
                        $weightTotal = (float) $tasks->sum('weight_percentage');
                    @endphp
                    @if($tasks->isNotEmpty())
                        <p class="mt-1.5 font-bold {{ abs($weightTotal - 100) < 0.001 ? 'text-emerald-700' : 'text-rose-700' }}">
                            <i class="fas {{ abs($weightTotal - 100) < 0.001 ? 'fa-circle-check' : 'fa-triangle-exclamation' }} mr-1"></i>
                            Current weight total across all tasks: {{ number_format($weightTotal, 2) }}% {{ abs($weightTotal - 100) < 0.001 ? '— good to go' : '— add up to 100%' }}
                        </p>
                    @endif
                </div>
                <form wire:submit.prevent="saveTask" class="space-y-3">
                    <input wire:model="taskTitle" placeholder="Task title" class="w-full rounded-lg border-gray-300 text-sm">
                    @error('taskTitle') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    <div class="grid grid-cols-2 gap-3">
                        <select wire:model="taskType" class="rounded-lg border-gray-300 text-sm">
                            <option>Exam</option><option>Quiz</option><option>Assignment</option><option>Project</option><option>Practical</option>
                        </select>
                        <input type="number" step="0.01" wire:model="taskWeight" placeholder="Weight %" class="rounded-lg border-gray-300 text-sm">
                    </div>
                    @error('taskWeight') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    <div class="flex gap-2">
                        <button class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            {{ $editingTaskId ? 'Update Task' : 'Create Task' }}
                        </button>
                        @if($editingTaskId)
                            <button type="button" wire:click="cancelEditTask" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                                Cancel
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-900">{{ $editingItemId ? 'Edit Assessment Item' : 'Map Assessment Item to CLO' }}</h2>
                    @if($editingItemId)
                        <button type="button" wire:click="cancelEditItem" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Cancel</button>
                    @endif
                </div>
                <div class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 p-3 text-xs leading-relaxed text-gray-600">
                    <p><i class="fas fa-circle-info mr-1 text-emerald-600"></i>Add each question or section as its own item mapped to <strong>one CLO</strong>. Every CLO must be covered by at least one item, or the syllabus cannot be submitted.</p>
                    <p class="mt-1">The task's total marks update automatically as you add or edit items — no need to know them upfront.</p>
                    <p class="mt-1">You can also <strong>Edit</strong> or <strong>Delete</strong> an existing item from its task's item list below.</p>
                    @php
                        $mappedTask = $selectedTaskId ? $tasks->firstWhere('id', (int) $selectedTaskId) : null;
                    @endphp
                    @if($mappedTask)
                        <p class="mt-1.5 font-bold text-gray-700">
                            <i class="fas fa-calculator mr-1 text-emerald-600"></i>
                            {{ $mappedTask->title }}: {{ $mappedTask->items->count() }} item(s), {{ number_format((float) $mappedTask->items->sum('max_marks'), 2) }} marks so far
                        </p>
                    @endif
                </div>
                <form wire:submit.prevent="saveItem" class="space-y-3">
                    <select wire:model.live="selectedTaskId" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">-- Select Task --</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->title }} ({{ $task->type }})</option>
                        @endforeach
                    </select>
                    <input wire:model="itemName" placeholder="Item name, e.g. Question 1" class="w-full rounded-lg border-gray-300 text-sm">
                    <select wire:model="itemCloId" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">-- Select CLO --</option>
                        @foreach($clos as $clo)
                            <option value="{{ $clo->id }}">{{ $clo->code }} - {{ $clo->description }}</option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" wire:model="itemMarks" placeholder="Maximum marks" class="w-full rounded-lg border-gray-300 text-sm">
                    <button class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">{{ $editingItemId ? 'Update Item' : 'Map Item to CLO' }}</button>
                </form>
            </div>
        </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-base font-bold text-gray-900">Tasks and CLO Items</h2>
            <div class="space-y-3">
                @forelse($tasks as $task)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <strong class="text-gray-900">{{ $task->title }}</strong>
                                <span class="ml-2 text-xs text-gray-500">{{ $task->type }} | {{ $task->weight_percentage }}% | {{ $task->total_marks }} marks</span>
                            </div>
                            <div class="flex items-center gap-3">
                                @if(!$locked)
                                    <button type="button" wire:click="editTask({{ $task->id }})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Edit</button>
                                    <button type="button" wire:click="deleteTask({{ $task->id }})" wire:confirm="Delete this assessment task and all mapped CLO items?" class="text-xs font-semibold text-rose-600 hover:text-rose-800">Delete</button>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 space-y-1 border-t border-gray-200 pt-2">
                            @forelse($task->items as $item)
                                <div class="flex items-center justify-between gap-2 text-xs text-gray-700">
                                    <span class="min-w-0">{{ $item->item_name }} -> {{ $item->clo->code ?? 'CLO' }}</span>
                                    <span class="flex shrink-0 items-center gap-3">
                                        <span class="text-gray-500">{{ number_format((float) $item->max_marks, 2) }} marks</span>
                                        @if(!$locked)
                                            <span class="flex items-center gap-2">
                                                <button type="button" wire:click="editItem({{ $item->id }})" class="font-semibold text-indigo-600 hover:text-indigo-800">Edit</button>
                                                <button type="button"
                                                    wire:click="deleteItem({{ $item->id }})"
                                                    wire:confirm="Delete assessment item '{{ $item->item_name }}'? This also removes any recorded student marks for it."
                                                    class="font-semibold text-rose-600 hover:text-rose-800">Delete</button>
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            @empty
                                <span class="text-xs italic text-gray-400">No CLO items mapped yet.</span>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-sm italic text-gray-500">No assessment tasks created for this course and batch.</p>
                @endforelse
            </div>
        </div>
    @else
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-10 text-center text-sm text-gray-500">Select an assigned course block to manage its assessment tasks and CLO mappings.</div>
    @endif
</div>
