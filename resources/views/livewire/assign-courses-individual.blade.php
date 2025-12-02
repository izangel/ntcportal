<div class="space-y-6">
    <h2 class="text-2xl font-bold">Individual Course Assignment (Irregular)</h2>
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
    
    <div class="grid grid-cols-3 gap-4">
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
            <select wire:model.live="selectedSemesterId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" {{ $selectedAcademicYearId ? '' : 'disabled' }}>
                <option value="">Select Semester</option>
                @foreach ($semesters as $semester)
                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="selectedStudentId" class="block text-sm font-medium text-gray-700">Student Name</label>
            <select wire:model.live="selectedStudentId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" {{ $selectedSemesterId ? '' : 'disabled' }}>
                <option value="">Select Student</option>
                @foreach ($allStudents as $student)
                    <option value="{{ $student->id }}">{{ $student->last_name }},{{ $student->first_name }} {{ $student->mid_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if (!empty($studentCourses) || !empty($availableCourses))
        <div class="grid grid-cols-2 gap-6 mt-6">
            
            {{-- Assigned Courses Div is unchanged --}}
            <div class="border p-4 rounded-lg">
                <h4 class="font-semibold mb-3">Assigned Courses ({{ $studentCourses->count() }})</h4>
                <ul class="space-y-2">
                    @foreach ($studentCourses as $course)
                        <li wire:key="assigned-{{ $course->id }}" class="flex justify-between items-center p-2 border bg-red-50 text-sm">
                            {{ $course->name }}
                            <button wire:click="removeCourse({{ $course->id }})" class="text-red-700 hover:text-red-900 font-medium">Remove</button>
                        </li>
                    @endforeach
                </ul>
            </div>
            
            {{-- 🚨 FIX: Add x-data="{ courseSelected: '' }" here to scope the Alpine model 🚨 --}}
            <div class="border p-4 rounded-lg" x-data="{ courseSelected: '' }"> 
                <h4 class="font-semibold mb-3">Available Courses ({{ $availableCourses->count() }})</h4>

               @if ($availableCourses->isNotEmpty())
                    <div class="flex space-x-2">
                        
                        <select 
                            x-model="courseSelected" {{-- This binds the select value to the Alpine variable --}}
                            wire:key="available-{{ count($availableCourses) }}" 
                            class="w-2/3 border rounded p-2 text-sm"
                            @reset-field.window="courseSelected = ''" {{-- This resets the Alpine model on successful add --}}
                        >
                            <option value="">Select a course to add</option> 
                            @foreach ($availableCourses as $course)
                                <option value="{{ $course->id }}">
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <button 
                            {{-- 🚨 FIX: Pass the Alpine variable value to the Livewire method as an argument --}}
                            wire:click="addCourse(courseSelected)" 
                            
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded text-sm disabled:opacity-50"
                        >
                            Add
                        </button>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">All available courses for this term are assigned.</p>
                @endif
                
                {{-- OPTIONAL: Display the list of available courses text only (if you still want a visual list) --}}
                <div class="mt-4 max-h-48 overflow-y-auto border p-2 rounded">
                     <ul class="space-y-1">
                        @foreach ($availableCourses as $course)
                             <li class="text-xs text-gray-600">{{ $course->name }}</li>
                        @endforeach
                     </ul>
                </div>
            </div>
        </div>
        
        <button wire:click="saveStudentCourses" class="mt-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Save Student Assignments</button>
    @elseif ($selectedStudentId)
        <p class="mt-6 p-4 border rounded text-center text-gray-500">No courses found for this student in the selected term. You can start adding courses from the list.</p>
    @endif
</div>