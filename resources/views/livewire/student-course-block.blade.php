<div>
    <div class="p-6 bg-white border-b border-gray-200 shadow-sm sm:rounded-lg">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">My Class Schedule</h2>
        <div class="mb-4"></div>
        <div class="bg-white shadow-md rounded-xl p-6 mb-6 border-l-4 border-indigo-600">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Academic Year</label>
                <select wire:model.live="selectedAcademicYearId" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }} - {{ $ay->end_year }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Semester</label>
                <select wire:model.live="selectedSemester" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }} Semester</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

        <div class="overflow-x-auto">
            @if(count($studentBlocks) > 0)
            <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($studentBlocks as $block)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $block->course->code ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $block->course->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $block->faculty->last_name ?? '' }}, {{ $block->faculty->first_name ?? '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $block->room_name ?? 'TBA' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $block->schedule_string ?? 'TBA' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <div class="p-6 text-center text-gray-500 bg-white border border-gray-200 rounded-lg">
                    No schedule found for the selected academic period.
                </div>
            @endif
        </div>
    </div>
</div>
