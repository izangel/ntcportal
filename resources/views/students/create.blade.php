@extends('layouts.plain')

@section('content')
<div class="p-4 bg-gray-100 min-h-screen font-sans">
   {{-- Success & Error Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 border-l-4 border-green-600 text-green-700 text-[11px] font-black uppercase rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 border-l-4 border-red-600 text-red-700 text-[11px] font-black uppercase rounded shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation Errors (e.g., required fields) --}}
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
        <form action="{{ route('students.store') }}" method="POST">
            @csrf
            
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-black text-blue-900 uppercase tracking-tighter">New Student Enrollment</h1>
                <div class="flex gap-2">
                    <a href="{{ route('students.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-[10px] font-black uppercase hover:bg-gray-300">Cancel</a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded text-[10px] font-black uppercase shadow-lg hover:bg-blue-700 transition">Confirm Enrollment</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                
                {{-- Column 1 & 2: Personal Information --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white p-6 rounded shadow-sm border border-gray-200">
                        <h2 class="text-[10px] font-black text-gray-400 uppercase mb-4 border-b pb-1">Basic Identification</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="text-[10px] font-black uppercase text-gray-500">Manual Student ID (Optional)</label>
                                <input type="text" name="student_id" placeholder="Leave blank for Auto-Gen" class="w-full border-gray-300 rounded text-sm bg-gray-50">
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase text-gray-500">First Name</label>
                                <input type="text" name="first_name" required class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase text-gray-500">Last Name</label>
                                <input type="text" name="last_name" required class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase text-gray-500">Middle Name</label>
                                <input type="text" name="middle_name" class="w-full border-gray-300 rounded text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500">Gender</label>
                                    <select name="gender" class="w-full border-gray-300 rounded text-sm font-bold">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500">Birthday</label>
                                    <input type="date" name="birthday" required class="w-full border-gray-300 rounded text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Placement Card --}}
                    <div class="bg-blue-900 p-6 rounded shadow-sm text-white">
                        <h2 class="text-[10px] font-black text-blue-300 uppercase mb-4">Current Enrollment Term</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-black uppercase text-blue-200">Assign Section</label>
                                <select name="section_id" required class="w-full border-none rounded text-sm font-bold text-gray-900 mt-1">
                                    <option value="">-- Choose Section --</option>
                                    @foreach($sections as $s)
                                        <option value="{{ $s->id }}">{{ $s->program->name }} » {{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-blue-200">Academic Context</p>
                                <p class="text-lg font-black tracking-tighter">{{ $context['ay']->start_year }}-{{ $context['ay']->end_year }}</p>
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
                            <label class="flex items-center gap-3 p-2 bg-gray-50 rounded cursor-pointer hover:bg-blue-50 transition">
                                <input type="checkbox" name="req_birth_cert" class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-bold text-gray-700">Birth Certificate</span>
                            </label>
                            <label class="flex items-center gap-3 p-2 bg-gray-50 rounded cursor-pointer hover:bg-blue-50 transition">
                                <input type="checkbox" name="req_form_138" class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-bold text-gray-700">Report Card (Form 138)</span>
                            </label>
                            <label class="flex items-center gap-3 p-2 bg-gray-50 rounded cursor-pointer hover:bg-blue-50 transition">
                                <input type="checkbox" name="req_good_moral" class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-bold text-gray-700">Good Moral Cert.</span>
                            </label>
                            <label class="flex items-center gap-3 p-2 bg-gray-50 rounded cursor-pointer hover:bg-blue-50 transition">
                                <input type="checkbox" name="req_picture" class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-bold text-gray-700">2x2 ID Pictures</span>
                            </label>
                        </div>

                        <hr class="my-6">

                        <div class="bg-green-50 p-3 rounded border border-green-200">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_fully_enrolled" checked class="rounded text-green-600">
                                <div>
                                    <span class="text-[10px] font-black text-green-800 uppercase block leading-none">Finalize Enrollment</span>
                                    <span class="text-[8px] text-green-600 font-bold">Mark as officially enrolled</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection