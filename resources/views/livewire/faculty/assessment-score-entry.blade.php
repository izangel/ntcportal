<div class="max-w-7xl mx-auto space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Assessment Score Entry (Class Record)</h1>
        <p class="mt-1 text-sm text-gray-500">Enter each student's marks on the assessment items of every task. Task totals and the overall percentage are computed automatically.</p>
    </div>

    @if(session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">
            {{ $errors->first() }}
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
        <style>
            /* Hide the native number-input spinners */
            input[type=number]::-webkit-outer-spin-button,
            input[type=number]::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            input[type=number] {
                -moz-appearance: textfield;
                appearance: textfield;
            }
        </style>
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
            <strong>Block #{{ $selectedBlock->id }}: {{ $selectedBlock->course->code }} - {{ $selectedBlock->course->name }}</strong>
            <span class="ml-2">{{ $selectedBlock->sections->pluck('name')->implode(', ') }} | Batch {{ $selectedBlock->academicYear->start_year }} | {{ $selectedBlock->semester }} | {{ $students->count() }} student(s)</span>
        </div>

        @if($tasks->isEmpty())
            <div class="rounded-xl border-2 border-dashed border-gray-300 p-10 text-center text-sm text-gray-500">
                No assessment tasks exist for this course and batch. Set up tasks in Assessment Tasks Setup first.
            </div>
        @else
            @php
                // Flatten tasks into their item columns so each item is one column.
                $columns = $tasks->flatMap(function ($task) {
                    return $task->items->map(fn ($item) => (object) [
                        'item' => $item,
                        'task' => $task,
                        'clo_code' => $item->clo->code ?? 'CLO',
                    ]);
                })->values();

                $overallWeightsTotal = (float) $tasks->sum('weight_percentage');
                $colCount = $columns->count();
            @endphp

            <form wire:submit.prevent="saveScores" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-5 py-4">
                    <div>
                        <h2 class="font-bold text-gray-900">Class Record</h2>
                        <p class="text-xs text-gray-500">{{ $tasks->count() }} task(s) · {{ $colCount }} item(s). Scroll horizontally to see all columns; the total marks for each item is at the top of its column.</p>
                    </div>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-xs font-semibold text-white hover:bg-indigo-700">Save Scores</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-xs">
                        <thead>
                            {{-- Row 1: task type groups --}}
                            <tr>
                                <td class="sticky left-0 z-20 bg-slate-100 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-500" rowspan="3">Student</td>
                                @foreach($taskGroups as $type => $tasksInGroup)
                                    <td colspan="{{ $tasksInGroup->sum(fn ($t) => $t->items->count()) }}" class="border-l border-gray-200 bg-slate-100 px-3 py-1.5 text-center text-[10px] font-black uppercase tracking-widest text-slate-500">
                                        {{ $type }}
                                    </td>
                                @endforeach
                                <td class="bg-indigo-50 px-3 py-1.5 text-center text-[10px] font-black uppercase tracking-widest text-indigo-700" rowspan="3">Overall %</td>
                            </tr>

                            {{-- Row 2: tasks (name + weight) spanning their items --}}
                            <tr>
                                @foreach($tasks as $task)
                                    <td colspan="{{ max(1, $task->items->count()) }}" class="border-l border-gray-200 bg-gray-50 px-2 py-2 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" wire:click="moveTask({{ $task->id }}, -1)" title="Move this task earlier"
                                                class="rounded bg-gray-200 px-1.5 py-0.5 text-[10px] font-bold text-gray-600 hover:bg-gray-300">&larr;</button>
                                            <button type="button" wire:click="moveTask({{ $task->id }}, 1)" title="Move this task later"
                                                class="rounded bg-gray-200 px-1.5 py-0.5 text-[10px] font-bold text-gray-600 hover:bg-gray-300">&rarr;</button>
                                        </div>
                                        <div class="font-bold text-indigo-700 mt-1">{{ $task->title }}</div>
                                        <div class="text-[10px] font-normal text-gray-500">{{ $task->type }} | {{ $task->weight_percentage }}%</div>
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Row 3: item name + total marks at top of each column --}}
                            <tr class="bg-gray-100">
                                @foreach($columns as $col)
                                    <th class="border-l border-gray-200 px-2 py-2 text-center align-bottom">
                                        <div class="font-bold text-gray-700 leading-tight">{{ $col->item->item_name }}</div>
                                        <div class="text-[10px] font-normal text-gray-500">{{ $col->clo_code }} · max {{ number_format((float) $col->item->max_marks, 0) }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($students as $student)
                                @php
                                    $overallPoints = 0;
                                    $overallWeightsUsed = 0;
                                @endphp
                                <tr class="hover:bg-gray-50/50">
                                    <td class="sticky left-0 z-10 bg-white px-4 py-3 font-semibold text-gray-800">
                                        {{ $student->last_name }}, {{ $student->first_name }}
                                    </td>
                                    @foreach($columns as $col)
                                        @php
                                            $score = $this->scores[$student->id][$col->item->id] ?? '';
                                            $meta = $scoreMeta[$col->item->id] ?? null;
                                        @endphp
                                        <td class="border-l border-gray-100 px-1.5 py-2 text-center">
                                            <input type="number" step="0.01" min="0" max="{{ $col->item->max_marks }}"
                                                wire:model.live="scores.{{ $student->id }}.{{ $col->item->id }}"
                                                data-item-id="{{ $col->item->id }}"
                                                data-task-id="{{ $col->task->id }}"
                                                data-task-max="{{ $meta['task_max'] ?? 0 }}"
                                                data-task-weight="{{ $meta['weight'] ?? 0 }}"
                                                title="{{ $col->item->item_name }} ({{ $col->clo_code }}) — max {{ $col->item->max_marks }}"
                                                class="w-16 rounded-md border-gray-200 text-center text-[11px]">
                                        </td>
                                    @endforeach
                                    <td class="bg-indigo-50/50 px-3 py-2 text-center" title="Overall weighted percentage across all tasks">
                                        @php
                                            $overallPoints = 0;
                                            $overallWeightsUsed = 0;
                                            foreach ($tasks as $task) {
                                                $max = (float) $task->total_marks;
                                                if ($max > 0) {
                                                    $obtained = $task->items->sum(fn ($it) => (float) ($this->scores[$student->id][$it->id] ?? 0));
                                                    $overallPoints += $task->weight_percentage * ((min($obtained, $max)) / $max);
                                                    $overallWeightsUsed += $task->weight_percentage;
                                                }
                                            }
                                            $overall = $overallWeightsUsed > 0 ? ($overallPoints / $overallWeightsUsed) * 100 : null;
                                        @endphp
                                        <span data-overall class="text-sm font-black {{ !is_null($overall) && $overall >= 75 ? 'text-emerald-700' : 'text-amber-700' }}">
                                            {{ !is_null($overall) ? number_format($overall, 1).'%' : '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $colCount + 2 }}" class="px-5 py-8 text-center italic text-gray-400">No students are enrolled in this course block.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        @endif
    @else
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-10 text-center text-sm text-gray-500">Select your course block to begin score entry.</div>
    @endif
</div>

@push('scripts')
<script>
    // Arrow up/down navigation between rows in the same score column.
    document.addEventListener('DOMContentLoaded', () => {
        document.addEventListener('keydown', (e) => {
            const t = e.target;
            if (!t || t.tagName !== 'INPUT' || t.type !== 'number') return;
            if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
            e.preventDefault();
            const row = t.closest('tr');
            const tbody = row && row.closest('tbody');
            if (!tbody) return;
            const rows = [...tbody.querySelectorAll('tr')];
            const inputsInRow = [...row.querySelectorAll('input[type=number]')];
            const colIndex = inputsInRow.indexOf(t);
            const targetRow = rows[rows.indexOf(row) + (e.key === 'ArrowDown' ? 1 : -1)];
            if (!targetRow) return;
            const target = [...targetRow.querySelectorAll('input[type=number]')][colIndex];
            if (target) { target.focus(); target.select(); }
        });
    });
</script>
@endpush
