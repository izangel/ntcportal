@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Add New Faculty Loading') }}
    </h2>
@endsection

@section('content')

<form method="POST" action="{{ route('faculty-loadings.store') }}" class="max-w-4xl mx-auto p-6 bg-white shadow-xl rounded-lg">
    @csrf

    <h2 class="text-2xl font-bold text-gray-800 mb-6">📝 Add Faculty Loading</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
            <select id="academic_year_id" name="academic_year_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->start_year }}-{{ $year->end_year }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="semester" class="block text-sm font-medium text-gray-700">Semester</label>
            <select id="semester" name="semester" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
               
                    <option value="1st">First Semester</option>
                     <option value="2nd">Second Semester</option>
                     <option value="Summer">Summer</option>
               
            </select>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="course_id" class="block text-sm font-medium text-gray-700">Course</label>
            <select id="course_id" name="course_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="faculty_id" class="block text-sm font-medium text-gray-700">Faculty</label>
            <select id="faculty_id" name="faculty_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @foreach($facultyMembers as $faculty)
                    <option value="{{ $faculty->id }}">{{ $faculty->last_name }}, {{ $faculty->first_name }} {{ $faculty->mid_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
         <div>
            <label for="section_id" class="block text-sm font-medium text-gray-700">Section</label>
            <select id="section_id" name="section_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @foreach($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->program->name }}-{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="room" class="block text-sm font-medium text-gray-700">Room</label>
            <input type="text" id="room" name="room" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" placeholder="e.g., G305">
        </div>
        <div>
            <label for="schedule" class="block text-sm font-medium text-gray-700">Schedule</label>
            <input type="text" id="schedule" name="schedule" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" placeholder="e.g., M-W 8:00am - 10:00am">
        </div>
    </div>
    
    <div class="flex justify-end">
        <button type="submit" 
            class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-md shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Save Loading
        </button>
    </div>
</form>

@endsection