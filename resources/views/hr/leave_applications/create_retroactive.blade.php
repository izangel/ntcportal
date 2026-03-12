@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('File Retroactive Leave Application') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create Retroactive Leave Application</h3>

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('hr.leave_applications.store_retroactive') }}" onsubmit="return validateFormSubmission()">
                    @csrf

                    <!-- Employee Selection -->
                    <div class="mt-4">
                        <x-label for="employee_id" value="{{ __('Employee') }}" />
                        <select id="employee_id" name="employee_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm @error('employee_id') border-red-500 @enderror" required onchange="updateEmployeeLeaveCredits(this.value)">
                            <option value="">-- Select an Employee --</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->last_name }}, {{ $employee->first_name }} ({{ $employee->role }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="employee_id" class="mt-2" />
                    </div>

                    <!-- Leave Type Selection -->
                    <div class="mt-4">
                        <x-label for="leave_type_id" value="{{ __('Leave Type') }}" />
                        <select id="leave_type_id" name="leave_type_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm @error('leave_type_id') border-red-500 @enderror" required onchange="validateLeaveTypeSelection()">
                            <option value="">-- Select a Leave Type --</option>
                            @foreach ($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}" data-leave-type="{{ $leaveType->name }}" {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                    {{ $leaveType->name }}
                                </option>
                            @endforeach
                        </select>
                        <div id="leave_type_error" class="mt-1 text-sm text-red-500 hidden">Please select a valid leave type</div>
                        <x-input-error for="leave_type_id" class="mt-2" />
                    </div>

                    <!-- Display Available Credits -->
                    <div id="credit_balance_container" class="rounded-lg border border-blue-200 bg-blue-50 p-4 hidden mt-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Current Leave Credits</h4>
                        <div id="credit_balance" class="text-sm text-blue-800">
                            -- Select a leave type to view available balance --
                        </div>
                    </div>

                    <!-- Start Date -->
                    <div class="mt-4">
                        <x-label for="start_date" value="{{ __('Start Date (Past Date)') }}" />
                        <x-input id="start_date" class="block mt-1 w-full @error('start_date') border-red-500 @enderror" type="date" name="start_date" :value="old('start_date')" required max="{{ date('Y-m-d') }}" />
                        <x-input-error for="start_date" class="mt-2" />
                        <p class="text-xs text-gray-500 mt-1">Must be a past date</p>
                    </div>

                    <!-- End Date -->
                    <div class="mt-4">
                        <x-label for="end_date" value="{{ __('End Date (Past Date)') }}" />
                        <x-input id="end_date" class="block mt-1 w-full @error('end_date') border-red-500 @enderror" type="date" name="end_date" :value="old('end_date')" required max="{{ date('Y-m-d') }}" />
                        <x-input-error for="end_date" class="mt-2" />
                        <p class="text-xs text-gray-500 mt-1">Must be on or after start date and in the past</p>
                    </div>

                    <!-- Total Days (Auto-calculated) -->
                    <div class="mt-4">
                        <x-label for="total_days" value="{{ __('Total Days') }}" />
                        <x-input id="total_days" class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="number" name="total_days" :value="old('total_days', 0)" disabled />
                        <p class="text-xs text-gray-500 mt-1">Automatically calculated from dates</p>
                    </div>
                    <!-- Exceeds Credits Notification -->
                    <div id="retro_exceeds_container" class="hidden mt-2 rounded-md border border-red-200 bg-red-50 p-3">
                        <p id="retro_exceeds_message" class="text-sm text-red-800"></p>
                    </div>

                    <!-- Reason -->
                    <div class="mt-4">
                        <x-label for="reason" value="{{ __('Reason for Absence') }}" />
                        <textarea id="reason" name="reason" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm @error('reason') border-red-500 @enderror" rows="4" required>{{ old('reason') }}</textarea>
                        <x-input-error for="reason" class="mt-2" />
                    </div>

                    <!-- Approval Status (With Pay / Without Pay) -->
                    <div class="mt-4">
                        <x-label for="approval_status" value="{{ __('Approval Status') }}" />
                        <select id="approval_status" name="approval_status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm @error('approval_status') border-red-500 @enderror" required>
                            <option value="">-- Select Approval Status --</option>
                            <option value="approved_with_pay" {{ old('approval_status') == 'approved_with_pay' ? 'selected' : '' }}>Approved With Pay</option>
                            <option value="approved_without_pay" {{ old('approval_status') == 'approved_without_pay' ? 'selected' : '' }}>Approved Without Pay</option>
                        </select>
                        <x-input-error for="approval_status" class="mt-2" />
                    </div>

                    <!-- HR Remarks (Optional) -->
                    <div class="mt-4">
                        <x-label for="hr_remarks" value="{{ __('HR Remarks (Optional)') }}" />
                        <textarea id="hr_remarks" name="hr_remarks" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('hr_remarks', 'Filed by HR') }}</textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('hr.leave_applications.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-600 focus:outline-none transition ease-in-out duration-150 mr-3">
                            {{ __('Cancel') }}
                        </a>
                        <x-button>
                            {{ __('File Retroactive Leave') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let employeeCredits = {};
        const holidays = [
            '2025-12-30','2025-12-31',
            '2026-01-01','2026-01-02','2026-02-25','2026-04-02','2026-04-03','2026-04-04',
            '2026-04-09','2026-05-01','2026-06-12','2026-08-21','2026-08-31',
            '2026-11-01','2026-11-02','2026-11-30','2026-12-08','2026-12-24','2026-12-25',
            '2026-12-30','2026-12-31',
        ];

        function formatDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function parseDateInput(value) {
            // Avoid timezone shifts from `new Date('YYYY-MM-DD')` (parsed as UTC).
            if (!value) return null;
            const parts = value.split('-').map((p) => parseInt(p, 10));
            if (parts.length !== 3 || parts.some((n) => Number.isNaN(n))) return null;
            const [y, m, d] = parts;
            // Use local noon to avoid DST edge cases around midnight.
            return new Date(y, m - 1, d, 12, 0, 0, 0);
        }
        function isWeekday(date) {
            const day = date.getDay();
            return day !== 0 && day !== 6;
        }
        function isHoliday(date) {
            return holidays.includes(formatDate(date));
        }

        // Auto-calculate total days when dates change
        function calculateTotalDays() {
            const startVal = document.getElementById('start_date').value;
            const endVal = document.getElementById('end_date').value;
            const startDate = parseDateInput(startVal);
            const endDate = parseDateInput(endVal);

            if (startDate && endDate && endDate >= startDate) {
                let workDays = 0;
                const current = new Date(startDate);
                current.setHours(12, 0, 0, 0);
                while (current <= endDate) {
                    if (isWeekday(current) && !isHoliday(current)) {
                        workDays++;
                    }
                    current.setDate(current.getDate() + 1);
                    current.setHours(12, 0, 0, 0);
                }
                document.getElementById('total_days').value = workDays;
                checkExceedsCredits();
            } else {
                document.getElementById('total_days').value = 0;
                hideExceeds();
            }
        }

        document.getElementById('start_date').addEventListener('change', calculateTotalDays);
        document.getElementById('end_date').addEventListener('change', calculateTotalDays);
        calculateTotalDays();

        function validateLeaveTypeSelection() {
            const leaveTypeSelect = document.getElementById('leave_type_id');
            const leaveTypeError = document.getElementById('leave_type_error');
            
            if (leaveTypeSelect.value === '') {
                leaveTypeError.classList.remove('hidden');
            } else {
                leaveTypeError.classList.add('hidden');
            }
            
            updateBalanceDisplay();
        }

        function validateFormSubmission() {
            const leaveTypeSelect = document.getElementById('leave_type_id');
            const leaveTypeError = document.getElementById('leave_type_error');
            const totalDaysEl = document.getElementById('total_days');
            const leaveTypeId = leaveTypeSelect.value;
            const totalDays = parseInt(totalDaysEl.value || '0', 10);
            
            // Check if leave type is selected
            if (leaveTypeSelect.value === '') {
                leaveTypeError.classList.remove('hidden');
                leaveTypeSelect.focus();
                return false;
            }
            
            leaveTypeError.classList.add('hidden');
            
            // Client-side validation: ensure credits are sufficient
            const available = employeeCredits && employeeCredits[leaveTypeId] !== undefined
                ? parseInt(employeeCredits[leaveTypeId], 10)
                : null;
            if (available === null) {
                alert('Unable to verify remaining leave credits for the selected employee.');
                return false;
            }
            if (totalDays > available) {
                alert('Insufficient remaining leave credits for the selected leave type.');
                return false;
            }
            return true;
        }

        // Update employee leave credits when employee is selected
        function updateEmployeeLeaveCredits(employeeId) {
            const creditBalanceContainer = document.getElementById('credit_balance_container');
            const leaveTypeSelect = document.getElementById('leave_type_id');

            if (!employeeId) {
                creditBalanceContainer.classList.add('hidden');
                employeeCredits = {};
                return;
            }

            fetch(`/hr/employee-leave-credits/${employeeId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.credits) {
                        employeeCredits = data.credits;
                        // If a leave type is already selected, update the display
                        if (leaveTypeSelect.value) {
                            updateBalanceDisplay();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching credits:', error);
                    creditBalanceContainer.classList.add('hidden');
                });
        }

        // Update balance display when leave type changes
        function updateBalanceDisplay() {
            const employeeId = document.getElementById('employee_id').value;
            const leaveTypeId = document.getElementById('leave_type_id').value;
            const creditBalanceContainer = document.getElementById('credit_balance_container');
            const creditBalanceText = document.getElementById('credit_balance');

            // If no employee or no leave type selected, hide the credits
            if (!employeeId || !leaveTypeId) {
                creditBalanceContainer.classList.add('hidden');
                return;
            }

            // If no credits for this employee, hide the credits
            if (!employeeCredits || Object.keys(employeeCredits).length === 0) {
                creditBalanceContainer.classList.add('hidden');
                return;
            }

            // Get the selected leave type name
            const selectedOption = document.querySelector(`#leave_type_id option[value="${leaveTypeId}"]`);
            if (!selectedOption) {
                creditBalanceContainer.classList.add('hidden');
                return;
            }

            const leaveTypeName = selectedOption.getAttribute('data-leave-type');
            const balance = employeeCredits[leaveTypeId];

            if (balance !== undefined && balance !== null) {
                const balanceInt = parseInt(balance);
                
                if (balanceInt <= 0) {
                    // Show red warning for no credits
                    creditBalanceContainer.classList.remove('bg-blue-50', 'border-blue-200');
                    creditBalanceContainer.classList.add('bg-red-50', 'border-red-200');
                    creditBalanceText.classList.remove('text-blue-800');
                    creditBalanceText.classList.add('text-red-800');
                    creditBalanceText.innerHTML = `<strong class="text-red-900">${leaveTypeName}</strong>: <span class="font-bold">NO CREDITS AVAILABLE</span>`;
                } else {
                    // Show blue info for available credits
                    creditBalanceContainer.classList.remove('bg-red-50', 'border-red-200');
                    creditBalanceContainer.classList.add('bg-blue-50', 'border-blue-200');
                    creditBalanceText.classList.remove('text-red-800');
                    creditBalanceText.classList.add('text-blue-800');
                    creditBalanceText.innerHTML = `<strong>${leaveTypeName}</strong>: <strong>${balanceInt}</strong> days available`;
                }
                
                creditBalanceContainer.classList.remove('hidden');
                checkExceedsCredits();
            } else {
                creditBalanceContainer.classList.add('hidden');
                hideExceeds();
            }
        }

        // Update credits when leave type selection changes
        document.getElementById('leave_type_id').addEventListener('change', updateBalanceDisplay);

        function hideExceeds() {
            const c = document.getElementById('retro_exceeds_container');
            if (c) c.classList.add('hidden');
        }

        function checkExceedsCredits() {
            const leaveTypeId = document.getElementById('leave_type_id').value;
            const totalDays = parseInt(document.getElementById('total_days').value || '0', 10);
            const available = employeeCredits && employeeCredits[leaveTypeId] !== undefined
                ? parseInt(employeeCredits[leaveTypeId], 10)
                : null;
            const c = document.getElementById('retro_exceeds_container');
            const m = document.getElementById('retro_exceeds_message');
            if (!c || !m) return;
            if (available === null || !leaveTypeId) {
                hideExceeds();
                return;
            }
            if (totalDays > available) {
                m.innerText = `Requested ${totalDays} day(s) exceeds available credits (${available}).`;
                c.classList.remove('hidden');
            } else {
                hideExceeds();
            }
        }
    </script>
@endsection
