<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border border-gray-200">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-3">
                    {{ __('Student Course Block') }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="flex flex-col">
                        <label for="academic_year" class="block text-sm font-medium text-gray-700 mb-2">Filter by Academic Year</label>
                        <select wire:model.live="selectedAcademicYear" id="academic_year"
                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-gray-700 h-11">
                            <option value="">Select AY</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->start_year }} - {{ $year->end_year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="semester" class="block text-sm font-medium text-gray-700 mb-2">Filter by Semester</label>
                        <select wire:model.live="selectedSemester" id="semester"
                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-gray-700 h-11">
                            <option value="">Select Semester</option>
                            <option value="First Semester">First Semester</option>
                            <option value="Second Semester">Second Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Assigned Courses</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border rounded-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Course Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Faculty</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Schedule</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($subjectLoad as $load)
                                    @if ($load->course)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">{{ $load->course->code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $load->course->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $load->faculty_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $load->room_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $load->schedule_string }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 whitespace-nowrap text-sm text-gray-400 text-center italic">
                                            No subjects assigned yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
