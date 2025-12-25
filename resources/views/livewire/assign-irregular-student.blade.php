<div class="p-6 bg-white shadow-xl rounded-lg border border-gray-200">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Assign Student to Course</h2>
        <p class="text-sm text-gray-500">(For Irregular Students / Special Class)</p>
    </div>

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

    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Academic Year -->
            <div>
                <label for="acad_year" class="block text-sm font-medium text-gray-700 mb-1">Acad Year</label>
                <select wire:model.live="selectedAcademicYearId" id="acad_year" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Year</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->start_year }} - {{ $year->end_year }}</option>
                    @endforeach
                </select>
                @error('selectedAcademicYearId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Semester -->
            <div>
                <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Sem</label>
                <select wire:model.live="selectedSemester" id="semester" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Semester</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }} Semester</option>
                    @endforeach
                </select>
                @error('selectedSemester') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="space-y-4 mb-6">
            <!-- Student Name -->
            <div>
                <label for="student" class="block text-sm font-medium text-gray-700 mb-1">Student Name</label>
                <select wire:model="selectedStudentId" id="student" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->last_name }}, {{ $student->first_name }}</option>
                    @endforeach
                </select>
                @error('selectedStudentId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Course -->
            <div>
                <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                <select wire:model="selectedCourseId" id="course" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->description }}</option>
                    @endforeach
                </select>
                @error('selectedCourseId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Section (Required by DB) -->
            <div>
                <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section / Class</label>
                <select wire:model="selectedSectionId" id="section" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Section</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">
                            {{ $section->program->code ?? '' }} {{ $section->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Select the section this student will attend.</p>
                @error('selectedSectionId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <!-- Validate Button (Visual only as per sketch) -->
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 cursor-not-allowed opacity-50" disabled>
                Validate
            </button>

            <!-- Add Button -->
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add (Save to student course)
            </button>
        </div>
    </form>
</div>
