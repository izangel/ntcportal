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
        <label class="block text-sm font-medium text-gray-700">Select Course Block to Assign</label>
        <select wire:model.live="course_block_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 border p-2">
            <option value="">-- Select a Block --</option>
            @foreach($courseBlocks as $block)
                <option value="{{ $block->id }}">
                    {{ $block->course->code ?? 'N/A' }} - {{ $block->course->name ?? 'No Name' }} | 
                    Faculty: {{ $block->faculty->last_name ?? 'Unassigned' }}, {{ $block->faculty->first_name ?? '' }} | 
                    Sched: {{ $block->days ?? '' }} 
                           {{ $block->start_time ? date('g:i A', strtotime($block->start_time)) : '' }} - 
                           {{ $block->end_time ? date('g:i A', strtotime($block->end_time)) : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto mb-6 border rounded">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100 border-b">
                <tr class="text-xs font-semibold uppercase text-gray-700">
                    <th class="p-3 text-left">LAST NAME</th>
                    <th class="p-3 text-left">FIRST NAME</th>
                    <th class="p-3 text-left">STUDENT ID</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 uppercase text-sm font-medium">{{ $student->last_name }}</td>
                        <td class="p-3 uppercase text-sm font-medium">{{ $student->first_name }}</td>
                        <td class="p-3 text-sm text-blue-600 font-bold">{{ $student->student_id }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-400">
                            {{ $target_section_id ? 'No students found in this section.' : 'Please select a Section below to view the student list.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center gap-4 bg-gray-50 p-4 rounded border">
        <div class="flex-1">
            <select wire:model.live="target_section_id" 
                @disabled(!$academic_year_id || !$semester)
                class="border p-2 rounded w-full disabled:bg-gray-200 border-blue-400">
                <option value="">Select Section...</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>
        </div>

        <button wire:click="addAllStudents" 
            wire:loading.attr="disabled"
            @disabled(!$target_section_id || !$course_block_id)
            class="bg-blue-600 text-white px-8 py-2 rounded font-bold hover:bg-blue-700 disabled:opacity-50 flex items-center shadow-md">
            
            <span wire:loading.remove>Add All Students</span>
            <span wire:loading>Processing...</span>
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mt-4 p-3 bg-green-100 text-green-700 rounded shadow-sm border border-green-200 text-sm">
            {{ session('message') }}
        </div>
    @endif
</div>