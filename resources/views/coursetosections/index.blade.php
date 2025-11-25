
@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Manage Courses to Section') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Courses Per Section List</h3>

                <div class="mb-4 flex justify-between items-center">
                    <a href="{{ route('coursetosections.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Assign New Course To Section
                    </a>

                    {{-- Filter Form (already existing from previous steps, ensure it's here) --}}
                    <form method="GET" action="{{ route('coursetosections.index') }}" class="flex items-center space-x-2">
                        <select id="academic_year_id" name="academic_year_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Academic Years</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->start_year }} - {{ $year->end_year }}
                                </option>
                            @endforeach
                        </select>

                        <select id="semester" name="semester" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Semesters</option>
                             <option value="1st">1st</option>
                              <option value="2nd">2nd</option>
                               <option value="Sum">Summer</option>
                        </select>

                        <x-button type="submit">
                            {{ __('Filter') }}
                        </x-button>
                        @if (request()->filled('academic_year_id') || request()->filled('semester'))
                            <a href="{{ route('coursetosections.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Clear Filters</a>
                        @endif
                    </form>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($coursetosections as $coursetosection)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $coursetosection->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $coursetosection->academicYear->start_year ?? 'N/A' }} - {{ $coursetosection->academicYear->end_year ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $coursetosection->semester }}
                                    </td>
                                   
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $coursetosection->course->code}}-{{ $coursetosection->course->name}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $coursetosection->section->program->name ?? 'N/A' }}-{{ $coursetosection->section->name ?? 'N/A' }}</td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('coursetosections.edit', $coursetosection) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                        <form action="{{ route('coursetosections.destroy', $coursetosection) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this course?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No courses found.</td> {{-- UPDATE COLSPAN --}}
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links (if you've implemented it on coursetosections.index) --}}
                <div class="mt-4">
                    {{-- $subjsecs->links() --}}
                </div>
            </div>
        </div>
    @endsection