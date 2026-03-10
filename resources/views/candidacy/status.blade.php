@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application Status') }}
        </h2>
        <span class="text-sm text-gray-500 font-medium">{{ now()->format('l, F j, Y') }}</span>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-gray-900">Candidacy Application Status</h3>
                <p class="mt-2 text-gray-600">Track the progress of your SSG candidacy application.</p>
            </div>

            @if($application)
                {{-- Status Card --}}
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Current Status</p>
                            @if($application->status == 'pending')
                                <p class="text-xl font-semibold text-yellow-600">Pending Review</p>
                            @elseif($application->status == 'approved')
                                <p class="text-xl font-semibold text-green-600">Approved</p>
                            @else
                                <p class="text-xl font-semibold text-red-600">Rejected</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Submitted On</p>
                            <p class="text-lg font-medium text-gray-800">{{ $application->submitted_at ? $application->submitted_at->format('M d, Y') : $application->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Application Details --}}
                <div class="bg-blue-50 rounded-lg p-6 mb-8">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Application Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="font-medium text-gray-900">{{ $application->student->last_name ?? '' }}, {{ $application->student->first_name ?? '' }} {{ $application->student->middle_name ?? '' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Position Applied</p>
                            <p class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $application->position_applied)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Partylist</p>
                            <p class="font-medium text-gray-900">
                                @if($application->is_independent)
                                    <span class="italic">Independent</span>
                                @else
                                    {{ $application->partylist ?? 'N/A' }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Academic Year</p>
                            <p class="font-medium text-gray-900">{{ $application->academic_year ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Progress Timeline --}}
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Application Progress</h4>
                    <div class="space-y-4">
                        {{-- Step 1: Submitted --}}
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <i class="fas fa-check text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Application Submitted</p>
                                <p class="text-sm text-gray-500">Your application has been submitted for review.</p>
                            </div>
                        </div>

                        {{-- Step 2: Under Review --}}
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full {{ $application->status == 'pending' ? 'bg-yellow-500' : ($application->status == 'approved' ? 'bg-green-500' : 'bg-red-500') }} flex items-center justify-center">
                                    @if($application->status == 'pending')
                                        <i class="fas fa-clock text-white text-sm"></i>
                                    @elseif($application->status == 'approved')
                                        <i class="fas fa-check text-white text-sm"></i>
                                    @else
                                        <i class="fas fa-times text-white text-sm"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Under Review</p>
                                <p class="text-sm text-gray-500">The OSA is reviewing your application.</p>
                            </div>
                        </div>

                        {{-- Step 3: Decision --}}
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full {{ $application->status == 'approved' ? 'bg-green-500' : ($application->status == 'rejected' ? 'bg-red-500' : 'bg-gray-300') }} flex items-center justify-center">
                                    @if($application->status == 'approved')
                                        <i class="fas fa-check text-white text-sm"></i>
                                    @elseif($application->status == 'rejected')
                                        <i class="fas fa-times text-white text-sm"></i>
                                    @else
                                        <i class="fas fa-hourglass-half text-white text-sm"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4">
                                @if($application->status == 'approved')
                                    <p class="text-sm font-medium text-green-600">Approved</p>
                                    <p class="text-sm text-gray-500">Your candidacy has been approved! You are now an official candidate.</p>
                                @elseif($application->status == 'rejected')
                                    <p class="text-sm font-medium text-red-600">Rejected</p>
                                    <p class="text-sm text-gray-500">{{ $application->remarks ?? 'Your application was not approved.' }}</p>
                                @else
                                    <p class="text-sm font-medium text-gray-400">Awaiting Decision</p>
                                    <p class="text-sm text-gray-500">Your application is still being processed.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($application->remarks && $application->status == 'rejected')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-red-800 mb-2">Rejection Reason:</h4>
                    <p class="text-sm text-red-700">{{ $application->remarks }}</p>
                </div>
                @endif

            @else
                {{-- No Application Message --}}
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg mb-4">No candidacy application found.</p>
                    <p class="text-gray-400 text-sm mb-6">You haven't submitted a candidacy application yet.</p>
                    <a href="{{ route('student.candidacy.index') }}" 
                        class="inline-block px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-medium">
                        <i class="fas fa-file-alt mr-2"></i> Apply Now
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
