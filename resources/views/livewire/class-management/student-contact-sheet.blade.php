<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Student Contact Sheet</h1>
        <p class="text-sm text-gray-600">View and export contact information for your students.</p>
    </div>

    @if(session()->has('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-circle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">School Year</label>
                <select wire:model.live="academicYearId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}">{{ $academicYear->start_year }} - {{ $academicYear->end_year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                <select wire:model.live="semester" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($semesterOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Class</label>
                <select wire:model.live="selectedBlockId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select a class...</option>
                    @foreach($assignedBlocks as $block)
                        <option value="{{ $block['id'] }}">
                            {{ $block['course_code'] }} - {{ $block['course_name'] }} ({{ $block['sections'] }}, {{ $block['student_count'] }} students)
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($blockInfo)
        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-5 mb-6">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $blockInfo['course_code'] }} - {{ $blockInfo['course_name'] }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $blockInfo['sections'] }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                        <span><i class="fas fa-clock mr-1 text-indigo-500"></i>{{ $blockInfo['schedule_string'] }}</span>
                        @if($blockInfo['room_name'])
                            <span><i class="fas fa-door-open mr-1 text-indigo-500"></i>{{ $blockInfo['room_name'] }}</span>
                        @endif
                        <span><i class="fas fa-users mr-1 text-indigo-500"></i>{{ count($students) }} student(s)</span>
                    </div>
                </div>
                <button wire:click="exportExcel" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700">
                    <i class="fas fa-file-excel"></i>Export Contact Sheet
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 w-10">#</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Student ID</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Student Name</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Gender</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Section</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Birthday</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($students as $index => $student)
                            <tr>
                                <td class="px-4 py-2.5 text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-700">{{ $student['student_number'] }}</td>
                                <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $student['name'] }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-600">{{ $student['gender'] }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-600">{{ $student['section'] }}</td>
                                <td class="px-4 py-2.5 text-sm text-indigo-600">{{ $student['email'] }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-600">{{ $student['birthday'] }}</td>
                                <td class="px-4 py-2.5">
                                    @if($student['fully_enrolled'])
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Fully Enrolled</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">Not Fully Enrolled</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-address-book text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">Select a class above to view the contact sheet.</p>
        </div>
    @endif
</div>
