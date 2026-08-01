<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Attendance</h1>
        <p class="text-sm text-gray-600">View your attendance records per subject for the selected school year and semester.</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">School Year</label>
                <select wire:model.live="selectedAcademicYear" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}">{{ $academicYear->start_year }} - {{ $academicYear->end_year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                <select wire:model.live="selectedSemester" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($semesterOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-emerald-50 rounded-xl border border-emerald-100 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-700">{{ $overall['present'] }}</p>
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">Present</p>
        </div>
        <div class="bg-amber-50 rounded-xl border border-amber-100 p-4 text-center">
            <p class="text-2xl font-bold text-amber-700">{{ $overall['late'] }}</p>
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Late</p>
        </div>
        <div class="bg-rose-50 rounded-xl border border-rose-100 p-4 text-center">
            <p class="text-2xl font-bold text-rose-700">{{ $overall['absent'] }}</p>
            <p class="text-xs font-semibold text-rose-600 uppercase tracking-wide">Absent</p>
        </div>
        <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-gray-600">{{ $overall['excused'] }}</p>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Excused</p>
        </div>
        <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-4 text-center">
            <p class="text-2xl font-bold text-indigo-700">{{ $overall['rate'] !== null ? $overall['rate'] . '%' : '—' }}</p>
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">Attendance Rate</p>
        </div>
    </div>

    @if($blocks->isEmpty())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-calendar-check text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">No attendance records for this school year and semester yet.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($blocks as $block)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-11 h-11 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-sm">{{ $block['course_code'] }}</span>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $block['course_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $block['faculty_name'] }} | {{ $block['schedule_string'] ?: 'No schedule' }} | {{ $block['room_name'] ?: 'Room: TBA' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold {{ $block['stats']['rate'] !== null && $block['stats']['rate'] >= 70 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $block['stats']['rate'] !== null ? $block['stats']['rate'] . '%' : '—' }}
                            </span>
                            <i class="fas fa-chevron-down text-gray-400 transition transform" :class="{'rotate-180': open}"></i>
                        </div>
                    </button>

                    <div x-show="open" x-collapse>
                        <div class="border-t border-gray-100 px-5 py-4">
                            @if(count($block['records']) === 0)
                                <p class="text-xs text-gray-400 italic">No attendance records for this subject yet.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr class="bg-gray-50">
                                                <th class="px-4 py-2 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-4 py-2 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-4 py-2 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Check-in Time</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100">
                                            @foreach($block['records'] as $record)
                                                <tr>
                                                    <td class="px-4 py-2 text-xs text-gray-700">{{ \Carbon\Carbon::parse($record['date'])->format('M d, Y (D)') }}</td>
                                                    <td class="px-4 py-2">
                                                        @php
                                                            $badge = match($record['status']) {
                                                                'present' => 'bg-emerald-100 text-emerald-800',
                                                                'late' => 'bg-amber-100 text-amber-800',
                                                                'absent' => 'bg-rose-100 text-rose-800',
                                                                'excused' => 'bg-gray-100 text-gray-700',
                                                                default => 'bg-gray-100 text-gray-700',
                                                            };
                                                        @endphp
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase {{ $badge }}">{{ $record['status'] }}</span>
                                                    </td>
                                                    <td class="px-4 py-2 text-xs text-gray-600">{{ $record['checked_in_at'] ? \Carbon\Carbon::parse($record['checked_in_at'])->format('h:i A') : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
