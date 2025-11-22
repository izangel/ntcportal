@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Faculty Loading') }}
    </h2>
@endsection

@section('content')

<form method="POST" action="{{ route('faculty-loadings.update', $loading->id) }}" class="max-w-4xl mx-auto p-6 bg-white shadow-xl rounded-lg">
    @csrf
    @method('PUT')

    <h2 class="text-2xl font-bold text-gray-800 mb-6">✏️ Edit Faculty Loading</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
            <select id="academic_year_id" name="academic_year_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ (old('academic_year_id', $loading->academic_year_id) == $year->id) ? 'selected' : '' }}>{{ $year->start_year }}-{{ $year->end_year }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="semester" class="block text-sm font-medium text-gray-700">Semester</label>
            <select id="semester" name="semester" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                   <option value="1st" {{ (old('semester', $loading->semester) == '1st') ? 'selected' : '' }}>First Semester</option>
                   <option value="2nd" {{ (old('semester', $loading->semester) == '2nd') ? 'selected' : '' }}>Second Semester</option>
                   <option value="Summer" {{ (old('semester', $loading->semester) == 'Summer') ? 'selected' : '' }}>Summer</option>
            </select>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="course_id" class="block text-sm font-medium text-gray-700">Course</label>
            <select id="course_id" name="course_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ (old('course_id', $loading->course_id) == $course->id) ? 'selected' : '' }}>{{ $course->code }} - {{ $course->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="faculty_id" class="block text-sm font-medium text-gray-700">Faculty</label>
            <select id="faculty_id" name="faculty_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @foreach($facultyMembers as $faculty)
                    <option value="{{ $faculty->id }}" {{ (old('faculty_id', $loading->faculty_id) == $faculty->id) ? 'selected' : '' }}>{{ $faculty->last_name }}, {{ $faculty->first_name }} {{ $faculty->mid_name }}</option>
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
                    <option value="{{ $section->id }}" {{ (old('section_id', $loading->section_id) == $section->id) ? 'selected' : '' }}>{{ $section->program->name }}-{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="room" class="block text-sm font-medium text-gray-700">Room</label>
            <input type="text" id="room" name="room" required value="{{ old('room', $loading->room) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" placeholder="e.g., G305">
        </div>
        <div>
            <label for="schedule" class="block text-sm font-medium text-gray-700">Schedule</label>
            <input type="text" id="schedule" name="schedule" required value="{{ old('schedule', $loading->schedule) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" placeholder="e.g., M-W 8:00am - 10:00am">
        </div>
    </div>
    
    <div class="flex justify-between">
        <a href="{{ route('faculty-loadings.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Cancel</a>
        <button type="submit" 
            class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-md shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Update Loading
        </button>
    </div>
</form>

@endsection
