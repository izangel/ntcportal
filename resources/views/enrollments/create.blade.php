{{-- resources/views/enrollments/create.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Create New Enrollment') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Enroll a Student</h3>

                <form method="POST" action="{{ route('enrollments.store') }}">
                    @csrf

                    <div class="mb-4 p-4 border rounded-md bg-blue-50 border-blue-200 text-blue-800">
                        <p class="font-semibold">Enrolling for Current Active Semester:</p>
                        <p>{{ $activeSemester->name }} ({{ $activeSemester->academicYear->start_year }} - {{ $activeSemester->academicYear->end_year }})</p>
                        <p class="text-sm">({{ $activeSemester->start_date->format('M d, Y') }} to {{ $activeSemester->end_date->format('M d, Y') }})</p>
                        <p class="text-sm mt-2 text-blue-600">To change the active semester, go to "Manage Academic Years" and "Manage Semesters".</p>
                    </div>


                    <div class="mt-4">
                        <x-label for="student_id" value="{{ __('Student') }}" />
                        <select id="student_id" name="student_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select Student --</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }} ({{ $student->email }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="student_id" class="mt-2" />
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
                            {{ __('Create Enrollment') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection