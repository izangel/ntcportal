<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Class Analytics</h1>
        <p class="text-sm text-gray-600">Attendance and grade insights for your classes.</p>
    </div>

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
            <h2 class="text-lg font-bold text-gray-900">{{ $blockInfo['course_code'] }} - {{ $blockInfo['course_name'] }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ $blockInfo['sections'] }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                <span><i class="fas fa-clock mr-1 text-indigo-500"></i>{{ $blockInfo['schedule_string'] }}</span>
                @if($blockInfo['room_name'])
                    <span><i class="fas fa-door-open mr-1 text-indigo-500"></i>{{ $blockInfo['room_name'] }}</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Students</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $gradeStats['total_students'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Sessions Held</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $attendance['sessions'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Attendance Rate</p>
                <p class="mt-2 text-3xl font-bold {{ ($attendance['rate'] ?? 100) >= 80 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $attendance['rate'] !== null ? $attendance['rate'] . '%' : 'N/A' }}
                </p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Average Grade</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $gradeStats['average'] ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Attendance Summary</h3>
                @if($attendance['total'] > 0)
                    @php
                        $max = max($attendance['present'], $attendance['late'], $attendance['absent'], $attendance['excused'], 1);
                    @endphp
                    <div class="space-y-3">
                        @foreach([
                            ['label' => 'Present', 'value' => $attendance['present'], 'color' => 'bg-emerald-500'],
                            ['label' => 'Late', 'value' => $attendance['late'], 'color' => 'bg-amber-500'],
                            ['label' => 'Absent', 'value' => $attendance['absent'], 'color' => 'bg-red-500'],
                            ['label' => 'Excused', 'value' => $attendance['excused'], 'color' => 'bg-sky-500'],
                        ] as $item)
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-600">{{ $item['label'] }}</span>
                                    <span class="font-semibold text-gray-800">{{ $item['value'] }}</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $item['color'] }} rounded-full" style="width: {{ round(($item['value'] / $max) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No attendance records yet.</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Grade Distribution</h3>
                <div class="space-y-3">
                    @foreach($gradeStats['buckets'] as $label => $count)
                        @php
                            $pct = $gradeStats['total_students'] > 0 ? round(($count / $gradeStats['total_students']) * 100) : 0;
                            $colors = [
                                '1.0 - 1.9' => 'bg-emerald-500',
                                '2.0 - 2.9' => 'bg-sky-500',
                                '3.0 - 3.9' => 'bg-amber-500',
                                '4.0 - 5.0' => 'bg-red-500',
                                'INC / DRP' => 'bg-gray-400',
                            ];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $label }}</span>
                                <span class="font-semibold text-gray-800">{{ $count }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $colors[$label] }} rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                    <span class="text-gray-500">Grades entered</span>
                    <span class="font-semibold text-gray-800">{{ $gradeStats['graded'] }} / {{ $gradeStats['total_students'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Students Below 80% Attendance</h3>
                <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">{{ count($atRiskStudents) }}</span>
            </div>

            @if(count($atRiskStudents))
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Student</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">ID Number</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Records</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Absences</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Rate</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($atRiskStudents as $student)
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $student['name'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600">{{ $student['student_number'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600">{{ $student['total'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-red-600 font-semibold">{{ $student['absent'] }}</td>
                                    <td class="px-4 py-2.5 text-sm font-semibold text-red-600">{{ $student['rate'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-400 italic">No students below the 80% attendance threshold.</p>
            @endif
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-chart-simple text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">Select a class above to view analytics.</p>
        </div>
    @endif
</div>
