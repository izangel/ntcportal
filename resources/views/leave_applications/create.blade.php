{{-- resources/views/leave_applications/create.blade.php --}}

@extends('layouts.admin')

@section('header')
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Submit New Leave Application') }}
            </h2>
        </div>
    </header>
@endsection

@section('content')
    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-6 sm:p-8">
                <form method="POST" action="{{ route('leave_applications.store') }}">
                    @csrf

                    @include('leave_applications._form', ['employees' => $employees, 'loggedInEmployee' => $loggedInEmployee, 'teachers' => $teachers, 'staffPersonnel' => $staffPersonnel, 'leaveTypes' => $leaveTypes])

                    <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6">
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            {{ __('Submit Leave Application') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection