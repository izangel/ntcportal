@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard') }}
        {{-- Displaying role in header based on custom hasRole method --}}
        @if(Auth::user()->hasRole('academic_head')) {{-- REVISED: NO @role --}}
            <span class="text-sm text-gray-500"> (Academic Head)</span>
        @elseif(Auth::user()->hasRole('hr')) {{-- REVISED: NO @role --}}
            <span class="text-sm text-gray-500"> (HR Manager)</span>
        {{-- Add other roles if you have them --}}
        @endif
    </h2>
@endsection

{{-- Add this at the top if you encounter 'Class 'URL' not found' --}}
@php use Illuminate\Support\Facades\URL; @endphp


@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
              

                {{-- Your Leave Applications Status (for the applicant - visible to all employees) --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-xl text-gray-800">{{ __('Your Leave Applications') }}</h3>
                    </div>
                    <div class="p-6">
                        @if(Auth::user()->employee && Auth::user()->employee->leaveApplications->isNotEmpty())
                            <ul class="space-y-3">
                                @foreach(Auth::user()->employee->leaveApplications->sortByDesc('created_at') as $application)
                                    <li class="p-3 border rounded-lg
                                        @if($application->hr_status === 'approved') bg-green-50 border-green-200
                                        @elseif($application->hr_status === 'rejected') bg-red-50 border-red-200
                                        @elseif($application->ah_status === 'rejected') bg-red-50 border-red-200
                                        @else bg-gray-50 border-gray-200 @endif
                                        ">
                                        <p class="font-medium text-lg">{{ $application->leaveType->name }} from {{ $application->start_date->format('M d, Y') }} to {{ $application->end_date->format('M d, Y') }}</p>
                                        <p class="text-sm text-gray-600">Reason: {{ Str::limit($application->reason, 50) }}</p>

                                        {{-- Academic Head Status --}}
                                        <p class="text-sm font-semibold mt-1">
                                            Academic Head Status:
                                            <span class="uppercase {{ $application->ah_status === 'approved' ? 'text-green-700' : ($application->ah_status === 'rejected' ? 'text-red-700' : 'text-gray-700') }}">{{ $application->ah_status }}</span>
                                        </p>
                                        @if($application->ah_status !== 'pending' && $application->ah_approved_at)
                                            <p class="text-xs text-gray-500 ml-2">Decision on: {{ $application->ah_approved_at->format('M d, Y') }} by {{ $application->ahApprover->name ?? 'Academic Head' }}</p>
                                        @endif
                                        @if($application->ah_remarks)
                                            <p class="text-xs text-gray-500 italic ml-2">Remarks: "{{ $application->ah_remarks }}"</p>
                                        @endif

                                        {{-- HR Status (only shown if AH has approved, or if it's explicitly rejected by AH) --}}
                                        @if($application->ah_status === 'approved' || $application->hr_status !== 'pending')
                                            <p class="text-sm font-semibold mt-2">
                                                HR Status:
                                                <span class="uppercase {{ $application->hr_status === 'approved' ? 'text-green-700' : ($application->hr_status === 'rejected' ? 'text-red-700' : 'text-gray-700') }}">{{ $application->hr_status }}</span>
                                            </p>
                                            @if($application->hr_status !== 'pending' && $application->hr_approved_at)
                                                <p class="text-xs text-gray-500 ml-2">Decision on: {{ $application->hr_approved_at->format('M d, Y') }} by {{ $application->hrApprover->name ?? 'HR' }}</p>
                                            @endif
                                            @if($application->hr_remarks)
                                                <p class="text-xs text-gray-500 italic ml-2">Remarks: "{{ $application->hr_remarks }}"</p>
                                            @endif
                                        @else
                                            <p class="text-sm font-semibold mt-2 text-gray-500">HR Status: <span class="uppercase">Pending HR Approval</span></p>
                                        @endif

                                         {{-- Admin Status (only shown if HR has approved, or if it's explicitly rejected by HR) --}}
                                        @if($application->hr_status === 'approved' || $application->admin_status !== 'pending')
                                            <p class="text-sm font-semibold mt-2">
                                                Admin Status:
                                                <span class="uppercase {{ $application->admin_status === 'approved' ? 'text-green-700' : ($application->admin_status === 'rejected' ? 'text-red-700' : 'text-gray-700') }}">{{ $application->admin_status }}</span>
                                            </p>
                                            @if($application->admin_status !== 'pending' && $application->admin_approved_at)
                                                <p class="text-xs text-gray-500 ml-2">Decision on: {{ $application->admin_approved_at->format('M d, Y') }} by {{ $application->adminApprover->name ?? 'Admin' }}</p>
                                            @endif
                                            @if($application->admin_remarks)
                                                <p class="text-xs text-gray-500 italic ml-2">Remarks: "{{ $application->admin_remarks }}"</p>
                                            @endif
                                        @else
                                            <p class="text-sm font-semibold mt-2 text-gray-500">Admin Status: <span class="uppercase">Pending Admin Approval</span></p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-center text-gray-600">{{ __('You have no leave applications submitted.') }}</p>
                        @endif
                    </div>
                </div>

                
            </div>
        </div>
    </div>
@endsection