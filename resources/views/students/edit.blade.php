    {{-- resources/views/students/edit.blade.php --}}

    @extends('layouts.admin')

    @section('header')
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Student Profile') }}
        </h2>
    @endsection

    @section('content')
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Student: {{ $student->first_name }} {{ $student->last_name }}</h3>

                    <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h4 class="font-bold text-indigo-700 border-b mb-4 uppercase text-sm">System Linkage</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-label for="section_id" value="{{ __('Program and Section') }}" />
                                    <select id="section_id" name="section_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">-- Select Program and Section --</option>
                                        @foreach ($sections as $section)
                                            <option value="{{ $section->id }}" {{ old('section_id', $student->section_id) == $section->id ? 'selected' : '' }}>
                                                {{ $section->program->name ?? 'N/A Program' }} - {{ $section->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error for="section_id" class="mt-2" />
                                </div>

                                <div>
                                    <x-label for="user_id" value="{{ __('Link to User Account (Optional)') }}" />
                                    <select id="user_id" name="user_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">-- Select a User --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id', $student->user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error for="user_id" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h4 class="font-bold text-indigo-700 border-b mb-4 uppercase text-sm">Student Personal Data</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-label for="first_name" value="{{ __('First Name') }}" />
                                    <x-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name', $student->first_name)" required />
                                    <x-input-error for="first_name" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="middle_name" value="{{ __('Middle Name') }}" />
                                    <x-input id="middle_name" class="block mt-1 w-full" type="text" name="middle_name" :value="old('middle_name', $student->middle_name)" />
                                    <x-input-error for="middle_name" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="last_name" value="{{ __('Last Name') }}" />
                                    <x-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name', $student->last_name)" required />
                                    <x-input-error for="last_name" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="email" value="{{ __('Email Address') }}" />
                                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $student->email)" required />
                                    <x-input-error for="email" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="date_of_birth" value="{{ __('Birthdate') }}" />
                                    <x-input id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth" :value="old('date_of_birth', $student->date_of_birth)" />
                                </div>
                                <div>
                                    <x-label for="gender" value="{{ __('Gender') }}" />
                                    <select name="gender" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div>
                                    <x-label for="civil_status" value="{{ __('Civil Status') }}" />
                                    <x-input id="civil_status" class="block mt-1 w-full" type="text" name="civil_status" :value="old('civil_status', $student->civil_status)" />
                                </div>
                                <div>
                                    <x-label for="card_number" value="{{ __('Card Number') }}" />
                                    <x-input id="card_number" class="block mt-1 w-full" type="text" name="card_number" :value="old('card_number', $student->card_number)" />
                                </div>
                                <div>
                                    <x-label for="place_birth" value="{{ __('Place of Birth') }}" />
                                    <x-input id="place_birth" class="block mt-1 w-full" type="text" name="place_birth" :value="old('place_birth', $student->place_birth)" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <x-label for="current_address" value="{{ __('Current Address') }}" />
                                    <x-input id="current_address" class="block mt-1 w-full" type="text" name="current_address" :value="old('current_address', $student->current_address)" />
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <x-label for="nationality" value="{{ __('Nationality') }}" />
                                        <x-input id="nationality" class="block mt-1 w-full" type="text" name="nationality" :value="old('nationality', $student->nationality)" />
                                    </div>
                                    <div>
                                        <x-label for="religion" value="{{ __('Religion') }}" />
                                        <x-input id="religion" class="block mt-1 w-full" type="text" name="religion" :value="old('religion', $student->religion)" />
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <x-label for="mobile_number" value="{{ __('Mobile Number') }}" />
                                    <x-input id="mobile_number" class="block mt-1 w-full" type="text" name="mobile_number" :value="old('mobile_number', $student->mobile_number)" />
                                </div>
                                <div x-data="{ photoPreview: null }">
                                    <x-label for="profile_photo" value="{{ __('Update Profile Picture') }}" />

                                    <input
                                        type="file"
                                        id="profile_photo"
                                        name="profile_photo"
                                        accept="image/*"
                                        class="hidden"
                                        x-ref="profilePhotoInput"
                                        @change="
                                            const file = $event.target.files[0];
                                            if (!file) return;
                                            const reader = new FileReader();
                                            reader.onload = (e) => photoPreview = e.target.result;
                                            reader.readAsDataURL(file);
                                        "
                                    >

                                    <button
                                        type="button"
                                        class="group mt-2 relative block w-full max-w-[220px] overflow-hidden rounded-lg border border-gray-200 shadow-sm"
                                        @click="$refs.profilePhotoInput.click()"
                                    >
                                        <img
                                            x-show="!photoPreview"
                                            src="{{ $student->profile_photo ? asset('storage/' . $student->profile_photo) : asset('images/ntc-logo.jpeg') }}"
                                            alt="Profile Photo"
                                            class="h-40 w-full object-cover"
                                        >

                                        <img
                                            x-show="photoPreview"
                                            x-bind:src="photoPreview"
                                            alt="New Profile Photo Preview"
                                            class="h-40 w-full object-cover"
                                            style="display: none;"
                                        >

                                        <span class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center bg-black/35 text-white text-[11px] font-bold uppercase tracking-wider opacity-0 transition-opacity duration-200 group-hover:opacity-100 group-focus:opacity-100">
                                            Change Photo
                                        </span>
                                    </button>

                                    <p class="mt-2 text-[11px] text-gray-500">Click the photo to choose a new image.</p>
                                    <x-input-error for="profile_photo" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h4 class="font-bold text-indigo-700 border-b mb-4 uppercase text-sm">Parents Data</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <x-label for="father_name" value="{{ __('Father Name') }}" />
                                        <x-input id="father_name" class="block mt-1 w-full" type="text" name="father_name" :value="old('father_name', $student->father_name)" />
                                    </div>
                                    <div>
                                        <x-label for="father_occupation" value="{{ __('Occupation') }}" />
                                        <x-input id="father_occupation" class="block mt-1 w-full" type="text" name="father_occupation" :value="old('father_occupation', $student->father_occupation)" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <x-label for="mother_name" value="{{ __('Mother Name') }}" />
                                        <x-input id="mother_name" class="block mt-1 w-full" type="text" name="mother_name" :value="old('mother_name', $student->mother_name)" />
                                    </div>
                                    <div>
                                        <x-label for="mother_occupation" value="{{ __('Occupation') }}" />
                                        <x-input id="mother_occupation" class="block mt-1 w-full" type="text" name="mother_occupation" :value="old('mother_occupation', $student->mother_occupation)" />
                                    </div>
                                </div>
                                <div>
                                    <x-label for="parent_address" value="{{ __('Parents Address') }}" />
                                    <x-input id="parent_address" class="block mt-1 w-full" type="text" name="parent_address" :value="old('parent_address', $student->parent_address)" />
                                </div>
                                <div>
                                    <x-label for="parent_tel" value="{{ __('Tel. No.') }}" />
                                    <x-input id="parent_tel" class="block mt-1 w-full" type="text" name="parent_tel" :value="old('parent_tel', $student->parent_tel)" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h4 class="font-bold text-indigo-700 border-b mb-4 uppercase text-sm">Guardian & Admission Data</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="col-span-1">
                                    <x-label for="guardian_name" value="{{ __('Guardian Name') }}" />
                                    <x-input id="guardian_name" class="block mt-1 w-full" type="text" name="guardian_name" :value="old('guardian_name', $student->guardian_name)" />
                                </div>
                                <div class="col-span-1">
                                    <x-label for="guardian_address" value="{{ __('Guardian Address') }}" />
                                    <x-input id="guardian_address" class="block mt-1 w-full" type="text" name="guardian_address" :value="old('guardian_address', $student->guardian_address)" />
                                </div>
                                <div class="col-span-1">
                                    <x-label for="guardian_tel" value="{{ __('Guardian Tel. No.') }}" />
                                    <x-input id="guardian_tel" class="block mt-1 w-full" type="text" name="guardian_tel" :value="old('guardian_tel', $student->guardian_tel)" />
                                </div>
                                <div>
                                    <x-label for="basis_of_admission" value="{{ __('Basis of Admission') }}" />
                                    <x-input id="basis_of_admission" class="block mt-1 w-full" type="text" name="basis_of_admission" :value="old('basis_of_admission', $student->basis_of_admission)" />
                                </div>
                                <div>
                                    <x-label for="date_of_admission" value="{{ __('Date of Admission') }}" />
                                    <x-input id="date_of_admission" class="block mt-1 w-full" type="date" name="date_of_admission" :value="old('date_of_admission', $student->date_of_admission)" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4 border-t pt-4">
                            <a href="{{ route('students.show', $student) }}" class="text-sm text-gray-600 underline hover:text-gray-900 mr-4">Cancel</a>
                            <x-button>
                                {{ __('Save All Changes') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection
