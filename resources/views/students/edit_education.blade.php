@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Education') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Education: {{ $student->first_name }} {{ $student->last_name }}</h3>

                @php
                    $primarySecondaryLevels = [
                        'Nursery Education',
                        'Junior Kinder Education',
                        'Senior Kinder Education',
                        'Primary Education',
                        'Intermediate Education',
                        'Secondary Education',
                    ];

                    $higherEducationLevels = [
                        'Bacaluarte/ Teritary',
                        'Masteral / Graduate',
                        'Doctorate / Post Graduate',
                    ];

                    $primarySecondaryEducation = old('primary_secondary_education');
                    if (is_array($primarySecondaryEducation)) {
                        $primarySecondaryEducation = collect($primarySecondaryEducation)->keyBy('level')->toArray();
                    } else {
                        $primarySecondaryEducation = $student->education
                            ->where('education_group', 'primary_secondary')
                            ->keyBy('level')
                            ->toArray();
                    }

                    $higherEducation = old('higher_education');
                    if (is_array($higherEducation)) {
                        $higherEducation = collect($higherEducation)->keyBy('level')->toArray();
                    } else {
                        $higherEducation = $student->education
                            ->where('education_group', 'higher_education')
                            ->keyBy('level')
                            ->toArray();
                    }
                @endphp

                <form method="POST" action="{{ route('students.education.update', $student) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-8">
                        <h4 class="font-bold text-indigo-700 border-b mb-4 uppercase text-sm">Primary and Secondary Education</h4>

                        @foreach($primarySecondaryLevels as $index => $level)
                            @php
                                $education = $primarySecondaryEducation[$level] ?? [];
                            @endphp
                            <div class="border rounded-lg p-4 mb-4 bg-gray-50">
                                <input type="hidden" name="primary_secondary_education[{{ $index }}][id]" value="{{ $education['id'] ?? '' }}">
                                <input type="hidden" name="primary_secondary_education[{{ $index }}][level]" value="{{ $level }}">

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-label value="{{ __('Education Level') }}" />
                                        <x-input class="block mt-1 w-full bg-gray-100" type="text" value="{{ $level }}" readonly />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('School') }}" />
                                        <x-input class="block mt-1 w-full" type="text" name="primary_secondary_education[{{ $index }}][school_name]" value="{{ $education['school_name'] ?? '' }}" />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('Inclusive Dates') }}" />
                                        <x-input class="block mt-1 w-full" type="text" name="primary_secondary_education[{{ $index }}][inclusive_dates]" value="{{ $education['inclusive_dates'] ?? '' }}" placeholder="e.g. 2010-2014" />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('Date Entered') }}" />
                                        <x-input class="block mt-1 w-full" type="date" name="primary_secondary_education[{{ $index }}][date_entered]" value="{{ $education['date_entered'] ?? '' }}" />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('Date Graduated') }}" />
                                        <x-input class="block mt-1 w-full" type="date" name="primary_secondary_education[{{ $index }}][date_graduated]" value="{{ $education['date_graduated'] ?? '' }}" />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('Honors and Awards') }}" />
                                        <x-input class="block mt-1 w-full" type="text" name="primary_secondary_education[{{ $index }}][honors_awards]" value="{{ $education['honors_awards'] ?? '' }}" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-8">
                        <h4 class="font-bold text-indigo-700 border-b mb-4 uppercase text-sm">College / Graduate / Post Graduate Education</h4>

                        @foreach($higherEducationLevels as $index => $level)
                            @php
                                $education = $higherEducation[$level] ?? [];
                            @endphp
                            <div class="border rounded-lg p-4 mb-4 bg-gray-50">
                                <input type="hidden" name="higher_education[{{ $index }}][id]" value="{{ $education['id'] ?? '' }}">
                                <input type="hidden" name="higher_education[{{ $index }}][level]" value="{{ $level }}">

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-label value="{{ __('Education Level') }}" />
                                        <x-input class="block mt-1 w-full bg-gray-100" type="text" value="{{ $level }}" readonly />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('School') }}" />
                                        <x-input class="block mt-1 w-full" type="text" name="higher_education[{{ $index }}][school_name]" value="{{ $education['school_name'] ?? '' }}" />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('Course/Major') }}" />
                                        <x-input class="block mt-1 w-full" type="text" name="higher_education[{{ $index }}][course_major]" value="{{ $education['course_major'] ?? '' }}" />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('Date Graduated') }}" />
                                        <x-input class="block mt-1 w-full" type="date" name="higher_education[{{ $index }}][date_graduated]" value="{{ $education['date_graduated'] ?? '' }}" />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('SO No.') }}" />
                                        <x-input class="block mt-1 w-full" type="text" name="higher_education[{{ $index }}][so_number]" value="{{ $education['so_number'] ?? '' }}" />
                                    </div>
                                    <div>
                                        <x-label value="{{ __('Thesis') }}" />
                                        <x-input class="block mt-1 w-full" type="text" name="higher_education[{{ $index }}][thesis]" value="{{ $education['thesis'] ?? '' }}" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end mt-4 border-t pt-4">
                        <a href="{{ route('students.show', ['student' => $student, 'tab' => 'education']) }}" class="text-sm text-gray-600 underline hover:text-gray-900 mr-4">Cancel</a>
                        <x-button>
                            {{ __('Save Education') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
