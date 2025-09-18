@extends('layouts.admin') {{-- CHANGE THIS FROM layouts.app --}}

@section('header') {{-- Add this section for page heading --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enrollment Details') }}
        </h2>
@endsection

@section('content') {{-- Wrap your existing content with this section --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <p><strong>Student:</strong> {{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }} ({{ $enrollment->student->student_id }})</p>
                        <p><strong>Course:</strong> {{ $enrollment->course->name }} ({{ $enrollment->course->code }})</p>
                        <p><strong>Grade:</strong> {{ $enrollment->grade ?? 'N/A' }}</p>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('enrollments.edit', $enrollment) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                            {{ __('Edit Enrollment') }}
                        </a>
                        <a href="{{ route('enrollments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Back to List') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection