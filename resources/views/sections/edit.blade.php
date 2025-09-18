{{-- resources/views/sections/edit.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Section') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Section: {{ $section->name }} ({{ $section->program->name ?? 'N/A' }})</h3>

                <form method="POST" action="{{ route('sections.update', $section) }}">
                    @csrf
                    @method('PUT') {{-- Use PUT method for updates --}}

                    <div class="mt-4">
                        <x-label for="program_id" value="{{ __('Program') }}" />
                        <select id="program_id" name="program_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select a Program --</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}" {{ old('program_id', $section->program_id) == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="program_id" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="name" value="{{ __('Section Name (e.g., 1A, 1B)') }}" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $section->name)" required autofocus />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Update Section') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection