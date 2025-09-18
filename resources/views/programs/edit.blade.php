{{-- resources/views/programs/edit.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Program') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Program: {{ $program->name }}</h3>

                <form method="POST" action="{{ route('programs.update', $program) }}">
                    @csrf
                    @method('PUT') {{-- Use PUT method for updates --}}

                    <div class="mt-4">
                        <x-label for="name" value="{{ __('Program Name') }}" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $program->name)" required autofocus />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Update Program') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection