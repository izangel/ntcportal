<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create Retroactive Leave Application</h3>

            @if (session()->has('error'))
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 mb-6 border-l-4 border-red-500 bg-red-50 text-red-700 rounded-r-md text-sm">
                    <p class="font-bold">Execution blocked. Please fix the following invalid parameters:</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form wire:submit.prevent="submit">
                <!-- Employee Selection -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Employee</label>
                    <select wire:model.live="employee_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">-- Select an Employee --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">
                                {{ $employee->last_name }}, {{ $employee->first_name }} ({{ $employee->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Leave Type Selection -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Leave Type</label>
                    <select wire:model.live="leave_type_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">-- Select a Leave Type --</option>
                        @foreach ($leaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Available Credits Display Banner -->
                @if($employee_id && $leave_type_id)
                    <div class="mt-4 p-3 bg-indigo-50 border border-indigo-100 rounded-md flex justify-between text-xs font-semibold text-indigo-950 shadow-inner">
                        <span>Available Leave Credit Balance: <strong class="text-indigo-600 text-sm">{{ $availableCredits }} Days Remaining</strong></span>
                        <span>Computed Request Duration: <strong class="{{ $total_days > $availableCredits ? 'text-red-600 font-bold' : 'text-green-600' }}">{{ $total_days }} Working Days</strong></span>
                    </div>
                @endif

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date (Past Date)</label>
                        <input type="date" wire:model.live="start_date" max="{{ date('Y-m-d') }}" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        <p class="text-xs text-gray-500 mt-1">Must be a past date</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date (Past Date)</label>
                        <input type="date" wire:model.live="end_date" max="{{ date('Y-m-d') }}" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        <p class="text-xs text-gray-500 mt-1">Must be on or after start date and in the past</p>
                    </div>
                </div>

                <!-- Exceeds Credits Notification -->
                @if($total_days > $availableCredits && $employee_id && $leave_type_id)
                    <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-3">
                        <p class="text-sm text-red-800">
                            <strong>Warning:</strong> Requested duration ({{ $total_days }} days) exceeds available balance ({{ $availableCredits }} days).
                        </p>
                    </div>
                @endif

                <!-- Reason -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Reason for Absence</label>
                    <textarea wire:model="reason" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3" required></textarea>
                </div>

                <!-- Approval Status -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Approval Status</label>
                    <select wire:model="approval_status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="approved_with_pay">Approved With Pay</option>
                        <option value="approved_without_pay">Approved Without Pay</option>
                    </select>
                </div>

                <!-- HR Remarks -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">HR Remarks (Optional)</label>
                    <textarea wire:model="hr_remarks" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end mt-6">
                    <a href="{{ route('hr.leave_applications.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 transition mr-3">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        File Retroactive Leave
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>