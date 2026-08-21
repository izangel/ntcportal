{{-- resources/views/sections/copy.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Copy Sections to a New Academic Year') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Bulk Copy Sections</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Copy all sections from a previous academic year into a new one in one click.
                    Sections with the same name and program already present in the target year are skipped.
                </p>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('sections.copy.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-label for="source_academic_year_id" value="{{ __('Source Academic Year') }}" />
                            <select id="source_academic_year_id" name="source_academic_year_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Select Source Year --</option>
                                @foreach ($ays as $ay)
                                    <option value="{{ $ay->id }}" {{ old('source_academic_year_id') == $ay->id ? 'selected' : '' }}>
                                        {{ $ay->start_year }}-{{ $ay->end_year }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error for="source_academic_year_id" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="target_academic_year_id" value="{{ __('Target Academic Year') }}" />
                            <select id="target_academic_year_id" name="target_academic_year_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Select Target Year --</option>
                                @foreach ($ays as $ay)
                                    <option value="{{ $ay->id }}" {{ (old('target_academic_year_id') ?: $activeAy->id ?? null) == $ay->id ? 'selected' : '' }}>
                                        {{ $ay->start_year }}-{{ $ay->end_year }}
                                        @if ($activeAy && $activeAy->id == $ay->id)
                                            (Current Active)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error for="target_academic_year_id" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('sections.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold mr-2 hover:bg-gray-300">
                            <i class="fas fa-eye"></i>
                            View All Sections
                        </a>
                        <x-button>
                            {{ __('Copy Sections') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection