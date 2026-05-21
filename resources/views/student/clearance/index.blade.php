@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-3xl text-gray-900 leading-tight tracking-tight">
                {{ __('Student Clearance') }}
            </h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-400 font-medium">| Check your clearance status</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="py-10 bg-gray-50/50 min-h-screen">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

        @if(session('status'))
            <div class="bg-white rounded-3xl shadow-sm border border-green-100 p-6">
                <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
            </div>
        @endif

        @if(($isEmployeeView ?? false) && empty($employeeDeptOffice))
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-10 text-center">
                <h3 class="text-2xl font-semibold text-gray-900 mb-4">Department/Office not set</h3>
                <p class="text-gray-600">Department/Office not set</p>
            </div>
        @else
            @if($isEmployeeView ?? false)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Department / Office</h3>
                    <p class="mt-2 text-gray-700">{{ $employeeDeptOffice->name }}</p>
                </div>
            @endif

            @if($isEmployeeView ?? false)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Student Clearance</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Student ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Student Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Section</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($students as $student)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">{{ $student->student_id ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ optional($student->section)->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('employee.clearance.review', $student) }}" class="inline-flex items-center justify-center rounded-full border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No students found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Clearance</h3>

                    @if($clearanceTotalCount > 0)
                        <div class="mb-8">
                            <div class="flex items-center justify-between text-sm font-semibold mb-2">
                                <span>Clearance progress</span>
                                <span>{{ $clearanceApprovedCount }} / {{ $clearanceTotalCount }} completed</span>
                            </div>
                            <div class="h-3 w-full bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-600 transition-all duration-300" style="width: {{ $clearanceProgressPercentage }}%;"></div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">{{ $clearanceProgressPercentage }}% complete</p>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Office / Department</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Action</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Reason for Rejection</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @php
                                    $departments = [
                                        'Registrar\'s Office',
                                        'Guidance Office',
                                        'SHS Adviser',
                                        'Student\'s Accounts Office',
                                        'Laboratory In-charge',
                                        'SHS Organization',
                                        'Supreme Student Government (SSG)',
                                        'Supreme Student Council (SSC)',
                                        'Librarian',
                                        'Prefect of Discipline',
                                        'SHS Coordinator',
                                        'Director for Student Affairs and Services',
                                        'Director of Academic Affairs',
                                        'School Administrator',
                                    ];
                                    $shsOnlyDepartments = ['SHS Adviser', 'SHS Organization', 'Supreme Student Government (SSG)', 'SHS Coordinator'];
                                    $collegeOnlyDepartments = ['Supreme Student Council (SSC)'];
                                @endphp
                                @foreach($departments as $department)
                                    @php
                                        $isShsOnly = in_array($department, $shsOnlyDepartments);
                                        $isCollegeOnly = in_array($department, $collegeOnlyDepartments);
                                        $shouldShowAsNotApplicable = ($isShs && $isCollegeOnly) || ($isCollege && $isShsOnly);
                                        
                                        // Get the mapping for this department
                                        $mapping = $columnMapping[$department] ?? null;
                                        $statusValue = null;
                                        $remarksValue = null;
                                        
                                        if ($mapping && !$shouldShowAsNotApplicable) {
                                            $statusColumn = $mapping['column'];
                                            $remarksColumn = $mapping['remarks'];
                                            
                                            // Check clearanceShs first if applicable
                                            if ($isShs && $mapping['shs'] && $student->clearanceShs) {
                                                $statusValue = $student->clearanceShs->{$statusColumn};
                                                $remarksValue = $student->clearanceShs->{$remarksColumn};
                                            }
                                            
                                            // Check clearanceCollege if no value found and applicable
                                            if (!$statusValue && $isCollege && $mapping['college'] && $student->clearanceCollege) {
                                                $statusValue = $student->clearanceCollege->{$statusColumn};
                                                $remarksValue = $student->clearanceCollege->{$remarksColumn};
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">{{ $department }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($shouldShowAsNotApplicable)
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Not Applicable</span>
                                            @elseif($statusValue === 'approved')
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Completed</span>
                                            @elseif($statusValue === 'pending')
                                                <button type="button" disabled class="inline-flex items-center justify-center rounded-full border border-transparent bg-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                                                    Pending
                                                </button>
                                            @else
                                                <form method="POST" action="{{ route('student.clearance.sign', urlencode($department)) }}" style="display: inline;" onsubmit="return confirm('Please make sure you have completed the requirements before signing for clearance. Continue?');">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center rounded-full border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                        Sign for Clearance
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($shouldShowAsNotApplicable)
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Not Applicable</span>
                                            @elseif($statusValue === 'approved')
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Approved</span>
                                            @elseif($statusValue === 'rejected')
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Rejected</span>
                                            @elseif($statusValue === 'pending')
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">Pending</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Not yet Applied</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $remarksValue ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

    </div>
</div>
@endsection