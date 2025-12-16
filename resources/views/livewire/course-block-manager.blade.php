<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">🗓️ Course Block & Enrollment Manager</h2>

    {{-- --- CONTEXT SELECTION BLOCK --- --}}
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-indigo-500">
        <h3 class="text-xl font-semibold mb-4 text-gray-700">Select Context</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div>
                <label for="ay" class="block text-sm font-medium text-gray-700">Academic Year</label>
                <select id="ay" wire:model.live="academicYearId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select AY</option>
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }} - {{ $ay->end_year }}</option>
                    @endforeach
                </select>
                @error('academicYearId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="sem" class="block text-sm font-medium text-gray-700">Semester</label>
                <select id="sem" wire:model.live="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Semester</option>
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }}</option>
                    @endforeach
                </select>
                @error('semester') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
                <select id="section" wire:model.live="sectionId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Section</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section['id'] }}">
                            {{ $section['program_name'] }}-{{ $section['name'] }}
                        </option>
                    @endforeach
                </select>
                @error('sectionId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- --- MESSAGES --- --}}
    @if (session()->has('error') && !str_contains(session('error'), 'required'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    
    @if (session()->has('message'))
        @php
            $isEnrollmentMessage = str_contains(session('message'), 'enrollment') || str_contains(session('message'), 'enrolled') || str_contains(session('message'), 'All students are already enrolled');
            $alertClass = $isEnrollmentMessage ? 'bg-green-100 border-green-500 text-green-700' : 'bg-blue-100 border-blue-500 text-blue-700';
        @endphp
        <div class="border-l-4 {{ $alertClass }} p-4 mb-4 rounded" role="alert">
            <p>{!! session('message') !!}</p>
        </div>
    @endif

    {{-- --- MAIN CONTENT (Conditional on Context Selection) --- --}}
    @if ($academicYearId && $semester && $sectionId)
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            {{-- 1. Student List for Section & Add Student Form (UPDATED COLUMN) --}}
            <div class="lg:col-span-1 h-fit">
                
                {{-- Student List Panel (Existing Code) --}}
                <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-emerald-500 h-fit">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700 flex justify-between items-center">
                        👥 Students in Section 
                        @if ($selectedSection)
                            <span class="text-sm font-medium text-emerald-600 bg-emerald-100 px-3 py-1 rounded-full">
                                {{ $selectedSection->name }}
                            </span>
                        @endif
                    </h3>

                    @if ($students->isNotEmpty())
                        <p class="text-sm text-gray-500 mb-3">Total Students: **{{ $students->count() }}**</p>
                        <div class="max-h-96 overflow-y-auto border p-3 rounded-md bg-white">
                            <ul class="divide-y divide-gray-100">
                                @foreach ($students as $student)
                                    <li class="py-2 text-sm text-gray-800">
                                        {{ $student->last_name }}, {{ $student->first_name }} {{ $student->mid_name }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic p-3 border rounded">
                            No students are currently enrolled in this Section/AY/Semester context.
                        </p>
                    @endif
                </div>

                {{-- ADD NEW STUDENT TO SECTION FORM (REVISED FEATURE: Dropdown Select) --}}
                <div class="bg-white shadow-lg rounded-xl p-6 border-t-4 border-yellow-500 h-fit">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">✍️ Add Student to Section</h3>
                    
                    @if ($availableStudentsForAdd->isNotEmpty())
                        <form wire:submit.prevent="addStudentToSection">
                            
                            {{-- 1. Student Dropdown --}}
                            <div class="mb-4">
                                <label for="student-select" class="block text-sm font-medium text-gray-700">Select Student to Add</label>
                            <select 
                                id="student-select" 
                                wire:model.live="selectedStudentId" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                    <option value="">-- Select a Student --</option>
                                    @foreach ($availableStudentsForAdd->sortBy('last_name') as $student)
                                        <option value="{{ $student->id }}">
                                            {{ $student->last_name }}, {{ $student->first_name }} (ID: {{ $student->id ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedStudentId') 
                                    <span class="text-red-500 text-sm block mt-2">
                                        {{ $message }}
                                    </span> 
                                @enderror
                            </div>

                            {{-- 2. Add Button --}}
                            <div class="flex justify-end">
                                <button 
                                    type="submit" 
                                    {{-- The controller validation handles the required check, but we can visually disable it too --}}
                                    @if (!$selectedStudentId) disabled @endif
                                    class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white transition duration-150 ease-in-out 
                                        @if ($selectedStudentId) bg-yellow-600 hover:bg-yellow-700 @else bg-gray-400 cursor-not-allowed @endif"
                                >
                                    Add Student to Section
                                </button>
                            </div>
                        </form>
                    @else
                        <p class="text-sm text-gray-500 italic p-3 border rounded">
                            All students are currently associated with this section context, or there are no students in the database.
                        </p>
                    @endif
                </div>
            </div> {{-- End Student List Column --}}

            {{-- 2. Course Blocks List & Mass Enrollment Action --}}
            <div class="lg:col-span-2">
                <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-gray-500">
                    <h3 class="text-xl font-semibold mb-4">📚 Existing Course Blocks ({{ $courseBlocks->count() }})</h3>
                    
                    {{-- Mass Enrollment Button (Placed prominently above the list) --}}
                    @if ($courseBlocks->isNotEmpty() && $students->isNotEmpty())
                        <div class="mb-4 pb-4 border-b border-gray-200 flex justify-start">
                            <button 
                                wire:click="enrollAllSectionStudents"
                                wire:confirm="Are you sure you want to enroll ALL {{ $students->count() }} students into ALL {{ $courseBlocks->count() }} course blocks? This will only add missing enrollments and may take a moment."
                                class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out">
                                🚀 Enroll All Students to All Blocks
                            </button>
                        </div>
                    @else
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-red-500">
                                Cannot perform mass enrollment: Must have at least one **Student** and one **Course Block** defined.
                            </p>
                        </div>
                    @endif

                    @if ($courseBlocks->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($courseBlocks as $block)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <p class="text-sm font-medium text-gray-900">{{ $block->course->code }}</p>
                                                <p class="text-xs text-gray-500">{{ $block->course->name }}</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $block->faculty->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{ $block->schedule_string }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $block->room_name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-gray-500 italic p-3 border rounded">
                            No course blocks defined yet for this Academic Period and Section.
                        </p>
                    @endif
                </div>
            </div> {{-- End Course Blocks Column --}}
        </div> {{-- End Grid Container --}}

        {{-- --- CREATE NEW COURSE BLOCK SECTION --- --}}
        <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-purple-500">
            <h3 class="text-xl font-semibold mb-4">➕ Create New Course Block</h3>
            
            <form wire:submit.prevent="saveCourseBlock">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Course</label>
                        <select wire:model.defer="newCourseBlock.course_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Select Course</option>
                            @foreach ($allCourses as $course)
                                <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }} ({{ $course->description }})</option>
                            @endforeach
                        </select>
                        @error('newCourseBlock.course_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Faculty</label>
                        <select wire:model.defer="newCourseBlock.faculty_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Select Faculty</option>
                            @foreach ($allFaculty as $faculty)
                                <option value="{{ $faculty->id }}">{{ $faculty->last_name }}, {{ $faculty->first_name }} {{ $faculty->mid_name }}</option>
                            @endforeach
                        </select>
                        @error('newCourseBlock.faculty_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Room Name</label>
                        <input type="text" wire:model.defer="newCourseBlock.room_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="e.g., L201, Online">
                        @error('newCourseBlock.room_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Schedule String</label>
                        <input type="text" wire:model.defer="newCourseBlock.schedule_string" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="e.g., MW 10:00-11:30AM">
                        @error('newCourseBlock.schedule_string') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="mt-6 text-right flex justify-end items-center space-x-4">
                    
                    <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                        Create Course Block
                    </button>
                </div>
            </form>
        </div>
        
    @else
        <p class="text-center text-gray-500 mt-10 p-6 bg-white shadow-lg rounded-xl">
            Please select an **Academic Year**, **Semester**, and **Section** above to view, create, and manage enrollments.
        </p>
    @endif
</div>