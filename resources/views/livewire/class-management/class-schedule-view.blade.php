<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Class Schedule</h1>
        <p class="text-sm text-gray-600">Your weekly class schedule for the selected term.</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
        </div>
    </div>

    @if($classCount > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Weekly Schedule</h2>
                <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800">{{ $classCount }} class(es)</span>
            </div>

            @php
                $dayColors = [
                    1 => 'bg-sky-50 border-sky-100',
                    2 => 'bg-violet-50 border-violet-100',
                    3 => 'bg-emerald-50 border-emerald-100',
                    4 => 'bg-amber-50 border-amber-100',
                    5 => 'bg-rose-50 border-rose-100',
                    6 => 'bg-slate-50 border-slate-100',
                    0 => 'bg-gray-50 border-gray-100',
                ];

                $days = [1, 2, 3, 4, 5, 6, 0];
            @endphp

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="w-32 px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 bg-gray-50 border border-gray-200">Time</th>
                            @foreach($days as $day)
                                <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600 bg-gray-50 border border-gray-200">{{ $dayLabels[$day] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timetable as $row)
                            <tr class="align-top">
                                <td class="px-3 py-2 border border-gray-200 text-xs font-semibold text-gray-600 whitespace-nowrap">
                                    {{ $row['time'] }}
                                </td>
                                @foreach($days as $day)
                                    <td class="px-2 py-2 border border-gray-200 {{ $dayColors[$day] }}">
                                        @forelse($row['days'][$day] ?? [] as $class)
                                            <div class="mb-1 p-2 rounded-lg bg-white border border-gray-100 shadow-sm">
                                                <p class="text-xs font-bold text-gray-800">{{ $class['course_code'] }} - {{ $class['course_name'] }}</p>
                                                <p class="text-[10px] text-gray-500">{{ $class['sections'] }}</p>
                                                @if($class['room_name'])
                                                    <p class="text-[10px] text-gray-600"><i class="fas fa-door-open mr-1 text-indigo-400"></i>{{ $class['room_name'] }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="text-[10px] text-gray-300 italic">-</span>
                                        @endforelse
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-calendar-days text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">No classes assigned for the selected term.</p>
        </div>
    @endif
</div>
