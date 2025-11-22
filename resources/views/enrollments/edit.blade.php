{{-- resources/views/enrollments/edit.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Enrollment') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Enrollment</h3>

                <form method="POST" action="{{ route('enrollments.update', $enrollment) }}">
                    @csrf
                    @method('PUT')

                    <div class="mt-4">
                        <x-label for="student_id" value="{{ __('Student') }}" />
                        <select id="student_id" name="student_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select Student --</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id', $enrollment->student_id) == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }} ({{ $student->email }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="student_id" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="section_id" value="{{ __('section') }}" />
                        <select id="section_id" name="section_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select section --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" {{ old('section_id', $enrollment->section_id) == $section->id ? 'selected' : '' }}>
                                    {{ $section->program->name }}-({{ $section->name }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="section_id" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="semester_id" value="{{ __('Semester') }}" />
                        <select id="semester_id" name="semester_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select Semester --</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ old('semester_id', $enrollment->semester_id) == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->name }} ({{ $semester->academicYear->start_year }} - {{ $semester->academicYear->end_year }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="semester_id" class="mt-2" />
                    </div>


                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Update Enrollment') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection