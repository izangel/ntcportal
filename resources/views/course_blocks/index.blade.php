<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        
        {{-- Header Container --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            
            {{-- Left Side: Title and Filters --}}
            <div class="flex flex-col md:flex-row items-center gap-4">
                <h2 class="text-2xl font-bold text-gray-800">Course Blocks</h2>
                
                <form action="{{ route('course_blocks.index') }}" method="GET" class="flex items-center gap-2">
                    <select name="academic_year_id" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500">
                        <option value="">All Academic Years</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>
                                AY {{ $ay->start_year }}-{{ $ay->end_year }}
                            </option>
                        @endforeach
                    </select>

                    <select name="semester" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500">
                        <option value="">All Semesters</option>
                        {{-- Using the full names seen in your database --}}
                        <option value="1st Semester" {{ request('semester') == '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                        <option value="2nd Semester" {{ request('semester') == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                        <option value="Summer" {{ request('semester') == 'Summer' ? 'selected' : '' }}>Summer</option>
                    </select>

                    @if(request('academic_year_id') || request('semester'))
                        <a href="{{ route('course_blocks.index') }}" class="text-xs text-red-500 hover:text-red-700 underline ml-1">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Right Side: Action Button --}}
            <a href="{{ route('course_blocks.create') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add New Block
            </a>
        </div>

        {{-- Table Section Start --}}
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            {{-- ... keep your existing table code ... --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course / Section</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule / Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
<tbody class="bg-white divide-y divide-gray-200">
    @forelse($courseBlocks as $block)
    <tr>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">{{ $block->course->code ?? 'N/A' }}</div>
           
        </td>

        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $block->schedule_string }}</div>
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ $block->room_name }}
            </span>
        </td>

        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $block->faculty->last_name }}, {{ $block->faculty->first_name }} {{ $block->faculty->middle_name }}</div>
        </td>

        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <div>{{ $block->semester }}</div>
            <div class="text-xs">
                {{ $block->academicYear->start_year ?? '' }}-{{ $block->academicYear->end_year ?? '' }}
            </div>
        </td>

<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
    <div class="flex justify-end gap-3">
        <a href="{{ route('course_blocks.edit', $block->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>

        <form action="{{ route('course_blocks.destroy', $block->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will remove the block.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-900 bg-transparent border-none p-0 cursor-pointer">
                Delete
            </button>
        </form>
    </div>
</td>
    </tr>
    @empty
    <tr>
        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
            No course blocks found.
        </td>
    </tr>
    @endforelse
</tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $courseBlocks->links() }}
            </div>
        </div>
    </div>
</x-app-layout>