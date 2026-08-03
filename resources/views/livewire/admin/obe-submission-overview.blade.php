<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">OBE Submission Overview</h1>
            <p class="text-sm text-gray-600">
                Your pending OBE submissions and the submission status of other faculty for
                {{ \App\Services\ObeDataCompleteness::SUBMISSION_ACADEMIC_YEAR_START }} - {{ \App\Services\ObeDataCompleteness::SUBMISSION_ACADEMIC_YEAR_START + 1 }} (2nd Semester).
            </p>
        </div>
    </div>

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
                @foreach($storedSemesters as $semester)
                    <option value="{{ $semester }}">{{ $semester }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end justify-end gap-3">
            @if($stats['blocks'] > 0)
                <span class="rounded-full bg-rose-100 text-rose-700 text-xs font-bold px-3 py-1.5">
                    <i class="fas fa-hourglass-half mr-1"></i>{{ $stats['incomplete'] }} pending
                </span>
                <span class="rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1.5">
                    <i class="fas fa-check-circle mr-1"></i>{{ $stats['complete'] }} complete
                </span>
            @endif
        </div>
    </div>

    {{-- 1. What I need to submit --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 bg-indigo-600">
            <div>
                <h2 class="text-sm font-bold text-white">
                    <i class="fas fa-clipboard-list mr-2"></i>What You Need to Submit
                </h2>
                <p class="text-[11px] text-indigo-100 mt-0.5">
                    Course blocks assigned to you that are still missing OBE data.
                </p>
            </div>
            @if($myBlocks->count() > 0)
                <span class="rounded-full bg-white/20 text-white text-xs font-bold px-3 py-1.5">
                    {{ $myBlocks->filter(fn ($b) => !$b['complete'])->count() }} pending · {{ $myBlocks->filter(fn ($b) => $b['complete'])->count() }} complete
                </span>
            @endif
        </div>

        @if($myBlocks->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-circle-check text-4xl text-emerald-400 mb-3"></i>
                <p class="text-sm font-bold text-gray-700">No course blocks assigned to you.</p>
                <p class="text-xs text-gray-400 mt-1">Try a different academic year or semester.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase">Course</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase">Term</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase">Section / Schedule</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold text-gray-400 uppercase">Students</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase">Missing Data</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase">Accomplish</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @foreach($myBlocks as $block)
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
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($block['complete'])
                                        <span class="text-[10px] text-gray-400 italic">—</span>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 flex-wrap">
                                            @foreach($block['accomplish_links'] as $link)
                                                <a href="{{ $link['url'] }}" target="_blank"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 text-white px-2.5 py-1 text-[10px] font-bold hover:bg-indigo-700">
                                                    <i class="fas {{ $link['icon'] }}"></i> {{ $link['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- 2. Submission status of everyone --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 bg-gray-800">
            <div>
                <h2 class="text-sm font-bold text-white">
                    <i class="fas fa-users mr-2"></i>Submission Status by Faculty
                </h2>
                <p class="text-[11px] text-gray-300 mt-0.5">
                    Who has submitted their OBE data for the selected term.
                </p>
            </div>
        </div>

        @forelse($statusByFaculty as $faculty)
            <div class="border-b border-gray-100 last:border-b-0">
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 bg-gray-50 border-b border-gray-200">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">
                            <i class="fas fa-chalkboard-user text-gray-400 mr-2"></i>{{ $faculty['faculty_name'] ?: 'Unassigned' }}
                            @if($faculty['is_me'])
                                <span class="ml-1 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5">You</span>
                            @endif
                        </h3>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $faculty['blocks']->count() }} block(s) ·
                            <span class="{{ $faculty['incomplete'] > 0 ? 'text-rose-600' : 'text-emerald-700' }} font-bold">
                                {{ $faculty['incomplete'] > 0 ? $faculty['incomplete'] . ' pending' : 'all complete' }}
                            </span>
                            · {{ $faculty['complete'] }} complete
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full {{ $faculty['incomplete'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }} px-3 py-1 text-[10px] font-bold">
                        <i class="fas {{ $faculty['incomplete'] > 0 ? 'fa-hourglass-half' : 'fa-check' }}"></i>
                        {{ $faculty['incomplete'] > 0 ? 'Incomplete' : 'Complete' }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Course</th>
                                <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Term</th>
                                <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Section / Schedule</th>
                                <th class="px-4 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Students</th>
                                <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @foreach($faculty['blocks'] as $block)
                                <tr class="hover:bg-gray-50 transition {{ $block['complete'] ? 'opacity-70' : '' }}">
                                    <td class="px-4 py-2.5">
                                        <div class="text-xs font-bold text-gray-900">{{ $block['course_code'] }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $block['course_name'] }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-[11px] text-gray-600">{{ $block['semester'] }}</td>
                                    <td class="px-4 py-2.5 text-[11px] text-gray-600">
                                        <div>{{ $block['sections'] }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $block['schedule'] }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-xs font-bold text-gray-700">{{ $block['student_count'] }}</td>
                                    <td class="px-4 py-2.5">
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm font-bold text-gray-700">No submissions found</p>
                <p class="text-xs text-gray-400 mt-1">No course blocks match the selected filters.</p>
            </div>
        @endforelse
    </div>
</div>
