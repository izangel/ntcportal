@extends('layouts.plain')

@section('content')
<div class="p-4 bg-gray-100 min-h-screen font-sans">
    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 border-l-4 border-green-600 text-green-700 text-[11px] font-black uppercase rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-orange-100 border-l-4 border-orange-500 text-orange-700 text-[10px] font-bold uppercase rounded shadow-sm">
            <p class="mb-1">Please fix the following errors:</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-5xl mx-auto">
        <form action="{{ route('students.update', $student) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-xl font-black text-blue-900 uppercase tracking-tighter">Edit Student Profile</h1>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">ID: {{ $student->student_id }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('students.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-[10px] font-black uppercase hover:bg-gray-300">Cancel</a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded text-[10px] font-black uppercase shadow-lg hover:bg-blue-700 transition">Save Changes</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                
                {{-- Column 1 & 2: Personal Information --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white p-6 rounded shadow-sm border border-gray-200">
                        <h2 class="text-[10px] font-black text-gray-400 uppercase mb-4 border-b pb-1">Basic Identification</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="text-[10px] font-black uppercase text-gray-500">Student ID (Read Only)</label>
                                <input type="text" value="{{ $student->student_id }}" disabled class="w-full border-gray-200 rounded text-sm bg-gray-50 text-gray-400 font-bold">
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase text-gray-500">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase text-gray-500">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase text-gray-500">Middle Name</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}" class="w-full border-gray-300 rounded text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500">Gender</label>
                                    <select name="gender" class="w-full border-gray-300 rounded text-sm font-bold">
                                        <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500">Birthday</label>
                                    <input type="date" name="birthday" value="{{ old('birthday', $student->birthday) }}" required class="w-full border-gray-300 rounded text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Placement Card (Blue Context Section) --}}
                    <div class="bg-blue-900 p-6 rounded shadow-sm text-white">
                        <h2 class="text-[10px] font-black text-blue-300 uppercase mb-4">Current Enrollment Term</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-black uppercase text-blue-200">Change Section</label>
                                <select name="section_id" required class="w-full border-none rounded text-sm font-bold text-gray-900 mt-1">
                                    @foreach($sections as $s)
                                        <option value="{{ $s->id }}" {{ old('section_id', $currentSectionId) == $s->id ? 'selected' : '' }}>
                                            {{ $s->program->name }} » {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-blue-200">Academic Context</p>
                                <p class="text-lg font-black tracking-tighter">
                                    {{ $context['ay']->start_year }}-{{ $context['ay']->end_year }}
                                </p>
                                <p class="text-[10px] font-bold opacity-70">{{ $context['semester'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Column 3: Registrar Checklist --}}
                <div class="space-y-4">
                    <div class="bg-white p-6 rounded shadow-sm border border-gray-200 h-full">
                        <h2 class="text-[10px] font-black text-gray-400 uppercase mb-4 border-b pb-1">Requirement Checklist</h2>
                        <div class="space-y-3">
                            @php
                                $savedReqs = $student->requirements_submitted ?? [];
                                $reqList = [
                                    'Birth Certificate' => 'req_birth_cert',
                                    'Report Card (Form 138)' => 'req_form_138',
                                    'Good Moral Cert.' => 'req_good_moral',
                                    '2x2 ID Pictures' => 'req_picture'
                                ];
                            @endphp

                            @foreach($reqList as $label => $name)
                                <label class="flex items-center gap-3 p-2 bg-gray-50 rounded cursor-pointer hover:bg-blue-50 transition">
                                    <input type="hidden" name="requirements[{{ $label }}]" value="0">
                                    <input type="checkbox" name="requirements[{{ $label }}]" value="1" 
                                        {{ (isset($savedReqs[$label]) && $savedReqs[$label]) ? 'checked' : '' }}
                                        class="rounded text-blue-600 focus:ring-blue-500">
                                    <span class="text-xs font-bold text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <hr class="my-6">

                        <div class="bg-blue-50 p-3 rounded border border-blue-200">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <span class="text-[10px] font-black text-blue-800 uppercase block leading-none">Record Preservation</span>
                                    <span class="text-[8px] text-blue-600 font-bold">Changes are logged for audit purposes.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection