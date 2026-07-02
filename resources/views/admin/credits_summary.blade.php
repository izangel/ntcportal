@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            
            {{-- Header & Multi-Filter Control Bars --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 italic">Employee Leave Credits Summary</h2>
                    @if($selectedYearId)
                        @if($currentYearObj = $academicYears->firstWhere('id', $selectedYearId))
                            <p class="text-xs text-indigo-600 font-semibold mt-1">
                                Displaying Balances For Term: {{ $currentYearObj->start_year }}-{{ $currentYearObj->end_year }} {{ $currentYearObj->is_active ? '(Active Term)' : '' }}
                            </p>
                        @endif
                    @endif
                </div>
                
                {{-- Integrated Filtering Engines Form Pipeline --}}
                <form method="GET" action="{{ route('leave_credits.summary') }}" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    {{-- Academic Year Selection Filter Dropdown --}}
                    <select name="academic_year_id" onchange="this.form.submit()" 
                            class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                SY {{ $year->start_year }}-{{ $year->end_year }} {{ $year->is_active ? '(Active)' : '' }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Search Input Bar field --}}
                    <div class="flex shadow-sm">
                        <input type="text" name="search" placeholder="Search employee..." value="{{ request('search') }}" 
                               class="rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-md hover:bg-indigo-700 text-sm font-semibold transition">
                            Search
                        </button>
                    </div>

                    @if(request()->filled('search') || request()->filled('academic_year_id'))
                        <a href="{{ route('leave_credits.summary') }}" class="text-xs text-red-500 hover:underline flex items-center justify-center sm:ml-2">
                            Clear Filters
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto border border-gray-100 rounded-lg shadow-inner">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50/70">Vacation</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-green-50/70">Sick</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-orange-50/70">Service Inc.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($summary as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucwords($item['role']) }}</td>
                            
                            {{-- Leave Credit Dynamic Columns Balances Output --}}
                            <td class="px-6 py-4 text-center text-sm font-bold text-blue-700 bg-blue-50/30">
                                {{ $item['credits']['vacation_leave'] ?? 0 }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-green-700 bg-green-50/30">
                                {{ $item['credits']['sick_leave'] ?? 0 }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-orange-700 bg-orange-50/30">
                                {{ $item['credits']['service_incentive_leave'] ?? 0 }}
                            </td>

                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('leave_applications.hr_create', ['employee_id' => $item['id']]) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 font-semibold">Record Leave</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{-- Appends query strings to preserve page pagination state maps correctly during filtering --}}
                {{ $employees->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection