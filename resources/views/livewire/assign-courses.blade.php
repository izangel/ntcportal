<div class="p-6 bg-white shadow-xl rounded-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">📚 Assign Students to Courses</h2>

    @if (session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-end">
        <div>
            <label for="academic_year" class="block text-sm font-medium text-gray-700">Academic Year</label>
            <select wire:model.live="selectedAcademicYearId" id="academic_year" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Select Year</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->start_year }}-{{ $year->end_year }}</option>
                @endforeach
            </select>
            @error('selectedAcademicYearId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        
        <div>
            <label for="semester" class="block text-sm font-medium text-gray-700">Semester</label>
            <select 
                wire:model.live="selectedSemesterId" 
                id="semesterId" 
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                {{-- 🚨 FIX 1: Disabled if Academic Year ID is not set 🚨 --}}
                {{ $selectedAcademicYearId ? '' : 'disabled' }}
            >
                <option value="">Select Semester</option>
                @foreach ($semesters as $semester)
                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                @endforeach
            </select>
            @error('selectedSemesterId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
            <select 
                wire:model.live="selectedSectionId" 
                id="sectionId" 
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                {{-- 🚨 FIX 2: Disabled if Semester ID is not set 🚨 --}}
                {{ $selectedSemesterId ? '' : 'disabled' }}
            >
                <option value="">Select Section</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>
            @error('selectedSectionId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <button wire:click="viewCourses" 
                    wire:loading.attr="disabled"
                    @if(!$selectedAcademicYearId || !$selectedSemesterId || !$selectedSectionId) disabled @endif
                    class="w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove>View Courses</span>
                <span wire:loading>Loading Courses...</span>
            </button>
        </div>
    </div>

    @if (!empty($students))
    <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">
            📚 Students in Selected Section ({{ $students->count() }})
        </h3>
        
        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                        
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($students as $student)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->mid_name }}</td>
                            
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="mt-8 p-4 text-center text-gray-500 bg-gray-50 border rounded-lg">
            Select a valid Academic Year, Semester, and Section to view students.
        </div>
    @endif

    @if(!empty($courses))
        <hr class="my-6">
        <h3 class="text-xl font-semibold mb-4 text-gray-700">Courses for Section: {{ $sections->find($selectedSectionId)->program->name ?? 'N/A' }}-{{ $sections->find($selectedSectionId)->name ?? 'N/A' }}</h3>
        
        <div class="flex justify-between items-center mb-4">
            <p class="text-sm text-gray-600">Total Courses: {{ count($courses) }}</p>
            <button wire:click="assignStudentsToCourses"
                    wire:confirm="Are you sure you want to assign these {{ count($courses) }} courses to ALL students in this section? This action cannot be undone."
                    wire:loading.attr="disabled"
                    class="rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove>ASSIGN (Bulk Enrollment)</span>
                <span wire:loading>Assigning...</span>
            </button>
        </div>

        <div class="overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Course Code</th>
                        <th scope="col" class="px-6 py-3">Course Name</th>
                        <th scope="col" class="px-6 py-3">Units</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $course->code }}</td>
                            <td class="px-6 py-4">{{ $course->name }}</td>
                            <td class="px-6 py-4">{{ $course->units }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>