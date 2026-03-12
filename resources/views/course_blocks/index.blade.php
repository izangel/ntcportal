<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Course Blocks</h2>
            <a href="{{ route('course_blocks.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                + Add New Block
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
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
            <div class="text-sm text-gray-500">{{ $block->section->program->name }}-{{ $block->section->name }}</div>
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