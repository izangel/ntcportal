{{-- resources/views/academic_years/edit.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Academic Year') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Academic Year: {{ $academicYear->start_year }} - {{ $academicYear->end_year }}</h3>

                <form method="POST" action="{{ route('academic_years.update', $academicYear) }}">
                    @csrf
                    @method('PUT')

                    <div class="mt-4">
                        <x-label for="start_year" value="{{ __('Start Year') }}" />
                        <x-input id="start_year" class="block mt-1 w-full" type="number" name="start_year" :value="old('start_year', $academicYear->start_year)" required autofocus />
                        <x-input-error for="start_year" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="end_year" value="{{ __('End Year') }}" />
                        <x-input id="end_year" class="block mt-1 w-full" type="number" name="end_year" :value="old('end_year', $academicYear->end_year)" required />
                        <x-input-error for="end_year" class="mt-2" />
                    </div>

                    <div class="mt-4 flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }}>
                        <x-label for="is_active" class="ml-2" value="{{ __('Set as Active Academic Year') }}" />
                        <x-input-error for="is_active" class="mt-2" />
                        <p class="text-sm text-gray-500 ml-4">Setting this as active will deactivate any other currently active academic year.</p>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Update Academic Year') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection