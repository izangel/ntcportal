<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Course Blocks</h2>
            <a href="{{ route('course_blocks.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                + Add New Block
            </a>
        </div>

        {{-- Filter Bar --}}
        <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-center justify-between">
            <form action="{{ route('course_blocks.index') }}" method="GET" class="flex items-center gap-4">
                <div class="w-96">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Select Term Filter</label>
                    <select name="term_filter" onchange="this.form.submit()" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Select Term --</option>
                        
                        @foreach($academicYears as $ay)
                            @foreach($activeSemesters as $activeSem)
                                @php 
                                    $value = $ay->id . '|' . $activeSem->name; 
                                    $isSelected = (request('term_filter') == $value);
                                @endphp
                                <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                                    SY {{ $ay->start_year }}-{{ $ay->end_year }}, {{ $activeSem->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-3 mt-5">
                    {{-- Native Export Button --}}
                    <button type="submit" name="export_excel" value="1" 
                        @if(!request('term_filter')) disabled @endif
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded shadow-sm disabled:bg-gray-300 disabled:cursor-not-allowed transition">
                        Export Excel
                    </button>

                    <a href="{{ route('course_blocks.index') }}" class="text-sm text-gray-400 hover:text-red-500 underline">Reset View</a>
                </div>
            </form>
        </div>

        {{-- Main Table --}}
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course / Section</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Schedule</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Faculty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($courseBlocks as $block)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $block->course->code ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $block->section->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $block->schedule_string }}</div>
                            <div class="text-xs text-blue-600 font-semibold">{{ $block->room_name }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $block->faculty->last_name ?? 'N/A' }}, {{ $block->faculty->first_name ?? '' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $block->semester }}</div>
                            <div class="text-xs text-gray-500">{{ $block->academicYear->start_year ?? '' }}-{{ $block->academicYear->end_year ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('course_blocks.edit', $block->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form action="{{ route('course_blocks.destroy', $block->id) }}" method="POST" onsubmit="return confirm('Delete this record?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">
                            No records match the selected term combination.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($courseBlocks->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $courseBlocks->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>