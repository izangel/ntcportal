<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">OBE Data Completion Reminders</h1>
            <p class="text-sm text-gray-600">
                @if($isAdminView)
                    Review course blocks with missing OBE data and notify faculty to complete assessments, scores and CLO attainment.
                @else
                    Your course blocks that still need assessment setup, scores or CLO attainment.
                @endif
            </p>
        </div>

        @if($isAdminView)
            <button type="button" wire:click="sendAllReminders"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <i class="fas fa-bell"></i>
                Remind All Incomplete
            </button>
        @endif
    </div>

    @if(session('obe-reminder-message'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            <i class="fas fa-check-circle mr-2"></i>{{ session('obe-reminder-message') }}
        </div>
    @endif

    @if(session('obe-reminder-error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('obe-reminder-error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Academic Year</label>
            <select wire:model.live="selectedAcademicYearId" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- All Years --</option>
                @foreach($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}">{{ $academicYear->start_year }} - {{ $academicYear->end_year }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Semester</label>
            <select wire:model.live="selectedSemester" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- All Semesters --</option>
                @foreach($semesters as $semester)
                    <option value="{{ $semester }}">{{ $semester }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end justify-end gap-3">
            @if($stats['blocks'] > 0)
                <span class="rounded-full bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1.5">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $stats['incomplete'] }} / {{ $stats['blocks'] }} incomplete
                </span>
                <span class="rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1.5">
                    <i class="fas fa-check-circle mr-1"></i>{{ $stats['complete'] }} complete
                </span>
            @endif
        </div>
    </div>

    {{-- Faculty / Blocks --}}
    @forelse($blocksByFaculty as $faculty)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 bg-gray-50 border-b border-gray-200">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">
                        <i class="fas fa-chalkboard-user text-gray-400 mr-2"></i>{{ $faculty['faculty_name'] ?: 'Unassigned' }}
                    </h3>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $faculty['blocks']->count() }} block(s) ·
                        @if($faculty['incomplete'] > 0)
                            <span class="text-amber-700 font-bold">{{ $faculty['incomplete'] }} incomplete</span>
                        @else
                            <span class="text-emerald-700 font-bold">all complete</span>
                        @endif
                    </p>
                </div>
                @if($isAdminView && $faculty['incomplete'] > 0)
                    <button type="button" wire:click="sendRemindersForFaculty({{ $faculty['faculty_id'] }})"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-600 text-indigo-600 px-3 py-1.5 text-xs font-bold hover:bg-indigo-50 focus:outline-none">
                        <i class="fas fa-bell"></i>
                        Remind Faculty
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase">Course</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase">Term</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase">Section / Schedule</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold text-gray-400 uppercase">Students</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase">Missing Data</th>
                            <th class="px-4 py-2.5 text-right text-[10px] font-bold text-gray-400 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @foreach($faculty['blocks'] as $block)
                            <tr class="hover:bg-gray-50 transition {{ $block['complete'] ? 'opacity-70' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="text-xs font-bold text-gray-900">{{ $block['course_code'] }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $block['course_name'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-[11px] text-gray-600">{{ $block['semester'] }}</td>
                                <td class="px-4 py-3 text-[11px] text-gray-600">
                                    <div>{{ $block['sections'] }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $block['schedule'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-center text-xs font-bold text-gray-700">{{ $block['student_count'] }}</td>
                                <td class="px-4 py-3">
                                    @if($block['complete'])
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-[10px] font-bold">
                                            <i class="fas fa-check"></i> Complete
                                        </span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($block['missing_labels'] as $label)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-700 px-2 py-0.5 text-[10px] font-bold">
                                                    <i class="fas fa-exclamation-circle"></i>{{ $label }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if($block['complete'])
                                        <span class="text-[10px] text-gray-400 italic">—</span>
                                    @else
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ $block['action_url'] }}" target="_blank"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-300 text-gray-600 px-2.5 py-1 text-[10px] font-bold hover:bg-gray-50">
                                                <i class="fas fa-arrow-up-right-from-square"></i> Go
                                            </a>
                                            @if($isAdminView)
                                                <button type="button" wire:click="sendReminder({{ $block['id'] }})"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 text-white px-2.5 py-1 text-[10px] font-bold hover:bg-indigo-700">
                                                    <i class="fas fa-bell"></i> Remind
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="bg-white border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center text-gray-500 space-y-3">
            <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-sm font-bold text-gray-700">No Course Blocks Found</h3>
            <p class="text-xs text-gray-400 max-w-sm mx-auto">
                No course blocks match the selected filters{{ $isAdminView ? '' : ' for your assigned load' }}.
                Try changing the academic year or semester.
            </p>
        </div>
    @endforelse
</div>
