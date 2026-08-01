<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Institutional Analytics</h1>
            <p class="text-sm text-gray-600">Institution-wide class, attendance, and grade insights.</p>
        </div>
        @if($summary)
            <button wire:click="exportExcel" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700">
                <i class="fas fa-file-excel"></i>Export Report
            </button>
        @endif
    </div>

    @if(session()->has('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-circle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <label class="block text-sm font-semibold text-gray-700 mb-2">Program</label>
                <select wire:model.live="programId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Programs</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Faculty</label>
                <select wire:model.live="facultyId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Faculty</option>
                    @foreach($faculties as $faculty)
                        <option value="{{ $faculty->id }}">{{ trim($faculty->last_name . ', ' . $faculty->first_name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($summary)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Classes</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['classes'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Faculty</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['faculty'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Students</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['students'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Sessions</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['sessions'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Attendance Rate</p>
                <p class="mt-2 text-3xl font-bold {{ ($summary['rate'] ?? 100) >= 80 ? 'text-emerald-600' : 'text-red-600' }}">{{ $summary['rate'] ?? 'N/A' }}{{ $summary['rate'] !== null ? '%' : '' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Grades Entered</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['grades_entered'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Analytics by Program</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Program</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Classes</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Students</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Attendance</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Grades In</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($byProgram as $item)
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $item['program'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['classes'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['students'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center {{ ($item['rate'] ?? 100) >= 80 ? 'text-emerald-600 font-semibold' : 'text-red-600 font-semibold' }}">{{ $item['rate'] ?? 'N/A' }}{{ $item['rate'] !== null ? '%' : '' }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['grades_entered'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Grade Distribution</h3>
                <div class="space-y-3">
                    @foreach($gradeDistribution['buckets'] as $label => $count)
                        @php
                            $pct = $summary['grades_entered'] > 0 ? round(($count / $summary['grades_entered']) * 100) : 0;
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
                                <span class="font-semibold text-gray-800">{{ $count }}</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $colors[$label] }} rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                    <span class="text-gray-500">Average grade</span>
                    <span class="font-bold text-gray-800">{{ $gradeDistribution['average'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Analytics by Faculty</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Faculty</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Classes</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Students</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Attendance</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Grades In</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($byFaculty as $item)
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $item['faculty'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['classes'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['students'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center {{ ($item['rate'] ?? 100) >= 80 ? 'text-emerald-600 font-semibold' : 'text-red-600 font-semibold' }}">{{ $item['rate'] ?? 'N/A' }}{{ $item['rate'] !== null ? '%' : '' }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['grades_entered'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Students Below 80% Attendance</h3>
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">{{ count($atRiskStudents) }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Student</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">ID Number</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Absences</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Rate</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($atRiskStudents as $student)
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $student['name'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600">{{ $student['student_number'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-red-600 font-semibold">{{ $student['absent'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-red-600 font-semibold">{{ $student['rate'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-sm text-gray-400 italic text-center">No students below the 80% threshold.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-chart-simple text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">No class data available for the selected period.</p>
        </div>
    @endif
</div>
