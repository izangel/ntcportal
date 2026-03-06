<div class="p-6 bg-white rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Assign Students to Course Blocks (for Regular only)</h1>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <select wire:model.live="academic_year_id" class="border p-2 rounded w-full">
            <option value="">Select Academic Year</option>
            @foreach($academicYears as $ay)
                <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
            @endforeach
        </select>

        <select wire:model.live="semester" class="border p-2 rounded w-full">
            <option value="">Select Semester</option>
            @foreach($semesters as $sem)
                <option value="{{ $sem }}">{{ $sem }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-6">
<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700">Select Course Block</label>
    <select wire:model.live="course_block_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <option value="">-- Select a Block --</option>
        @foreach($courseBlocks as $block)
            <option value="{{ $block->id }}">
                {{-- Match: MK201 - Principles of Marketing --}}
                {{ $block->course->code ?? 'N/A' }} - {{ $block->course->name ?? 'No Name' }} | 
                
                {{-- Match: Smith, Jane --}}
                Faculty: {{ $block->faculty->name ?? 'Unassigned' }} | 
                
                {{-- Match: MWF 10:00 AM - 11:30 AM --}}
                Sched: {{ $block->schedule_string ?? ($block->days . ' ' . $block->start_time . ' - ' . $block->end_time) }}
            </option>
        @endforeach
    </select>
</div>
    </div>

    <div class="overflow-x-auto mb-6 border rounded">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Student ID</th>
                    <th class="p-3 text-left">Name</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr class="border-t">
                        <td class="p-3">{{ $student->student_id }}</td>
                        <td class="p-3">{{ $student->last_name }}, {{ $student->first_name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="p-6 text-center text-gray-400">No students assigned to this block yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center gap-4 bg-gray-50 p-4 rounded border">
        <div class="flex-1">
            <select wire:model.live="target_section_id" 
                @disabled(!$academic_year_id || !$semester || !$course_block_id)
                class="border p-2 rounded w-full disabled:bg-gray-200">
                <option value="">Select Section...</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>
        </div>

<button wire:click="addAllStudents" 
    wire:loading.attr="disabled"
    @disabled(!$target_section_id)
    class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700 disabled:opacity-50 flex items-center">
    
    {{-- Show a spinner or text change while processing --}}
    <span wire:loading.remove>Add All Students</span>
    <span wire:loading>Processing...</span>
</button>
    </div>

    @if (session()->has('message'))
        <div class="mt-4 p-3 bg-green-100 text-green-700 rounded">{{ session('message') }}</div>
    @endif
</div>