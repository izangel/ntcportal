@extends('layouts.admin')

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Course to Section
    </h2>
</x-slot>

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">

            {{-- Display Validation Errors --}}
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Update Form --}}
            <form action="{{ route('coursetosections.update', $coursetosection->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Academic Year --}}
                <div class="mb-4">
                    <x-input-label value="Academic Year" />
                    <select name="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->academicYear->id }}"
                                {{ $sem->academicYear->id == $coursetosection->academic_year_id ? 'selected' : '' }}>
                                {{ $sem->academicYear->start_year }} - {{ $sem->academicYear->end_year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Semester --}}
                <div class="mb-4">
                    <x-input-label value="Semester" />
                    <select name="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="1st" {{ $coursetosection->semester == '1st' ? 'selected' : '' }}>1st</option>
                        <option value="2nd" {{ $coursetosection->semester == '2nd' ? 'selected' : '' }}>2nd</option>
                        <option value="Sum" {{ $coursetosection->semester == 'Sum' ? 'selected' : '' }}>Summer</option>
                    </select>
                </div>

                {{-- Course --}}
                <div class="mb-4">
                    <x-input-label value="Course" />
                    <select name="course_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ $coursetosection->course_id == $course->id ? 'selected' : '' }}>
                                {{ $course->code }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Section --}}
                <div class="mb-4">
                    <x-input-label value="Section" />
                    <select name="section_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ $coursetosection->section_id == $section->id ? 'selected' : '' }}>
                                {{ $section->program->code ?? '' }} - {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit Button --}}
                <div class="mt-6 flex justify-end">
                    <x-primary-button>Update Course to Section</x-primary-button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
