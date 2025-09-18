{{-- resources/views/reports/index.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Reports') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Available Reports</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 shadow-sm flex flex-col items-start">
                        <h4 class="text-md font-semibold text-gray-800 mb-2">Students Per Course</h4>
                        <p class="text-sm text-gray-600 mb-4">View the count of unique students enrolled in each course for specific semesters or academic years.</p>
                        <a href="{{ route('reports.studentsPerCourse') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            View Report
                        </a>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 shadow-sm flex flex-col items-start">
                        <h4 class="text-md font-semibold text-gray-800 mb-2">New vs. Old Students</h4>
                        <p class="text-sm text-gray-600 mb-4">Analyze the number of new students versus returning students for each semester.</p>
                        <a href="{{ route('reports.studentTypes') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            View Report
                        </a>
                    </div>

                    {{-- Add more report cards here as needed --}}
                </div>
            </div>
        </div>
    </div>
@endsection