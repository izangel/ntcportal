<div class="max-w-7xl mx-auto space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Faculty Assessment Score Entry</h1>
        <p class="mt-1 text-sm text-gray-500">Select your course block and enter scores for its enrolled students.</p>
    </div>

    @if(session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-4">
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
        <div class="md:col-span-2">
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">Your Course Block</label>
            <select wire:model.live="selectedCourseBlockId" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="">-- Choose Course Block --</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->id }}">
                        Block #{{ $block->id }} | {{ $block->course->code }} - {{ $block->course->name }} |
                        {{ $block->sections->pluck('name')->implode(', ') ?: 'Section' }}
                    </option>
                @endforeach
            </select>
            @error('selectedCourseBlockId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
    </div>

    @if($selectedBlock)
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
            <strong>Block #{{ $selectedBlock->id }}: {{ $selectedBlock->course->code }} - {{ $selectedBlock->course->name }}</strong>
            <span class="ml-2">{{ $selectedBlock->sections->pluck('name')->implode(', ') }} | Batch {{ $selectedBlock->academicYear->start_year }} | {{ $selectedBlock->semester }}</span>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">Assessment Task</label>
            <select wire:model.live="selectedTaskId" class="w-full rounded-lg border-gray-300 text-sm md:w-1/2">
                <option value="">-- Choose Assessment Task --</option>
                @foreach($tasks as $task)
                    <option value="{{ $task->id }}">{{ $task->title }} | {{ $task->type }} | {{ $task->weight_percentage }}%</option>
                @endforeach
            </select>
            @error('selectedTaskId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        @if($selectedTask)
            <form wire:submit.prevent="saveScores" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-5 py-4">
                    <div>
                        <h2 class="font-bold text-gray-900">{{ $selectedTask->title }}</h2>
                        <p class="text-xs text-gray-500">{{ $selectedTask->type }} | {{ $selectedTask->weight_percentage }}% | {{ $selectedTask->total_marks }} total marks</p>
                    </div>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-xs font-semibold text-white hover:bg-indigo-700">Save Scores</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold uppercase text-gray-600">Student</th>
                                @foreach($selectedTask->items as $item)
                                    <th class="border-l border-gray-200 px-3 py-3 text-center font-bold text-indigo-700">
                                        {{ $item->item_name }}<br><span class="text-[10px] font-normal text-gray-500">{{ $item->clo->code ?? 'CLO' }} | Max {{ $item->max_marks }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($students as $student)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->mid_name }}</td>
                                    @foreach($selectedTask->items as $item)
                                        <td class="border-l border-gray-100 px-3 py-2 text-center">
                                            <input type="number" step="0.01" min="0" max="{{ $item->max_marks }}" wire:model="scores.{{ $student->id }}.{{ $item->id }}" class="w-20 rounded-md border-gray-300 text-center text-xs">
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ $selectedTask->items->count() + 1 }}" class="px-5 py-8 text-center italic text-gray-400">No students are enrolled in this course block.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        @else
            <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">Select an assessment task to enter student scores.</div>
        @endif
    @else
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-10 text-center text-sm text-gray-500">Select your course block to begin score entry.</div>
    @endif
</div>
