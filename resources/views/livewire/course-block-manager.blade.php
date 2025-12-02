<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Course Block Assignment</h2>
    
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-indigo-500">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div>
                <label for="ay" class="block text-sm font-medium text-gray-700">Academic Year</label>
                <select id="ay" wire:model.live="academicYearId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select AY</option>
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }} - {{ $ay->end_year }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="sem" class="block text-sm font-medium text-gray-700">Semester</label>
                <select id="sem" wire:model.live="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Semester</option>
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }} Semester</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
                <select id="section" wire:model.live="sectionId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Section</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    
    @if ($sectionId && $selectedSection)
    
        @if (session()->has('message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                <p class="font-bold">Success</p>
                <p>{{ session('message') }}</p>
            </div>
        @endif
    
        <div class="flex flex-col lg:flex-row gap-8">
            
            <div class="w-full lg:w-1/3">
                <div class="bg-white shadow-lg rounded-xl p-4 border border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Students in {{ $selectedSection->name }} ({{ count($students) }})</h3>
                    <ul class="space-y-2 overflow-y-auto max-h-96">
                        @forelse ($students as $student)
                            <li class="p-2 text-sm border-b last:border-b-0">{{ $student->last_name}}, {{ $student->first_name}} {{ $student->mid_name}}</li>
                        @empty
                            <li class="text-gray-500 p-2 italic">No students found for this section.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="w-full lg:w-2/3 space-y-8">

                <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
                    <h3 class="text-xl font-semibold text-indigo-600 mb-4">Existing Course Blocks</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($courseBlocks as $block)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $block->course->code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $block->faculty->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $block->room_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $block->schedule_string }}</td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 italic">No course blocks assigned yet for this section, AY, and Semester.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
                    <h3 class="text-xl font-semibold text-green-600 mb-4">Add New Course Block</h3>

                    <form wire:submit.prevent="saveCourseBlock">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="course_id" class="block text-sm font-medium text-gray-700">Course</label>
                                <select id="course_id" wire:model.change="newCourseBlock.course_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Select Course</option>
                                    @foreach ($allCourses as $course)
                                        <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                                    @endforeach
                                </select>
                                @error('newCourseBlock.course_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="faculty_id" class="block text-sm font-medium text-gray-700">Faculty</label>
                                <select id="faculty_id" wire:model.change="newCourseBlock.faculty_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Select Faculty</option>
                                    @foreach ($allFaculty as $faculty)
                                        <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                    @endforeach
                                </select>
                                @error('newCourseBlock.faculty_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="room_name" class="block text-sm font-medium text-gray-700">Room Name</label>
                                <input type="text" id="room_name" wire:model="newCourseBlock.room_name" placeholder="e.g., C305 or Gym" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('newCourseBlock.room_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="schedule_string" class="block text-sm font-medium text-gray-700">Schedule (e.g., MW 1-3PM)</label>
                                <input type="text" id="schedule_string" wire:model="newCourseBlock.schedule_string" placeholder="e.g., MW 1:00 PM - 3:00 PM" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('newCourseBlock.schedule_string') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Course Block
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <p class="text-center text-gray-500 mt-10 p-6 bg-white shadow-lg rounded-xl">
            Please select an **Academic Year**, **Semester**, and **Section** above to view and assign course blocks.
        </p>
    @endif
</div>