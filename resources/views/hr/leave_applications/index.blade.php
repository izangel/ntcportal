@extends('layouts.admin') {{-- Or your main layout file --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('HR Leave Applications Review') }}
    </h2>
@endsection

@section('content')


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="font-semibold text-lg">{{ __('Pending Leave Applications') }}</h3>
                </div>
                <div class="p-6">
                    @if($pendingApplications->isNotEmpty())
                        <ul class="space-y-4">
                            @foreach($pendingApplications as $application)
                                <li class="bg-gray-50 p-4 rounded-lg shadow-sm flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-lg text-gray-800">{{ $application->employee->first_name.' '.$application->employee->last_name  }}</p>
                                        <p class="text-sm text-gray-600">{{ $application->leaveType->name }} from {{ $application->start_date->format('M d, Y') }} to {{ $application->end_date->format('M d, Y') }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Status: <span class="uppercase">{{ $application->hr_status }}</span></p>
                                    </div>
                                    <div>
                                        <a href="{{ URL::signedRoute('hr.leave_applications.review', ['leaveApplication' => $application->id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Review') }}
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-gray-600">{{ __('No pending leave applications for HR review.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection