@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('All Employee Leave Credits') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Leave Credits List</h3>

                @if(empty($employeesData))
                    <div class="alert alert-info" role="alert">
                        No employees or leave credit data to display.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee Name</th>
                                    @php
                                        $allLeaveTypes = [];
                                        foreach ($employeesData as $employeeData) {
                                            if (is_array($employeeData['credits'])) {
                                                $allLeaveTypes = array_merge($allLeaveTypes, array_keys($employeeData['credits']));
                                            }
                                        }
                                        $allLeaveTypes = array_unique($allLeaveTypes);
                                    @endphp
                                    @foreach ($allLeaveTypes as $type)
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ Str::of($type)->replace('_', ' ')->title() }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($employeesData as $employeeData)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $employeeData['last_name'].' '. $employeeData['first_name'].' '.$employeeData['mid_name'] }}</td>
                                        @if (is_array($employeeData['credits']))
                                            @foreach ($allLeaveTypes as $type)
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $employeeData['credits'][$type] ?? 'N/A' }}
                                                </td>
                                            @endforeach
                                        @else
                                            <td colspan="{{ count($allLeaveTypes) }}" class="px-6 py-4 text-center text-gray-500">
                                                {{ $employeeData['credits'] }}
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($allLeaveTypes) + 1 }}" class="px-6 py-4 text-center text-gray-500">No employees or leave credit data to display.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection