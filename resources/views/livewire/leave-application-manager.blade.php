<div>
    @if(!$isCreating)
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">
                {{ $isHrRecordingMode ? 'Global Leave Records (HR Management Panel)' : 'My Leave Applications' }}
            </h3>
            <button type="button" wire:click="enterCreateMode" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                + File New Leave Request
            </button>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-sm">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(!$isHrRecordingMode)
            <div class="mb-6 bg-indigo-50 border border-indigo-100 p-4 rounded-md">
                <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-2">Available Leave Credit Balances</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs text-indigo-950">
                    @forelse ($remainingCredits as $type => $credits)
                        <div class="p-2 bg-white rounded shadow-sm border border-indigo-50">
                            <span class="font-medium block text-gray-400">{{ Str::of($type)->replace('_', ' ')->title() }}</span>
                            <span class="text-sm font-bold text-gray-800">{{ $credits }} Days Remaining</span>
                        </div>
                    @empty
                        <p class="text-gray-500 italic">No tracking credit parameters linked to your workforce file.</p>
                    @endforelse
                </div>
            </div>
        @endif

        <div class="mb-4 bg-gray-50 p-4 rounded-md border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase">Academic Year</label>
                    <select wire:model.live="filter_academic_year_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-sm shadow-sm">
                        <option value="">All Academic Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->start_year }}-{{ $year->end_year }} {{ $year->is_active ? '(Active)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase">Filter by Leave Type</label>
                    <select wire:model.live="filter_leave_type_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-sm shadow-sm">
                        <option value="">All Types</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase">Filter by Status</label>
                    <select wire:model.live="filter_status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-sm shadow-sm">
                        <option value="">All Statuses</option>
                        @foreach($approvalStatuses as $stat)
                            <option value="{{ $stat }}">{{ ucwords(str_replace('_', ' ', $stat)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if($filter_leave_type_id || $filter_status || $filter_academic_year_id != ($activeAcademicYear->id ?? ''))
                <div class="flex justify-end mt-2">
                    <button type="button" wire:click="clearFilters" class="text-xs text-red-600 font-medium hover:underline">Clear Active Filters</button>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @if($isHrRecordingMode)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aca. Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason Context</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Schedule Range</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Audit / Remarks</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" wire:loading.class="opacity-50">
                    @forelse ($leaveApplications as $application)
                        <tr wire:key="application-row-{{ $application->id }}">
                            @if($isHrRecordingMode)
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ $application->employee->last_name }}, {{ $application->employee->first_name }}
                                </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">
                                {{ $application->academicYear->start_year ?? 'N/A' }}- {{ $application->academicYear->end_year ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $application->leaveType->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $application->reason }}">{{ $application->reason }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ Carbon\Carbon::parse($application->start_date)->format('M d, Y') }} - {{ Carbon\Carbon::parse($application->end_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ max(0, (int) $application->total_days) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if ($application->approval_status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif ($application->approval_status === 'approved_with_pay' || $application->approval_status === 'approved_without_pay') bg-green-100 text-green-800
                                    @elseif ($application->approval_status === 'rejected') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucwords(str_replace('_', ' ', $application->approval_status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                {{ $application->hr_remarks ?? '--' }}
                            </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('leave_applications.show', $application) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">View</a>
                            
                            @if($application->approval_status === 'pending')
                                <button type="button" wire:click="edit({{ $application->id }})" class="text-amber-600 hover:text-amber-900 font-semibold">
                                    Edit
                                </button>
                            @endif
                        </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-sm text-gray-500 text-center italic">No matched data sets corresponding to specified variables.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $leaveApplications->links() }}</div>

    @else
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h3 class="text-lg font-medium text-gray-900">
                {{ $isHrRecordingMode ? 'HR Administrative Direct Leave Recording Entry Panel' : 'New Leave Application Submission Request Form' }}
            </h3>
            <a href="{{ route('leave_applications.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-md uppercase tracking-wider transition decoration-none">
                Cancel & Return
            </a>
        </div>

        @if(!$activeAcademicYear)
            <div class="p-4 mb-6 border-l-4 border-red-500 bg-red-50 text-red-700 rounded-r-md text-sm">
                <strong>System Configuration Fault:</strong> Leave entry requests are currently disabled because an active structural school year session allocation link is missing from database configurations.
            </div>
        @else
            <div class="p-3 mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 text-xs rounded-r-md shadow-inner">
                Processing allocations tracked directly into configured Active Term: <strong>{{ $activeAcademicYear->start_year }}-{{ $activeAcademicYear->end_year }}</strong>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 mb-6 border-l-4 border-red-500 bg-red-50 text-red-700 rounded-r-md text-sm">
                <p class="font-bold">Execution blocked. Please fix the following invalid parameters:</p>
                <ul class="mt-1 list-disc list-inside">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($isHrRecordingMode)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Employee Account File</label>
                        <select wire:model.live="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('employee_id') border-red-400 focus:ring-red-400 @enderror">
                            <option value="">-- Select Target Profile --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Leave Type Category</label>
                    <select wire:model.live="leave_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('leave_type_id') border-red-400 focus:ring-red-400 @enderror">
                        <option value="">-- Select Type --</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($employee_id && $leave_type_id)
                <div class="mt-4 p-3 bg-gray-50 border rounded-md flex justify-between text-xs font-semibold text-gray-600 shadow-inner">
                    <span>Account Profile Balance: <strong class="text-indigo-600">{{ $availableCredits }} Credits</strong></span>
                    <span>Computed Request Duration: <strong class="{{ $total_days > $availableCredits && !$isHrRecordingMode ? 'text-red-600 font-bold' : 'text-green-600' }}">{{ $total_days }} Billing Days</strong></span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" wire:model.live="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" wire:model.live="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Reason Matrix Documentation Statement</label>
                <textarea wire:model="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Provide detailed operational reason contexts..."></textarea>
            </div>

            @if($isHrRecordingMode)
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <h4 class="font-bold text-yellow-800 text-sm mb-4 uppercase tracking-wider">Administrative Verification Settings Override</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase">Set Final Approval Status Mapping</label>
                            <select wire:model="approval_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                @foreach($approvalStatuses as $status)
                                    <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase">Internal Administrative File Notes</label>
                            <input type="text" wire:model="hr_remarks" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Provide tracking audit markers...">
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-6 border-t pt-6">
                    <h3 class="text-md font-medium text-gray-900 mb-4">Coverage Handover Specifics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">(For Admin/Staff Operations) Interim Operations Coverage Personnel</label>
                            <select wire:model="personnel_to_take_over_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-- Select Alternate Employee --</option>
                                @foreach($staffPersonnel as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->last_name }}, {{ $staff->first_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Endorsed Tasks Specifics Summary</label>
                            <input type="text" wire:model="tasks_endorsed" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="E.g., processing pending payroll updates...">
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700">Classes to Miss & Alternate Substitute Allocations</label>
                            <button type="button" wire:click="addClassRow" class="px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded hover:bg-gray-700 transition">+ Add Class Row</button>
                        </div>
                        @foreach($classes_data as $index => $classRow)
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-2 items-center mb-2 p-2 border border-dashed rounded bg-gray-50" wire:key="class-row-{{ $index }}">
                                <input type="text" wire:model="classes_data.{{$index}}.course_code" placeholder="Course Code" class="rounded border-gray-300 text-sm w-full">
                                <input type="text" wire:model="classes_data.{{$index}}.title" placeholder="Class Title" class="rounded border-gray-300 text-sm w-full">
                                <input type="text" wire:model="classes_data.{{$index}}.day_time_room" placeholder="Day/Time/Room" class="rounded border-gray-300 text-sm w-full">
                                <select wire:model="classes_data.{{$index}}.substitute_teacher_id" class="rounded border-gray-300 text-sm w-full">
                                    <option value="">-- Substitute --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->last_name }}, {{ $teacher->first_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="removeClassRow({{ $index }})" class="text-xs text-red-600 font-bold hover:underline text-left md:text-center">Remove</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-8 pt-4 border-t flex justify-end">
                <button type="submit" @if(!$activeAcademicYear) disabled @endif class="px-6 py-2 bg-indigo-600 text-white font-semibold text-sm rounded shadow hover:bg-indigo-700 disabled:opacity-50 transition">
                    Commit & Save Leave Application
                </button>
            </div>
        </form>
    @endif
</div>