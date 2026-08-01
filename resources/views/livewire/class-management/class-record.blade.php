<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Class Record / Grade Entry</h1>
            <p class="text-sm text-gray-600">Enter and manage grades for your classes.</p>
        </div>
        @if($blockInfo)
            <div class="flex items-center gap-2">
                @if($finalized)
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                        <i class="fas fa-lock mr-1"></i>Finalized
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                        <i class="fas fa-pen mr-1"></i>Draft
                    </span>
                @endif
            </div>
        @endif
    </div>

    @if(session()->has('message'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-circle-check mr-2"></i>{{ session('message') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-circle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif

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
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $blockInfo['course_code'] }} - {{ $blockInfo['course_name'] }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $blockInfo['sections'] }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                        <span><i class="fas fa-clock mr-1 text-indigo-500"></i>{{ $blockInfo['schedule_string'] }}</span>
                        @if($blockInfo['room_name'])
                            <span><i class="fas fa-door-open mr-1 text-indigo-500"></i>{{ $blockInfo['room_name'] }}</span>
                        @endif
                        <span><i class="fas fa-users mr-1 text-indigo-500"></i>{{ count($students) }} student(s)</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="exportExcel" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-file-excel text-green-600"></i>Export Class Record
                    </button>
                    @if($finalized)
                        <button wire:click="unfinalizeRecord" wire:confirm="Unlock this class record for editing?" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-semibold hover:bg-amber-700">
                            <i class="fas fa-lock-open"></i>Unlock
                        </button>
                    @else
                        <button wire:click="finalizeRecord" wire:confirm="Finalize this class record? Grades can no longer be edited." class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
                            <i class="fas fa-lock"></i>Finalize
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @if($finalized)
                <div class="bg-amber-50 border-b border-amber-200 px-5 py-3 text-sm text-amber-800 flex items-center gap-2">
                    <i class="fas fa-lock"></i>This class record is finalized. Grades are locked.
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 w-10">#</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Student ID</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Student Name</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Section</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 w-32">Final Grade</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($students as $index => $student)
                            <tr>
                                <td class="px-4 py-2.5 text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-700">{{ $student['student_number'] }}</td>
                                <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $student['name'] }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-600">{{ $student['section'] }}</td>
                                <td class="px-4 py-2.5">
                                    <select wire:model="grades.{{ $student['id'] }}" :disabled="{{ $finalized ? 'true' : 'false' }}" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $finalized ? 'bg-gray-100' : '' }}">
                                        <option value="">--</option>
                                        @foreach($gradeOptions as $grade)
                                            <option value="{{ $grade }}">{{ $grade }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2.5">
                                    <input type="text" wire:model="remarks.{{ $student['id'] }}" maxlength="255" placeholder="Remarks" :disabled="{{ $finalized ? 'true' : 'false' }}" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $finalized ? 'bg-gray-100' : '' }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-end">
                <button wire:click="saveGrades" wire:loading.attr="disabled" :disabled="{{ $finalized ? 'true' : 'false' }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 disabled:opacity-50">
                    <i class="fas fa-save"></i>
                    <span wire:loading.remove wire:target="saveGrades">Save Class Record</span>
                    <span wire:loading wire:target="saveGrades">Saving...</span>
                </button>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">Select a class above to start entering grades.</p>
        </div>
    @endif
</div>
