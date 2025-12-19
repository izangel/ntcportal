{{-- resources/views/leave_applications/all.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('All Leave Applications') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">All Leave Applications</h3>
                    <a href="{{ route('leave_applications.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Add New Leave Application
                    </a>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Filter Form --}}
                <form action="{{ route('hr.leave_applications.all') }}" method="GET" class="mb-4 bg-gray-50 p-4 rounded-md shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-label for="employee_id_filter" value="{{ __('Filter by Employee') }}" />
                            <select id="employee_id_filter" name="employee_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->last_name .', '. $employee->first_name .' '. $employee->mid_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label for="type_filter" value="{{ __('Filter by Leave Type') }}" />
                            <select id="type_filter" name="leave_type_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">All Types</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label for="status_filter" value="{{ __('Filter by Status') }}" />
                            <select id="status_filter" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">All Statuses</option>
                                @foreach ($approvalStatuses as $statusOption)
                                    <option value="{{ $statusOption }}" {{ request('status') == $statusOption ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $statusOption)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <x-button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white">
                            {{ __('Apply Filters') }}
                        </x-button>
                        <a href="{{ route('hr.leave_applications.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 ml-2">
                            {{ __('Clear Filters') }}
                        </a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Middle Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Filed</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($leaveApplications as $application)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $application->employee->last_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $application->employee->first_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $application->employee->mid_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucwords(str_replace('_', ' ', $application->leaveType->name)) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ Str::limit($application->reason, 30) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $application->start_date->format('M d, Y') }} - {{ $application->end_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $application->total_days }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if ($application->approval_status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif ($application->approval_status === 'approved_with_pay' || $application->approval_status === 'approved_without_pay') bg-green-100 text-green-800
                                            @elseif ($application->approval_status === 'rejected') bg-red-100 text-red-800
                                            @elseif ($application->approval_status === 'cancelled') bg-gray-100 text-gray-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucwords(str_replace('_', ' ', $application->approval_status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $application->date_filed->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        {{-- View Button --}}
                                        <a href="{{ route('leave_applications.show', $application) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>

                                        {{-- CANCEL BUTTON --}}
                                        {{-- Condition: Visible ONLY for 'approved_with_pay' or 'approved_without_pay' or 'cancelled' --}}
                                        @if ($application->approval_status === 'approved_with_pay' || $application->approval_status === 'approved_without_pay' || $application->approval_status === 'cancelled')
                                            <form action="{{ route('leave_applications.cancel', $application) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to cancel this approved leave? Credits will be refunded.');">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-900 font-bold mr-3">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif

                                        {{-- EDIT & DELETE BUTTONS --}}
                                        {{-- Condition: Visible ONLY for 'pending' --}}
                                        @if ($application->approval_status === 'pending')
                                            <a href="{{ route('leave_applications.edit', $application) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                            <form action="{{ route('leave_applications.destroy', $application) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this leave application? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No leave applications found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $leaveApplications->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection