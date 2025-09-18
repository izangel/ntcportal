{{-- resources/views/semesters/create.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Add New Semester') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Semester</h3>

                <form method="POST" action="{{ route('semesters.store') }}">
                    @csrf

                    <div class="mt-4">
                        <x-label for="academic_year_id" value="{{ __('Academic Year') }}" />
                        <select id="academic_year_id" name="academic_year_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select an Academic Year --</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->start_year }} - {{ $year->end_year }} {{ $year->is_active ? '(Active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="academic_year_id" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="name" value="{{ __('Semester Name (e.g., First Semester, Summer)') }}" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="start_date" value="{{ __('Start Date') }}" />
                        <x-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" required />
                        <x-input-error for="start_date" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="end_date" value="{{ __('End Date') }}" />
                        <x-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" required />
                        <x-input-error for="end_date" class="mt-2" />
                    </div>

                    <div class="mt-4 flex items-center">
                    {{-- HIDDEN INPUT FIX FOR CHECKBOXES --}}
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" {{ old('is_active') ? 'checked' : '' }}>
                    <x-label for="is_active" class="ml-2" value="{{ __('Set as Active Semester for this Academic Year') }}" />
                    <x-input-error for="is_active" class="mt-2" />
                    <p class="text-sm text-gray-500 ml-4">Setting this as active will deactivate any other currently active semester within the selected academic year.</p>
                </div>
                

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Add Semester') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection