<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Course Block</h2>

            <form action="{{ route('course_blocks.update', $courseBlock->id) }}" method="POST">
                @csrf
                @method('PUT')
 
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Course</label>
                        <select name="course_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ $courseBlock->course_id == $course->id ? 'selected' : '' }}>
                                    {{ $course->code }} - {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Section</label>
                        <select name="section_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ $courseBlock->section_id == $section->id ? 'selected' : '' }}>
                                     {{ $section->program->name }}-{{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Faculty</label>
                        <select name="faculty_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $courseBlock->faculty_id == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->last_name }}, {{ $emp->first_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                        <select name="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $courseBlock->academic_year_id == $ay->id ? 'selected' : '' }}>
                                    {{ $ay->start_year }}-{{ $ay->end_year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Semester</label>
                        <select name="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="1st Semester" {{ $courseBlock->semester == '1st' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2nd Semester" {{ $courseBlock->semester == '2nd' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="Summer" {{ $courseBlock->semester == 'Summer' ? 'selected' : '' }}>Summer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Room Name</label>
                        <input type="text" name="room_name" value="{{ old('room_name', $courseBlock->room_name) }}" class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Schedule</label>
                        <input type="text" name="schedule_string" value="{{ old('schedule_string', $courseBlock->schedule_string) }}" class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('course_blocks.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow shadow-indigo-200">
                        Save Edit
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>