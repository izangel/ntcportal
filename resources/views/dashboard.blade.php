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
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Welcome, {{ Auth::user()->name }}!</h3>

                {{-- Academic Head Specific Content Section --}}
                @if(Auth::user()->hasRole('academic_head')) {{-- REVISED: Display only for Academic Heads, NO @role --}}
                    {{-- NEW: Link to All Pending Leave Applications --}}
                    <div class="mb-6">
                        <a href="{{ route('ah.leave_applications.all') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('View All Pending Leave Applications (AH)') }}
                        </a>
                    </div>

                {{-- HR Head Specific Content Section --}}
                @elseif(Auth::user()->hasRole('hr')) {{-- REVISED: Display only for HR Heads, NO @role --}}
                    {{-- NEW: Link to All Pending Leave Applications --}}
                    <div class="mb-6">
                        <a href="{{ route('hr.leave_applications.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('View All Pending Leave Applications (HR)') }}
                        </a>
                    </div>
                {{-- Admin Head Specific Content Section --}}
                @elseif(Auth::user()->hasRole('admin')) {{-- REVISED: Display only for Admin Heads, NO @role --}}
                    {{-- NEW: Link to All Pending Leave Applications --}}
                    <div class="mb-6">
                        <a href="{{ route('admin.leave_applications.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('View All Pending Leave Applications (Admin)') }}
                        </a>
                    </div>

                    
                @endif {{-- REVISED: End @if(Auth::user()->hasRole('academic_head')) --}}

                {{-- Your Leave Applications Status (for the applicant - visible to all employees) --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-xl text-gray-800">{{ __('Your Leave Applications') }}</h3>
                         <a href="{{ route('leaveapplicationstatus') }}" class="block p-4 bg-blue-100 rounded-lg shadow hover:bg-blue-200 text-blue-800 text-center font-medium">
                                    Leave Applications
                        </a>
                    </div>
                    
                </div>

                {{-- Your existing Notifications Section (no major changes needed as it handles types) --}}
                @if(Auth::check() && Auth::user()->unreadNotifications->isNotEmpty())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="font-semibold text-xl text-gray-800">{{ __('New Notifications') }}</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                @foreach(Auth::user()->unreadNotifications as $notification)
                                    @php
                                        $data = $notification->data;
                                        $notificationId = $notification->id;
                                    @endphp
                                    <li class="relative p-4 rounded-lg shadow-sm
                                        @if($data['type'] === 'substitute_assignment') bg-blue-50 border border-blue-200
                                        @elseif($data['type'] === 'ah_leave_review') bg-yellow-50 border border-yellow-200
                                        @elseif($data['type'] === 'hr_leave_review') bg-orange-50 border border-orange-200
                                        @elseif($data['type'] === 'leave_decision' && ($data['decision'] ?? '') === 'approved') bg-green-50 border border-green-200
                                        @elseif($data['type'] === 'leave_decision' && ($data['decision'] ?? '') === 'rejected') bg-red-50 border border-red-200
                                        @else bg-gray-50 border border-gray-200 @endif
                                        ">

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
                                                    @if($data['type'] === 'substitute_assignment') text-blue-800
                                                    @elseif($data['type'] === 'ah_leave_review') text-yellow-800
                                                    @elseif($data['type'] === 'hr_leave_review') text-orange-800
                                                    @elseif($data['type'] === 'leave_decision' && ($data['decision'] ?? '') === 'approved') text-green-800
                                                    @elseif($data['type'] === 'leave_decision' && ($data['decision'] ?? '') === 'rejected') text-red-800
                                                    @else text-gray-800 @endif
                                                    break-words">
                                                    {{ $data['title'] ?? 'General Notification' }}
                                                </p>
                                                <p class="text-sm text-gray-700 mt-1 break-words">{{ $data['message'] ?? '' }}</p>
                                            </div>

                                            <div class="flex-shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                                                @if($data['type'] === 'substitute_assignment')
                                                    <a href="{{ $data['acknowledgement_url'] ?? '#' }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                                                        {{ __('Review & Acknowledge') }}
                                                    </a>
                                                @elseif($data['type'] === 'ah_leave_review')
                                                    {{-- Construct a signed URL for AH review --}}
                                                    <a href="{{ URL::signedRoute('ah.leave_applications.review', ['leaveApplication' => $data['leave_application_id']]) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 focus:bg-yellow-500 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                                                        {{ __('Review Leave App (AH)') }}
                                                    </a>
                                                @elseif($data['type'] === 'hr_leave_review')
                                                    {{-- This link assumes HR also has a review route for the specific application --}}
                                                    <a href="{{ URL::signedRoute('hr.leave_applications.review', ['leaveApplication' => $data['leave_application_id']]) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                                                        {{ __('Review Leave App (HR)') }}
                                                    </a>
                                                @elseif($data['type'] === 'admin_leave_review')
                                                   
                                                    {{-- This link assumes Admin also has a review route for the specific application --}}
                                                    <a href="{{ URL::signedRoute('admin.leave_applications.review', ['leaveApplication' => $data['leave_application_id']]) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                                                        {{ __('Review Leave App (Admin)') }}
                                                    </a>
                                                @elseif($data['type'] === 'leave_decision')
                                                    @if(isset($data['view_application_url']))
                                                        <a href="{{ $data['view_application_url'] }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                                                            {{ __('View Application') }}
                                                        </a>
                                                    @endif
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


                {{-- Overview Statistics Cards (Visible to all roles, data from DashboardController) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-blue-500 text-white rounded-lg shadow-md p-5">
                        <div class="text-sm uppercase font-bold">Total Students</div>
                        <div class="text-3xl font-bold">{{ $totalStudents }}</div>
                    </div>
                    <div class="bg-green-500 text-white rounded-lg shadow-md p-5">
                        <div class="text-sm uppercase font-bold">Total Courses</div>
                        <div class="text-3xl font-bold">{{ $totalCourses }}</div>
                    </div>
                    <div class="bg-purple-500 text-white rounded-lg shadow-md p-5">
                        <div class="text-sm uppercase font-bold">Total Enrollments</div>
                        <div class="text-3xl font-bold">{{ $totalEnrollments }}</div>
                    </div>
                    <div class="bg-yellow-500 text-white rounded-lg shadow-md p-5">
                        <div class="text-sm uppercase font-bold">Total Teachers</div>
                        <div class="text-3xl font-bold">{{ $totalTeachers }}</div>
                    </div>
                    <div class="bg-indigo-500 text-white rounded-lg shadow-md p-5">
                        <div class="text-sm uppercase font-bold">Total Programs</div>
                        <div class="text-3xl font-bold">{{ $totalPrograms }}</div>
                    </div>
                    <div class="bg-pink-500 text-white rounded-lg shadow-md p-5">
                        <div class="text-sm uppercase font-bold">Total Sections</div>
                        <div class="text-3xl font-bold">{{ $totalSections }}</div>
                    </div>
                    <div class="bg-gray-700 text-white rounded-lg shadow-md p-5">
                        <div class="text-sm uppercase font-bold">Total Users</div>
                        <div class="text-3xl font-bold">{{ $totalUsers }}</div>
                    </div>
                </div>

                {{-- Recent Activity Section (Visible to all roles, data from DashboardController) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-4">Recently Added Students</h4>
                        <div class="bg-gray-50 rounded-lg shadow-md p-4">
                            @forelse ($recentStudents as $student)
                                <div class="flex justify-between items-center py-2 border-b last:border-b-0 border-gray-200">
                                    <span>{{ $student->name }} ({{ $student->email }})</span>
                                    <span class="text-sm text-gray-500">{{ $student->created_at->diffForHumans() }}</span>
                                </div>
                            @empty
                                <p class="text-gray-600">No recent students.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-4">Recently Added Courses</h4>
                        <div class="bg-gray-50 rounded-lg shadow-md p-4">
                            @forelse ($recentCourses as $course)
                                <div class="flex justify-between items-center py-2 border-b last:border-b-0 border-gray-200">
                                    <span>{{ $course->name }} ({{ $course->code }})</span>
                                    <span class="text-sm text-gray-500">{{ $course->created_at->diffForHumans() }}</span>
                                </div>
                            @empty
                                <p class="text-gray-600">No recent courses.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-4">System Updates</h4>
                        <div class="bg-gray-50 rounded-lg shadow-md p-4">
                            @forelse ($recentStudents as $student)
                               
                            @empty
                                <p class="text-gray-600">No recent students.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection