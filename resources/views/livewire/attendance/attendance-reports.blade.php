<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Attendance Reports</h1>
        <p class="text-sm text-gray-600">Generate a multi-date attendance sheet or a per-student summary for any of your classes.</p>
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

    @if($selectedBlockId)
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-2">
                    <button wire:click="$set('reportType', 'sheet')" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg transition {{ $reportType === 'sheet' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <i class="fas fa-table mr-2"></i> Attendance Sheet
                    </button>
                    <button wire:click="$set('reportType', 'summary')" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg transition {{ $reportType === 'summary' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <i class="fas fa-users mr-2"></i> Per-Student Summary
                    </button>
                </div>

                @if($reportType === 'summary' && $generated)
                    <button wire:click="exportSummary" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg hover:bg-emerald-100">
                        <i class="fas fa-file-excel mr-1.5"></i> Export Excel
                    </button>
                @endif
            </div>

            @if($reportType === 'sheet')
                <div class="flex flex-wrap items-end gap-3 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">From</label>
                        <input type="date" wire:model.live="dateFrom" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">To</label>
                        <input type="date" wire:model.live="dateTo" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button wire:click="generate" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-wand-magic-sparkles mr-2"></i> Generate
                    </button>
                    @if($generated && count($sheetDates))
                        <button wire:click="exportSheet" class="inline-flex items-center px-3 py-2 bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-lg hover:bg-emerald-100">
                            <i class="fas fa-file-excel mr-1.5"></i> Export Excel
                        </button>
                    @endif
                </div>

                @if($generated && count($sheetDates))
                    <p class="text-xs text-gray-500 mb-3">
                        {{ count($sheetDates) }} session date(s) — scheduled meeting days within the range, plus any dates with recorded attendance. Rows with an attendance rate below 80% are highlighted.
                    </p>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-indigo-600">
                                    <th class="px-3 py-2 text-left text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap">#</th>
                                    <th class="px-3 py-2 text-left text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap">Student</th>
                                    <th class="px-3 py-2 text-left text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap">ID Number</th>
                                    @foreach($sheetDates as $date)
                                        <th class="px-2 py-2 text-center text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($date)->format('n/j') }}
                                        </th>
                                    @endforeach
                                    <th class="px-2 py-2 text-center text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap">P</th>
                                    <th class="px-2 py-2 text-center text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap">L</th>
                                    <th class="px-2 py-2 text-center text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap">A</th>
                                    <th class="px-2 py-2 text-center text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap">E</th>
                                    <th class="px-3 py-2 text-center text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap">Rate %</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @php
                                    $statusBadge = [
                                        'present' => 'bg-emerald-100 text-emerald-800',
                                        'late' => 'bg-amber-100 text-amber-800',
                                        'absent' => 'bg-rose-100 text-rose-800',
                                        'excused' => 'bg-gray-200 text-gray-700',
                                    ];
                                    $statusLetter = [
                                        'present' => 'P',
                                        'late' => 'L',
                                        'absent' => 'A',
                                        'excused' => 'E',
                                    ];
                                @endphp
                                @foreach($sheetRows as $index => $student)
                                    <tr class="{{ ($student['rate'] ?? 100) < 80 ? 'bg-amber-50' : '' }}">
                                        <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2 text-xs font-medium text-gray-800 whitespace-nowrap">{{ $student['name'] }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap">{{ $student['student_number'] }}</td>
                                        @foreach($sheetDates as $date)
                                            @php $status = $student['per_date'][$date] ?? null; @endphp
                                            <td class="px-2 py-2 text-center whitespace-nowrap">
                                                @if($status)
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold {{ $statusBadge[$status] ?? 'bg-gray-100 text-gray-600' }}">{{ $statusLetter[$status] ?? '?' }}</span>
                                                @else
                                                    <span class="text-gray-300">·</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-2 py-2 text-center text-xs font-bold text-emerald-700 whitespace-nowrap">{{ $student['present'] }}</td>
                                        <td class="px-2 py-2 text-center text-xs font-bold text-amber-700 whitespace-nowrap">{{ $student['late'] }}</td>
                                        <td class="px-2 py-2 text-center text-xs font-bold text-rose-700 whitespace-nowrap">{{ $student['absent'] }}</td>
                                        <td class="px-2 py-2 text-center text-xs font-bold text-gray-600 whitespace-nowrap">{{ $student['excused'] }}</td>
                                        <td class="px-3 py-2 text-center text-xs font-bold whitespace-nowrap {{ ($student['rate'] ?? 100) < 80 ? 'text-rose-700' : 'text-gray-800' }}">
                                            {{ $student['rate'] ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif($generated)
                    <p class="text-sm text-gray-400 italic py-8 text-center">No scheduled meeting days or attendance records found in the selected range.</p>
                @endif
            @else
                <div class="flex items-center gap-3 mb-5">
                    <button wire:click="generate" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-wand-magic-sparkles mr-2"></i> Generate Summary
                    </button>
                </div>

                @if($generated)
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800">Students: {{ count($summaryRows) }}</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">Sessions recorded: {{ $summaryStats['sessions'] ?? 0 }}</span>
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Below 80%: {{ $summaryStats['below_threshold'] ?? 0 }}</span>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">ID Number</th>
                                    <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">Present</th>
                                    <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">Late</th>
                                    <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">Absent</th>
                                    <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">Excused</th>
                                    <th class="px-4 py-2.5 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">Attendance Rate %</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($summaryRows as $index => $student)
                                    <tr class="{{ ($student['rate'] ?? 100) < 80 ? 'bg-amber-50' : '' }}">
                                        <td class="px-4 py-2 text-xs text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-xs font-medium text-gray-800">{{ $student['name'] }}</td>
                                        <td class="px-4 py-2 text-xs text-gray-600">{{ $student['student_number'] }}</td>
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
            @endif
        </div>
    @else
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-chart-column text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">Select a class above to generate reports.</p>
        </div>
    @endif
</div>
