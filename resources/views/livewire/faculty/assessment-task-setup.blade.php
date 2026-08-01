<div class="max-w-7xl mx-auto space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Assessment Tasks by CLO</h1>
        <p class="mt-1 text-sm text-gray-500">Create assessment tasks and map each item to a course learning outcome.</p>
    </div>

    @if(session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>
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

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-gray-900">Create Assessment Task</h2>
                <form wire:submit.prevent="saveTask" class="space-y-3">
                    <input wire:model="taskTitle" placeholder="Task title" class="w-full rounded-lg border-gray-300 text-sm">
                    @error('taskTitle') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    <div class="grid grid-cols-2 gap-3">
                        <select wire:model="taskType" class="rounded-lg border-gray-300 text-sm">
                            <option>Exam</option><option>Quiz</option><option>Assignment</option><option>Project</option><option>Practical</option>
                        </select>
                        <input type="number" step="0.01" wire:model="taskWeight" placeholder="Weight %" class="rounded-lg border-gray-300 text-sm">
                    </div>
                    <input type="number" step="0.01" wire:model="taskTotalMarks" placeholder="Total marks" class="w-full rounded-lg border-gray-300 text-sm">
                    <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Create Task</button>
                </form>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-gray-900">Map Assessment Item to CLO</h2>
                <form wire:submit.prevent="saveItem" class="space-y-3">
                    <select wire:model="selectedTaskId" class="w-full rounded-lg border-gray-300 text-sm">
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
                    <button class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Map Item to CLO</button>
                </form>
            </div>
        </div>

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
                            <button type="button" wire:click="deleteTask({{ $task->id }})" wire:confirm="Delete this assessment task and all mapped CLO items?" class="text-xs font-semibold text-rose-600 hover:text-rose-800">Delete</button>
                        </div>
                        <div class="mt-3 space-y-1 border-t border-gray-200 pt-2">
                            @forelse($task->items as $item)
                                <div class="flex justify-between text-xs text-gray-700">
                                    <span>{{ $item->item_name }} -> {{ $item->clo->code ?? 'CLO' }}</span>
                                    <span>{{ $item->max_marks }} marks</span>
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
