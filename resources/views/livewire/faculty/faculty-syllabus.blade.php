<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Course Syllabus</h1>
            <p class="text-sm text-gray-600">Select one of your assigned course blocks to prepare or update its syllabus.</p>
        </div>
        <a href="{{ route('faculty.syllabus.help') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50">
            <i class="fas fa-circle-question text-indigo-600"></i> User Manual
        </a>
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
                    <option value="1st">1st Semester</option>
                    <option value="2nd">2nd Semester</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
        </div>
    </div>

    @forelse($assignedBlocks as $block)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-gray-900">{{ $block['course_code'] }}</span>
                    <span class="text-sm text-gray-600">{{ $block['course_name'] }}</span>
                    <span class="text-xs text-gray-400">({{ $block['course_units'] }} units)</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $block['sections'] }} • {{ $block['schedule_string'] }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @foreach($block['programs'] as $program)
                    <div class="flex items-center gap-2">
                        @if($program['has_syllabus'])
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                <i class="fas fa-circle-check mr-1"></i>{{ $program['name'] }}
                                {{ $program['has_learning_plan'] ? 'Prepared' : 'Incomplete' }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                <i class="fas fa-pen mr-1"></i>{{ $program['name'] }} Not yet prepared
                            </span>
                        @endif
                        <a href="{{ route('faculty.syllabus.edit', [$block['id'], $program['id']]) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700">
                            <i class="fas fa-file-lines"></i>{{ $program['has_syllabus'] ? 'Edit' : 'Prepare' }} Syllabus
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-book-open text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">No course blocks assigned to you for the selected period.</p>
        </div>
    @endforelse
</div>
