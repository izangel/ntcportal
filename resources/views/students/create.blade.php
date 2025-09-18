{{-- resources/views/students/create.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Add New Student') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Student</h3>

                <form method="POST" action="{{ route('students.store') }}">
                    @csrf

                    <div class="mt-4">
                        <x-label for="first_name" value="{{ __('First Name') }}" />
                        <x-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus />
                        <x-input-error for="first_name" class="mt-2" />
                    </div>

                     <div class="mt-4">
                        <x-label for="last_name" value="{{ __('Last Name') }}" />
                        <x-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autofocus />
                        <x-input-error for="last_name" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="student_email" value="{{ __('Student Email') }}" />
                        <x-input id="student_email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                        <x-input-error for="email" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="section_id" value="{{ __('Program and Section') }}" />
                        <select id="section_id" name="section_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select Program and Section --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->program->name ?? 'N/A Program' }} - {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="section_id" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="user_id" value="{{ __('Link to User Account (Optional)') }}" />
                        <select id="user_id" name="user_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select a User --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="user_id" class="mt-2" />
                        <p class="text-sm text-gray-500 mt-1">If linking to an existing user, they should have the 'student' role.</p>
                    </div>


                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Add Student') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection