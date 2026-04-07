<div class="p-6 bg-white rounded-lg shadow">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b pb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-800 uppercase">{{ $block->course->code }}: {{ $block->course->name }}</h1>
            <p class="text-blue-600 font-bold">Faculty: {{ $block->faculty->last_name }}, {{ $block->faculty->first_name }}</p>
            <div class="flex gap-4 mt-2 text-sm text-gray-500">
                <span><strong>Room:</strong> {{ $block->room ?? 'TBA' }}</span>
                <span><strong>Schedule:</strong> {{ $block->schedule_string }}</span>
                <span><strong>Term:</strong> {{ $block->semester }} ({{ $block->academicYear->start_year }}-{{ $block->academicYear->end_year }})</span>
            </div>
        </div>
        
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded shadow hover:bg-gray-700 text-sm font-bold">
                Print Class List
            </button>
            <a href="{{ route('assign.courseblocks') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded border hover:bg-gray-200 text-sm font-bold">
                Back to Assignment
            </a>
        </div>
    </div>

    <div class="mb-4">
        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-black uppercase">
            Total Enrolled: {{ $students->count() }}
        </span>
    </div>

    <div class="border rounded-xl overflow-hidden shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr class="text-[10px] font-black text-gray-500 uppercase tracking-widest">
                    <th class="px-6 py-3 text-left w-12">#</th>
                    <th class="px-6 py-3 text-left">Student ID</th>
                    <th class="px-6 py-3 text-left">Full Name</th>
                    <th class="px-6 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($students as $index => $student)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 text-sm text-gray-400 font-mono">{{ $index + 1 }}</td>
                        <td class="px-6 py-3 text-sm font-bold text-blue-600 font-mono">{{ $student->student_id }}</td>
                        <td class="px-6 py-3 text-sm font-bold text-gray-800 uppercase">
                            {{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            <button 
                                wire:click="removeStudent({{ $student->id }})"
                                wire:confirm="Are you sure you want to remove this student from this course block?"
                                class="text-red-400 hover:text-red-600 transition">
                                <svg class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-400 italic">
                            No students are currently enrolled in this block.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (session()->has('message'))
        <div class="mt-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm font-bold text-center border border-red-100">
            {{ session('message') }}
        </div>
    @endif
</div>