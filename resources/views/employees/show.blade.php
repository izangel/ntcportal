@extends('layouts.admin')

@section('header')
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employee Details: ') . $employee->last_name .' '  . $employee->first_name .' '. $employee->mid_name}}
        </h2>
@endsection

@section('content')


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <div class="mb-4">
                    <p class="text-sm text-gray-600"><strong>Name:</strong> {{ $employee->first_name . ' ' . $employee->mid_name . ' ' . $employee->last_name }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-600"><strong>Email:</strong> {{ $employee->email ?? 'N/A' }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-600"><strong>Phone:</strong> {{ $employee->phone ?? 'N/A' }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-600"><strong>Address:</strong> {{ $employee->address ?? 'N/A' }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-600"><strong>Role:</strong> {{ ucwords(str_replace('_', ' ', $employee->role)) }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-600"><strong>Linked User:</strong> {{ $employee->user->email ?? 'Not Linked' }}</p>
                </div>

                <div class="flex justify-end mt-6">
                    <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        {{ __('Edit Employee') }}
                    </a>
                    <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 active:bg-gray-600 focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Back to List') }}
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection