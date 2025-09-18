{{-- resources/views/leave_applications/show.blade.php --}}

@extends('layouts.admin') {{-- Or your main layout file --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Leave Application Details') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Leave Application for {{ $leaveApplication->employee->name ?? 'N/A' }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-6">
                    <div>
                        <p><strong class="text-gray-900">Last Name:</strong> {{ $leaveApplication->employee->last_name ?? 'N/A' }}</p>
                        <p><strong class="text-gray-900">First Name:</strong> {{ $leaveApplication->employee->first_name ?? 'N/A' }}</p>
                        <p><strong class="text-gray-900">Middle Name:</strong> {{ $leaveApplication->employee->mid_name ?? 'N/A' }}</p>
                        <p><strong class="text-gray-900">Employee Role:</strong> {{ ucwords($leaveApplication->employee->role ?? 'N/A') }}</p>
                        <p><strong class="text-gray-900">Leave Type:</strong> {{ $leaveApplication->leaveType->name }}</p>
                        <p><strong class="text-gray-900">Reason:</strong> {{ $leaveApplication->reason }}</p>
                        <p><strong class="text-gray-900">Leave Dates:</strong> {{ $leaveApplication->start_date->format('M d, Y') }} - {{ $leaveApplication->end_date->format('M d, Y') }}</p>
                        <p><strong class="text-gray-900">Total Days:</strong> {{ $leaveApplication->total_days }}</p>
                        <p><strong class="text-gray-900">Date Filed:</strong> {{ $leaveApplication->date_filed->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <p><strong class="text-gray-900">Current Status:</strong>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if ($leaveApplication->approval_status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif ($leaveApplication->approval_status === 'approved_with_pay' || $leaveApplication->approval_status === 'approved_without_pay') bg-green-100 text-green-800
                                @elseif ($leaveApplication->approval_status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucwords(str_replace('_', ' ', $leaveApplication->approval_status)) }}
                            </span>
                        </p>
                        {{-- Future approval timestamps will go here --}}
                    </div>
                </div>

                @if ($leaveApplication->isTeacher())
                    <h4 class="text-md font-medium text-gray-900 mb-2 mt-4">For Teaching Personnel: Classes to be Missed</h4>
                  
                    @if ($leaveApplication->classesToMiss && count($leaveApplication->classesToMiss) > 0)
                        <div class="overflow-x-auto mb-4">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day/Time/Room</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topics</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Substitute Teacher</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($leaveApplication->classesToMiss as $class)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $class['course_code'] ?? '' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $class['title'] ?? '' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $class['day_time_room'] ?? '' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $class['topics'] ?? '' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $class->substituteTeacher->name ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p><strong class="text-gray-900">Acknowledgement of Subject Teacher:</strong> {{ $leaveApplication->acknowledgement_subject_teacher ?? 'N/A' }}</p>
                    @else
                        <p>No classes to miss details provided.</p>
                    @endif
                @elseif ($leaveApplication->isStaff())
                    <h4 class="text-md font-medium text-gray-900 mb-2 mt-4">For Staff: Work Endorsement</h4>
                    <p><strong class="text-gray-900">List of Works/Tasks Endorsed:</strong> {{ $leaveApplication->tasks_endorsed ?? 'N/A' }}</p>
                    <p><strong class="text-gray-900">Personnel to Take Over:</strong> {{ $leaveApplication->personnel_to_take_over ?? 'N/A' }}</p>
                    <p><strong class="text-gray-900">Acknowledgement of Personnel to Take Over:</strong> {{ $leaveApplication->acknowledgement_personnel_take_over ?? 'N/A' }}</p>
                @endif

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('leave_applications.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Back to List
                    </a>
                    @if ($leaveApplication->approval_status === 'pending') {{-- Only allow edit/delete if pending --}}
                        <a href="{{ route('leave_applications.edit', $leaveApplication) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 ml-2">
                            Edit Application
                        </a>
                        <form action="{{ route('leave_applications.destroy', $leaveApplication) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Are you sure you want to delete this leave application? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Delete Application
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection