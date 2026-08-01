<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Course Materials</h1>
        <p class="text-sm text-gray-600">Links to your LMS, course pack and syllabus shared by your instructors.</p>
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

    @if(count($blocks) === 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">No course materials shared for this term yet.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($blocks as $block)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                        <h2 class="text-base font-bold text-gray-800">
                            {{ $block['course_code'] }} - {{ $block['course_name'] }}
                        </h2>
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700">
                            {{ count($block['materials']) }} link(s)
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">
                        <i class="fas fa-user-tie mr-1"></i> {{ $block['faculty_name'] }}
                        @if($block['schedule_string'])
                            <span class="mx-1 text-gray-300">|</span>
                            <i class="fas fa-clock mr-1"></i> {{ $block['schedule_string'] }}
                        @endif
                        @if($block['room_name'])
                            <span class="mx-1 text-gray-300">|</span>
                            <i class="fas fa-door-open mr-1"></i> {{ $block['room_name'] }}
                        @endif
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($block['materials'] as $material)
                            <a href="{{ $material['url'] }}" target="_blank" rel="noopener"
                               class="group flex items-start gap-3 p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/40 transition">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 shrink-0">
                                    <i class="fas {{ $material['type_icon'] }}"></i>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-indigo-500">{{ $material['type_label'] }}</span>
                                    <span class="block text-sm font-semibold text-gray-800 group-hover:text-indigo-700 truncate">{{ $material['title'] }}</span>
                                    <span class="inline-flex items-center text-[11px] text-indigo-600 mt-1">
                                        Open link <i class="fas fa-arrow-up-right-from-square ml-1 text-[10px]"></i>
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
