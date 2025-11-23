@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Course to Section') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">

                <form action="{{ route('coursetosections.update', $coursetosection->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Academic Year & Semester --}}
                    <div class="grid grid-cols-2 gap-4">

                        {{-- Academic Year --}}
                        <div>
                            <x-input-label value="Academic Year" />
                            <select name="academic_year_id" class="block w-full mt-1 rounded-md border-gray-300">
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->academicYear->id }}"
                                        {{ $semester->academicYear->id == $coursetosection->academic_year_id ? 'selected' : '' }}>
                                        {{ $semester->academicYear->start_year }} {{ $semester->academicYear->end_year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Semester --}}
                        <div>
                            <x-input-label value="Semester" />
                            <select name="semester_id" class="block w-full mt-1 rounded-md border-gray-300">
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}"
                                        {{ $semester->id == $coursetosection->semester_id ? 'selected' : '' }}>
                                        {{ $semester->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- Course --}}
                    <div class="mt-4">
                        <x-input-label value="Course" />
                        <select name="course_id" class="block w-full mt-1 rounded-md border-gray-300">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}"
                                    {{ $course->id == $coursetosection->course_id ? 'selected' : '' }}>
                                    {{ $course->code }} - {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Section --}}
                    <div class="mt-4">
                        <x-input-label value="Section" />
                        <select name="section_id" class="block w-full mt-1 rounded-md border-gray-300">
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}"
                                    {{ $section->id == $coursetosection->section_id ? 'selected' : '' }}>
                                    {{ $section->program->code }} - {{ $section->name }}
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
</div>
@endsection
