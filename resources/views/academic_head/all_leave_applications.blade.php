@extends('layouts.admin') {{-- IMPORTANT: Adjust this to your actual main layout file --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('All Department Leave Applications (Academic Head)') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">All Leave Applications for Your Department</h3>

                    @if($leaveApplications->isEmpty())
                        <p class="text-gray-600">No leave applications found for your department at this time.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Employee
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dates
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            AH Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            HR Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($leaveApplications as $application)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $application->employee->user->name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ $application->employee->user->email ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $application->leaveType->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $application->start_date->format('M d, Y') }} - {{ $application->end_date->format('M d, Y') }}</div>
                                                <div class="text-sm text-gray-500">({{ $application->total_days }} days)</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($application->ah_status === 'approved') bg-green-100 text-green-800
                                                    @elseif($application->ah_status === 'rejected') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($application->ah_status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($application->hr_status === 'approved') bg-green-100 text-green-800
                                                    @elseif($application->hr_status === 'rejected') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($application->hr_status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($application->ah_status === 'pending')
                                                    {{-- Link to review page if still pending AH approval --}}
                                                    <a href="{{ URL::signedRoute('ah.leave_applications.review', ['leaveApplication' => $application->id]) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Review</a>
                                                @else
                                                    {{-- Link to view the application if already reviewed --}}
                                                    <a href="{{ URL::signedRoute('ah.leave_applications.review', ['leaveApplication' => $application->id]) }}" class="text-gray-400 cursor-not-allowed" aria-disabled="true">View</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $leaveApplications->links() }} {{-- Renders pagination links --}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    // Required if you use URL::signedRoute in the Blade file directly and get an error
    // @php use Illuminate\Support\Facades\URL; @endphp should be at the very top of the Blade file.
</script>
@endpush