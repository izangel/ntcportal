@extends('layouts.admin')

@section('header')
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Employee: ') . $employee->last_name.' '.$employee->first_name.' '.$employee->mid_name }}
        </h2>
@endsection

@section('content')


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('employees.update', $employee) }}">
                    @csrf
                    @method('PUT')

                    @include('employees._form', ['employee' => $employee, 'unlinkedUsers' => $unlinkedUsers])

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Update Employee') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        </h2>
@endsection