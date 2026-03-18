@extends('admin.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Personal Information') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                
                {{-- Profile Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-8">
                    <div class="flex items-center">
                        <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center shadow-lg">
                            @if($student)
                                <span class="text-blue-600 font-bold text-2xl">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</span>
                            @elseif($employee)
                                <span class="text-blue-600 font-bold text-2xl">{{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}</span>
                            @else
                                <span class="text-blue-600 font-bold text-2xl">{{ substr($user->name, 0, 2) }}</span>
                            @endif
                        </div>
                        <div class="ml-6">
                            <h3 class="text-2xl font-bold text-white">
                                @if($student)
                                    {{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name ?? '' }}
                                @elseif($employee)
                                    {{ $employee->last_name }}, {{ $employee->first_name }} {{ $employee->middle_name ?? '' }}
                                @else
                                    {{ $user->name }}
                                @endif
                            </h3>
                            <p class="text-blue-100">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'User')) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Information Details --}}
                <div class="p-6">

                    @if(session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    {{-- Account Information --}}
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-user-circle text-blue-600 mr-2"></i>Account Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Username</label>
                                <p class="mt-1 text-gray-900">{{ $user->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email Address</label>
                                <p class="mt-1 text-gray-900">{{ $user->email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Role</label>
                                <p class="mt-1 text-gray-900">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'N/A')) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Account Created</label>
                                <p class="mt-1 text-gray-900">{{ $user->created_at->format('F d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($student)
                        {{-- Student Information --}}
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-graduation-cap text-blue-600 mr-2"></i>Student Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Student ID</label>
                                    <p class="mt-1 text-gray-900">{{ $student->student_id ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Full Name</label>
                                    <p class="mt-1 text-gray-900">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name ?? '' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Date of Birth</label>
                                    <p class="mt-1 text-gray-900">
                                        @if($student->date_of_birth)
                                            {{ \Carbon\Carbon::parse($student->date_of_birth)->format('F d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Program</label>
                                    <p class="mt-1 text-gray-900">{{ $student->program->name ?? 'N/A' }}</p>
                                </div>
                                @php
                                    $currentSection = $student->sections()->latest('pivot_created_at')->first();
                                @endphp
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Section</label>
                                    <p class="mt-1 text-gray-900">{{ $currentSection->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($employee)
                        {{-- Employee Information --}}
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-briefcase text-blue-600 mr-2"></i>Employee Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Full Name</label>
                                    <p class="mt-1 text-gray-900">{{ $employee->last_name }}, {{ $employee->first_name }} {{ $employee->middle_name ?? '' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Position</label>
                                    <p class="mt-1 text-gray-900">{{ ucfirst(str_replace('_', ' ', $employee->role ?? 'N/A')) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Department</label>
                                    <p class="mt-1 text-gray-900">{{ $employee->department->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Phone</label>
                                    <p class="mt-1 text-gray-900">{{ $employee->phone ?? 'N/A' }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-500">Address</label>
                                    <p class="mt-1 text-gray-900">{{ $employee->address ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('profile.personal-information.edit') }}" 
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-edit mr-2"></i> Edit Information
                        </a>
                        <a href="{{ route('password.edit') }}" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
