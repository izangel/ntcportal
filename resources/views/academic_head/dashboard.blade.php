@extends('layouts.admin') {{-- IMPORTANT: Adjust this to your actual main layout file --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Academic Head Dashboard') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Welcome, {{ Auth::user()->name }}!</h3>

                {{-- NEW: Link to All Pending Leave Applications --}}
                <div class="mb-6">
                    <a href="{{ route('ah.leave_applications.all') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('View All Pending Leave Applications') }}
                    </a>
                </div>

                {{-- Your Leave Applications Status (This section is usually for employees viewing their own apps,
                    might not be relevant on an AH dashboard unless the AH is also an employee who applies for leave) --}}
                {{-- Example:
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-xl text-gray-800">{{ __('Your Leave Applications') }}</h3>
                    </div>
                    <div class="p-6">
                        @if(Auth::user()->employee && Auth::user()->employee->leaveApplications->isNotEmpty())
                            ... your existing code for displaying employee's own applications ...
                        @else
                            <p class="text-center text-gray-600">{{ __('You have no leave applications submitted.') }}</p>
                        @endif
                    </div>
                </div>
                --}}

                {{-- Display New Notifications Section --}}
                @if($notifications->isNotEmpty()) {{-- Using $notifications passed from controller --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="font-semibold text-xl text-gray-800">{{ __('New Notifications') }}</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                @foreach($notifications as $notification)
                                    @php
                                        $data = $notification->data;
                                        $notificationId = $notification->id;
                                    @endphp
                                    <li class="relative p-4 rounded-lg shadow-sm
                                        @if(($data['type'] ?? '') === 'substitute_assignment') bg-blue-50 border border-blue-200
                                        @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationSubmittedForAH') bg-yellow-50 border border-yellow-200
                                        @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationApprovedByAH') bg-orange-50 border border-orange-200
                                        @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationDecision' && ($data['decision'] ?? '') === 'approved') bg-green-50 border border-green-200
                                        @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationDecision' && ($data['decision'] ?? '') === 'rejected') bg-red-50 border border-red-200
                                        @else bg-gray-50 border border-gray-200 @endif
                                        ">

                                        {{-- Mark as Read Button (X icon) --}}
                                        <form method="POST" action="{{ route('notifications.markAsRead', $notificationId) }}" class="absolute top-2 right-2 z-20">
                                            @csrf
                                            <button type="submit" class="text-gray-400 hover:text-gray-600 transition duration-150 ease-in-out p-1 rounded-full hover:bg-gray-200">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>

                                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pr-10">
                                            <div class="flex-1 min-w-0 mb-2 sm:mb-0">
                                                <p class="font-medium text-lg
                                                    @if(($data['type'] ?? '') === 'substitute_assignment') text-blue-800
                                                    @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationSubmittedForAH') text-yellow-800
                                                    @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationApprovedByAH') text-orange-800
                                                    @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationDecision' && ($data['decision'] ?? '') === 'approved') text-green-800
                                                    @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationDecision' && ($data['decision'] ?? '') === 'rejected') text-red-800
                                                    @else text-gray-800 @endif
                                                    break-words">
                                                    {{ $data['title'] ?? 'General Notification' }}
                                                </p>
                                                <p class="text-sm text-gray-700 mt-1 break-words">{{ $data['message'] ?? '' }}</p>
                                            </div>

                                            <div class="flex-shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                                                @if(($data['type'] ?? '') === 'substitute_assignment' && isset($data['acknowledgement_url']))
                                                    <a href="{{ $data['acknowledgement_url'] }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                                                        {{ __('Review & Acknowledge') }}
                                                    </a>
                                                @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationSubmittedForAH' && isset($data['leave_application_id']))
                                                    {{-- Construct a signed URL for AH review --}}
                                                    <a href="{{ URL::signedRoute('ah.leave_applications.review', ['leaveApplication' => $data['leave_application_id']]) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 focus:bg-yellow-500 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                                                        {{ __('Review Leave App (AH)') }}
                                                    </a>
                                                @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationSubmittedForHR' && isset($data['leave_application_id']))
                                                    {{-- This notification is for HR, but AH might see it too. Adjust if not needed. --}}
                                                    <a href="{{ URL::signedRoute('hr.leave_applications.review', ['leaveApplication' => $data['leave_application_id']]) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                                                        {{ __('Review Leave App (HR)') }}
                                                    </a>
                                                @elseif(($data['type'] ?? '') === 'App\Notifications\LeaveApplicationDecision' && isset($data['view_application_url']))
                                                    <a href="{{ $data['view_application_url'] }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                                                        {{ __('View Application') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <form method="POST" action="{{ route('notifications.markAllAsRead') }}" class="mt-4 text-center">
                                @csrf
                                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-900 font-semibold">Mark all as read</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 border-t border-gray-200 text-center text-gray-600">
                            {{ __('You have no new notifications at this time.') }}
                        </div>
                    </div>
                @endif


                {{-- Pending Leave Applications for AH Review --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-xl text-gray-800">{{ __('Pending Leave Applications for Your Review') }}</h3>
                    </div>
                    <div class="p-6">
                        @if($pendingApplications->isNotEmpty())
                            <ul class="space-y-3">
                                @foreach($pendingApplications as $application)
                                    <li class="p-3 border rounded-lg bg-yellow-50 border-yellow-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-lg">{{ $application->employee->first_name ?? 'N/A' }} {{ $application->employee->last_name ?? 'N/A' }} - {{ $application->leaveType->name }}</p>
                                                <p class="text-sm text-gray-600">{{ $application->start_date->format('M d, Y') }} to {{ $application->end_date->format('M d, Y') }}</p>
                                                <p class="text-xs text-gray-500">Submitted: {{ $application->created_at->diffForHumans() }}</p>
                                            </div>
                                            <div>
                                                {{-- Link to review page using the signed route helper --}}
                                                <a href="{{ URL::signedRoute('ah.leave_applications.review', ['leaveApplication' => $application->id]) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 focus:bg-yellow-500 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    {{ __('Review') }}
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-center text-gray-600">{{ __('No pending leave applications for your review at this time.') }}</p>
                        @endif
                    </div>
                </div>

               

            </div>
        </div>
    </div>
@endsection

{{-- Add this if you get an error about URL::signedRoute not found --}}
@push('scripts')
<script>
    // This script block is primarily to demonstrate where to put JS if needed.
    // The URL::signedRoute is a Blade directive, not JS.
</script>
@endpush