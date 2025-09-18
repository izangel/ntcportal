{{-- resources/views/leave_applications/edit.blade.php --}}

@extends('layouts.admin') {{-- Or your main layout file --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Leave Application') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('leave_applications.update', $leaveApplication) }}">
                    @csrf
                    @method('PUT') {{-- Use PUT method for updates --}}

                    @include('leave_applications._form', ['leaveApplication' => $leaveApplication, 'employees' => $employees])

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Update Leave Application') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection