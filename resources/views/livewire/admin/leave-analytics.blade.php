<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Leave Analytics</h1>
            <p class="text-sm text-gray-600">Insights and trends based on employee leave applications.</p>
        </div>
        @if($summary)
            <button wire:click="exportExcel" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700">
                <i class="fas fa-file-excel"></i>Export Report
            </button>
        @endif
    </div>

    @if(session()->has('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-circle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">From Date</label>
                <input type="date" wire:model.live="startDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">To Date</label>
                <input type="date" wire:model.live="endDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Leave Type</label>
                <select wire:model.live="leaveTypeId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Leave Types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Employee</label>
                <select wire:model.live="employeeId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ trim($employee->last_name . ', ' . $employee->first_name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($summary)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Applications</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['applications'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Days</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['days'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Approved</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $summary['approved'] }} <span class="text-sm font-semibold text-gray-400">({{ $summary['approved_days'] }} days)</span></p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Pending</p>
                <p class="mt-2 text-3xl font-bold text-amber-500">{{ $summary['pending'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Rejected</p>
                <p class="mt-2 text-3xl font-bold text-red-500">{{ $summary['rejected'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Half Days</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['half_days'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Monthly Leave Trend</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Month</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Applications</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Days</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($byMonth as $item)
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $item['month'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['applications'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['days'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Status Breakdown</h3>
                <div class="space-y-3">
                    @foreach($byStatus as $item)
                        @php
                            $pct = $summary['applications'] > 0 ? round(($item['applications'] / $summary['applications']) * 100) : 0;
                            $colors = [
                                'Pending' => 'bg-amber-500',
                                'Approved with Pay' => 'bg-emerald-500',
                                'Approved without Pay' => 'bg-sky-500',
                                'Rejected' => 'bg-red-500',
                                'Cancelled' => 'bg-gray-400',
                            ];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $item['status'] }}</span>
                                <span class="font-semibold text-gray-800">{{ $item['applications'] }}</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $colors[$item['status']] ?? 'bg-indigo-500' }} rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Leave Days by Type</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Leave Type</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Applications</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Days</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($byLeaveType as $item)
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $item['name'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['applications'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['days'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Leave Days by Department</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Department</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Applications</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Days</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($byDepartment as $item)
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $item['department'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['applications'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['days'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Top Employees by Leave Days</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Employee</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Applications</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Days</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topEmployees as $item)
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $item['name'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['applications'] }}</td>
                                    <td class="px-4 py-2.5 text-sm text-center text-gray-600">{{ $item['days'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Overview</h3>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Employees on Leave</span>
                        <span class="font-bold text-gray-800">{{ $summary['employees'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Cancelled Applications</span>
                        <span class="font-bold text-gray-800">{{ $summary['cancelled'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Average Days per Application</span>
                        <span class="font-bold text-gray-800">{{ $summary['applications'] > 0 ? number_format($summary['days'] / $summary['applications'], 2) : '0.00' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Approval Rate</span>
                        <span class="font-bold {{ $summary['applications'] > 0 && ($summary['approved'] / $summary['applications']) >= 0.7 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $summary['applications'] > 0 ? round(($summary['approved'] / $summary['applications']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-chart-simple text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">No leave data available for the selected period.</p>
        </div>
    @endif
</div>
