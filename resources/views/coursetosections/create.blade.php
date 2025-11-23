{{-- resources/views/enrollments/create.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Add New Course to Section') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assign a Course</h3>

                <form method="POST" action="{{ route('coursetosections.store') }}">
                    @csrf

                    <div class="mt-4">
                        <x-label for="academic_year_id" value="{{ __('Academic Year') }}" />
                        <select id="academic_year_id" name="academic_year_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select AY --</option>
                            @foreach ($academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}" {{ old('academic_year_id') == $academicYear->id ? 'selected' : '' }}>
                                    {{ $academicYear->start_year }}-({{ $academicYear->end_year }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="academic_year_id" class="mt-2" />
                    </div>
                 
                    <div class="mt-4">
                        <x-label for="semester" value="{{ __('Semester') }}" />
                        <select id="semester" name="semester" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select Semester --</option>
                            <option value="1st">1st</option>
                            <option value="2nd">2nd</option>
                            <option value="Summer">Summer</option>
                        </select>
                        <x-input-error for="academic_year_id" class="mt-2" />
                    </div>
                 

                    <div class="mt-4">
                        <x-label for="course_id" value="{{ __('Course') }}" />
                        <select id="course_id" name="course_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select Course --</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->code }}-{{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="course_id" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="section_id" value="{{ __('Program and Section') }}" />
                        <select id="section_id" name="section_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select Program and Section --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->program->name }}-({{ $section->name }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="section_id" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Assign Course') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection