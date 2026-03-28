<div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800">📋 My Course Load</h2>
        <p class="text-gray-600">View and manage your assigned teaching schedule for the current semester.</p>
    </div>

    <div class="bg-white shadow-md rounded-xl p-6 mb-6 border-l-4 border-indigo-600">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Academic Year</label>
                <select wire:model.live="academicYearId" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }} - {{ $ay->end_year }}</option> 
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Semester</label>
                <select wire:model.live="semester" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Course Code</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Course Description</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Schedule</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Sections</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($assignedBlocks as $block)
                        <tr class="hover:bg-indigo-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-700">{{ $block['course_code'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $block['course_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 italic">{{ $block['schedule_string'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <span class="bg-gray-100 px-2 py-1 rounded text-xs font-medium">{{ $block['sections'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($block['finalized'])
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Finalized</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Open</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('faculty.course-blocks', ['selectedBlockId' => $block['id']]) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded hover:bg-indigo-700 transition">
                                    Go to Grading
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                No courses found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>