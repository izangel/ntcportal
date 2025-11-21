@extends('layouts.admin') {{-- Or your main layout file --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('My Course Load') }}
    </h2>
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                        My Course Load
                    </h2>

                    <form action="{{ route('faculty.course_load.show') }}" method="GET" class="mb-8 p-4 bg-gray-50 rounded-lg shadow-inner flex items-end space-x-4">
                        
                        <div>
                           <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                            <select id="academic_year_id" name="academic_year_id" required 
                                    class="mt-1 block w-40 py-2.5 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select Year</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}" 
                                            {{ $selectedYear == $year->id ? 'selected' : '' }}>
                                        {{ $year->start_year }}-{{ $year->end_year }} </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <select id="semester" name="semester" required 
                                    class="mt-1 block w-40 py-2.5 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select Semester</option>
                                @foreach (['1st', '2nd', 'Summer'] as $semester)
                                    <option value="{{ $semester }}" 
                                            {{ $selectedSemester == $semester ? 'selected' : '' }}>
                                        {{ $semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="inline-flex items-center 
                            px-4 py-2.5              border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Show Courses
                        </button>
                    </form>
                    
                    @if ($selectedYear && $selectedSemester)
                        <h3 class="text-xl font-medium text-gray-700 mb-4 mt-8">
                            Courses for {{ $loadings->first()->academicYear->year ?? 'Selected Year' }} / {{ $selectedSemester }} Semester
                        </h3>
                    @endif

                    @if ($loadings->isNotEmpty())
                        <div class="overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-indigo-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Subject / Course
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Section
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Schedule
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Room
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($loadings as $loading) 
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $loading->course->code ?? 'N/A' }} - {{ $loading->course->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $loading->section->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold">
                                                {{ $loading->schedule }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $loading->room }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif ($selectedYear && $selectedSemester)
                        <div class="mt-8 p-4 text-center text-gray-500 bg-yellow-50 border border-yellow-200 rounded-lg">
                            No courses found for the selected Academic Year and Semester.
                        </div>
                    @else
                        <div class="mt-8 p-4 text-center text-gray-500 bg-blue-50 border border-blue-200 rounded-lg">
                            Please select an Academic Year and Semester above to view your course load.
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection