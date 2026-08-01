<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Students / Class Details</h1>
        <p class="text-sm text-gray-600">View the roster of each class with student details and their attendance summary.</p>
    </div>

    @if (session()->has('error'))
        <div class="mb-4 p-4 text-sm text-rose-800 bg-rose-100 rounded-lg border border-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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

            <div class="lg:col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Class / Course Block</label>
                <select wire:model.live="selectedBlockId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Choose a class --</option>
                    @foreach($assignedBlocks as $block)
                        <option value="{{ $block['id'] }}">
                            {{ $block['course_code'] }} - {{ $block['course_name'] }} ({{ $block['sections'] }}) {{ $block['schedule_string'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($blockInfo)
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-base font-bold text-gray-800">
                        {{ $blockInfo['course_code'] }} - {{ $blockInfo['course_name'] }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $blockInfo['sections'] }}
                        @if($blockInfo['schedule_string'])
                            <span class="mx-1 text-gray-300">|</span> <i class="fas fa-clock mr-1"></i>{{ $blockInfo['schedule_string'] }}
                        @endif
                        @if($blockInfo['room_name'])
                            <span class="mx-1 text-gray-300">|</span> <i class="fas fa-door-open mr-1"></i>{{ $blockInfo['room_name'] }}
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800">{{ count($students) }} students</span>
                    <button wire:click="exportExcel" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg hover:bg-emerald-100">
                        <i class="fas fa-file-excel mr-1.5"></i> Export Excel
                    </button>
                </div>
            </div>

            <div class="max-w-sm mb-4">
                <div class="relative">
                    <i class="fas fa-magnifying-glass absolute left-3 top-2.5 text-xs text-gray-400"></i>
                    <input type="text" wire:model.live.debounce.250ms="searchTerm" placeholder="Search by name or ID number..." class="w-full pl-8 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            @if(count($students) === 0)
                <p class="text-sm text-gray-400 italic py-8 text-center">No students match your search.</p>
            @else
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">ID Number</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Section</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Gender</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">P</th>
                                <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">L</th>
                                <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">A</th>
                                <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">E</th>
                                <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">Rate %</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($students as $index => $student)
                                <tr>
                                    <td class="px-4 py-2 text-xs text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 text-xs font-medium text-gray-800">{{ $student['name'] }}</td>
                                    <td class="px-4 py-2 text-xs text-gray-600">{{ $student['student_number'] }}</td>
                                    <td class="px-4 py-2 text-xs text-gray-600">{{ $student['section'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-xs text-gray-600">{{ $student['gender'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-xs text-gray-600">{{ $student['email'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-center text-xs font-bold text-emerald-700">{{ $student['present'] }}</td>
                                    <td class="px-4 py-2 text-center text-xs font-bold text-amber-700">{{ $student['late'] }}</td>
                                    <td class="px-4 py-2 text-center text-xs font-bold text-rose-700">{{ $student['absent'] }}</td>
                                    <td class="px-4 py-2 text-center text-xs font-bold text-gray-600">{{ $student['excused'] }}</td>
                                    <td class="px-4 py-2 text-center text-xs font-bold {{ ($student['rate'] ?? 100) < 80 ? 'text-rose-700' : 'text-gray-800' }}">
                                        {{ $student['rate'] ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @else
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">Select a class above to view its students.</p>
        </div>
    @endif
</div>
