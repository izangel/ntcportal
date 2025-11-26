<div class="p-6 bg-white shadow-xl rounded-lg">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Assigned Courses List</h2>
            <p class="text-sm text-gray-500">Manage student course assignments</p>
        </div>
        <div>
            <a href="{{ route('assign.courses') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                <i class="fas fa-plus mr-2"></i> Assign New
            </a>
            <button wire:click="toggleIrregularForm" class="ml-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded shadow">
                <i class="fas fa-user-plus mr-2"></i> Assign Irregular
            </button>
        </div>
    </div>

    @if($showIrregularForm)
        <div class="mb-6">
            <livewire:assign-irregular-student />
        </div>
    @endif

    @if (session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700">Search Student</label>
            <input wire:model.live.debounce.300ms="search" type="text" id="search" placeholder="Name or ID..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label for="academic_year" class="block text-sm font-medium text-gray-700">Academic Year</label>
            <select wire:model.live="selectedAcademicYear" id="academic_year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">All Years</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->start_year }}-{{ $year->end_year }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="semester" class="block text-sm font-medium text-gray-700">Semester</label>
            <select wire:model.live="selectedSemester" id="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">All Semesters</option>
                <option value="1st">1st Semester</option>
                <option value="2nd">2nd Semester</option>
                <option value="Sum">Summer</option>
            </select>
        </div>

        <div>
            <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
            <select wire:model.live="selectedSection" id="section" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->program->name ?? '' }} - {{ $section->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto shadow-md sm:rounded-lg mb-4">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="p-4">
                        <div class="flex items-center">
                            <input wire:model.live="selectAll" id="checkbox-all" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="checkbox-all" class="sr-only">checkbox</label>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3">Acad Year</th>
                    <th scope="col" class="px-6 py-3">Semester</th>
                    <th scope="col" class="px-6 py-3">Student ID</th>
                    <th scope="col" class="px-6 py-3">Course ID</th>
                    <th scope="col" class="px-6 py-3">Validated</th>
                    <th scope="col" class="px-6 py-3">Validated By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignedCourses as $assignment)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="w-4 p-4">
                            <div class="flex items-center">
                                <input wire:model.live="selectedAssignments" value="{{ $assignment->id }}" id="checkbox-table-{{ $assignment->id }}" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                <label for="checkbox-table-{{ $assignment->id }}" class="sr-only">checkbox</label>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            {{ $assignment->acadYear->start_year ?? '' }}-{{ $assignment->acadYear->end_year ?? '' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $assignment->semester }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $assignment->student->student_id }}</div>
                            <div class="text-xs text-gray-500">{{ $assignment->student->last_name }}, {{ $assignment->student->first_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $assignment->course->code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($assignment->validated)
                                <span class="text-green-600 font-bold">Yes</span>
                            @else
                                <span class="text-gray-400">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{ $assignment->validatedby->name ?? '' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No assignments found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bulk Actions -->
    <div class="flex items-center justify-between">
        <div>
            @if(count($selectedAssignments) > 0)
                <span class="text-sm text-gray-600">{{ count($selectedAssignments) }} selected</span>
            @endif
        </div>
        <button wire:click="validateSelected" 
                wire:loading.attr="disabled"
                @if(empty($selectedAssignments)) disabled @endif
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-check mr-2"></i> Validate
        </button>
    </div>

    <div class="mt-4">
        {{ $assignedCourses->links() }}
    </div>
</div>
