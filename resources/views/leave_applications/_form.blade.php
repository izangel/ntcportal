{{-- resources/views/leave_applications/_form.blade.php --}}

@props(['leaveApplication' => null, 'employees', 'loggedInEmployee' => null, 'teachers', 'staffPersonnel', 'leaveTypes', 'remainingCredits'])

@php
    // Check if the current user is HR to toggle the "HR Filing Mode"
    $isHrFiling = auth()->user()->hasRole('hr');
@endphp

<div class="space-y-6">
    {{-- 1. Employee Selection --}}
    <div>
        <x-label for="employee_id" value="{{ __('Employee') }}" />
        
        @if($isHrRecordingMode)
            {{-- HR is recording for someone else --}}
            <select id="employee_id" name="employee_id" class="mt-1 block w-full ...">
                <option value="">-- Select Employee --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                @endforeach
            </select>
        @else
            {{-- HR (or anyone else) is filing for themselves --}}
            <p class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 p-2 text-gray-700 shadow-sm">
                {{ $loggedInEmployee->last_name .', '.$loggedInEmployee->first_name }}
            </p>
            <input type="hidden" name="employee_id" value="{{ $loggedInEmployee->id }}">
        @endif
    </div>

    {{-- 2. Leave Type Dropdown --}}
    <div>
        <x-label for="leave_type_id" value="{{ __('Leave Type') }}" class="font-semibold text-gray-700" />
        <select id="leave_type_id" name="leave_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">-- Select Leave Type --</option>
             @foreach ($leaveTypes as $leaveType)
                @php
                    $creditColumn = strtolower(str_replace(' ', '_', $leaveType->name));
                    $balance = $remainingCredits ? ($remainingCredits[$creditColumn] ?? 0) : 0;
                @endphp
                <option value="{{ $leaveType->id }}" 
                    data-balance="{{ $balance }}"
                    data-leave-type="{{ $leaveType->name }}"
                    {{ old('leave_type_id', $leaveApplication?->leave_type_id) == $leaveType->id ? 'selected' : '' }}>
                    {{ $leaveType->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- 3. Live Credit Balance Display --}}
    <div id="credit_balance_container" class="rounded-lg border border-blue-200 bg-blue-50 p-4 hidden">
        <h4 class="font-semibold text-blue-900 mb-1">Available Credits</h4>
        <div id="credit_balance" class="text-sm text-blue-800"></div>
    </div>

    {{-- 4. Reason and Dates --}}
    <div>
        <x-label for="reason" value="{{ __('Reason for Leave') }}" class="font-semibold text-gray-700" />
        <textarea name="reason" id="reason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3" required>{{ old('reason', $leaveApplication->reason ?? '') }}</textarea>
    </div>

    {{-- HR Manual Approval Status (Only visible to HR) --}}

  
    @if(isset($isHrRecordingMode) && $isHrRecordingMode)
    {{-- Show the Approval Dropdown and HR Remarks --}}
        <div class="mt-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
            <h4 class="font-bold text-orange-800 mb-3">Direct Approval (HR Admin)</h4>
            <div>
                <x-label for="approval_status" value="{{ __('Set Final Status') }}" class="font-semibold text-gray-700" />
                <select id="approval_status" name="approval_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="pending">Pending</option>
                    <option value="approved_with_pay">Approved with Pay</option>
                    <option value="approved_without_pay">Approved without Pay</option>
                    <option value="rejected">Rejected</option>
                </select>
                <p class="mt-1 text-xs text-orange-600 italic">Note: Choosing a status here will bypass the Academic Head and Admin approval steps.</p>
            </div>
        </div>

        {{-- HR Remarks (Only visible to HR) --}}

    <div class="mt-4">
        <x-label for="hr_remarks" value="{{ __('Administrative Remarks') }}" class="font-semibold text-gray-700" />
        <textarea name="hr_remarks" id="hr_remarks" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="2" placeholder="e.g., Request via walk-in / Medical emergency">{{ old('hr_remarks') }}</textarea>
        <p class="mt-1 text-xs text-gray-500 italic">This will be saved as: "Recorded by HR: [Your Note]"</p>
    </div>

    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <x-label for="start_date" value="{{ __('Start Date') }}" class="font-semibold text-gray-700" />
            <x-input id="start_date" type="date" name="start_date" class="mt-1 block w-full" :value="old('start_date', $leaveApplication?->start_date?->format('Y-m-d'))" required />
        </div>
        <div>
            <x-label for="end_date" value="{{ __('End Date') }}" class="font-semibold text-gray-700" />
            <x-input id="end_date" type="date" name="end_date" class="mt-1 block w-full" :value="old('end_date', $leaveApplication?->end_date?->format('Y-m-d'))" required />
        </div>
    </div>
</div>

{{-- 5. Conditional Sections (Hidden for HR) --}}
@if(!$isHrRecordingMode)
    <div id="classes_to_miss_container" class="mt-8 border-t pt-6">
        <h3 class="mb-4 text-lg font-bold text-gray-800">Classes to Miss (For Teachers)</h3>
        <div class="overflow-x-auto rounded-lg border border-gray-200 p-4">
            {{-- Loop through your class rows here as you did before --}}
            @php $rowsData = old('classes_data') ?? $existingClasses ?? []; @endphp
            @for ($i = 0; $i < 8; $i++)
                @include('leave_applications.partials.class_row', ['index' => $i, 'class' => $rowsData[$i] ?? [], 'teachers' => $teachers])
            @endfor
        </div>
    </div>

    <div id="staff_fields" class="mt-8 rounded-md border border-green-200 bg-green-50 p-6">
        <h4 class="mb-4 text-lg font-bold text-gray-800">Work Handover (For Staff)</h4>
        <div class="space-y-4">
            <x-label for="tasks_endorsed" value="{{ __('Tasks Endorsed') }}" />
            <textarea id="tasks_endorsed" name="tasks_endorsed" class="w-full rounded-md border-gray-300 shadow-sm" rows="2">{{ old('tasks_endorsed', $leaveApplication->tasks_endorsed ?? '') }}</textarea>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-label value="Personnel to Take Over" />
                    <select name="personnel_to_take_over_id" class="w-full rounded-md border-gray-300">
                        <option value="">-- Select --</option>
                        @foreach($staffPersonnel as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->last_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- 6. Updated JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Selectors
    const empSelect = document.getElementById('employee_id');
    const leaveTypeSelect = document.getElementById('leave_type_id');
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const balanceBox = document.getElementById('credit_balance_container');
    const balanceText = document.getElementById('credit_balance');
    const exceedsCreditsContainer = document.getElementById('exceeds_credits_container');
    const exceedsCreditsMessage = document.getElementById('exceeds_credits_message');
    
    // Initial Data
    const today = new Date("{{ date('Y-m-d') }}");
    const holidays = [
        '2025-12-30', '2025-12-31',
        '2026-01-01', '2026-01-02', '2026-02-25', '2026-04-02', '2026-04-03', '2026-04-04',
        '2026-04-09', '2026-05-01', '2026-06-12', '2026-08-21', '2026-08-31',
        '2026-11-01', '2026-11-02', '2026-11-30', '2026-12-08', '2026-12-24',
        '2026-12-25', '2026-12-30', '2026-12-31'
    ];
    let activeCredits = @json($remainingCredits ?? []);
    const isHrRecordingMode = {{ isset($isHrRecordingMode) && $isHrRecordingMode ? 'true' : 'false' }};

    // --- Utility Functions (From Old Script) ---
    function addDays(date, days) {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function isWeekday(date) {
        const day = date.getDay();
        return day !== 0 && day !== 6;
    }

    function isHoliday(date) {
        return holidays.includes(formatDate(date));
    }

    function calculateWorkDays(startStr, endStr) {
        if (!startStr || !endStr) return 0;
        const startDate = new Date(startStr);
        const endDate = new Date(endStr);
        let workDays = 0;
        const currentDate = new Date(startDate);
        while (currentDate <= endDate) {
            if (isWeekday(currentDate) && !isHoliday(currentDate)) {
                workDays++;
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }
        return workDays;
    }

    // --- Core UI Logic ---
    function updateUI() {
        const option = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        if (!option || !option.value) {
            if (balanceBox) balanceBox.classList.add('hidden');
            return;
        }

        const rawTypeName = option.getAttribute('data-leave-type');
        const typeKey = rawTypeName.toLowerCase().replace(/ /g, '_');
        const balance = activeCredits[typeKey] ?? 0;

        // 1. Update Balance Display
        if (balanceText) {
            balanceText.innerHTML = `<strong>${rawTypeName}</strong>: ${balance} days available`;
            if (balance <= 0) {
                balanceBox.classList.replace('bg-blue-50', 'bg-red-50');
                balanceBox.classList.replace('border-blue-200', 'border-red-200');
            } else {
                balanceBox.classList.replace('bg-red-50', 'bg-blue-50');
                balanceBox.classList.replace('border-red-200', 'border-blue-200');
            }
            balanceBox.classList.remove('hidden');
        }

        // 2. Update Date Constraints (Min/Max dates)
        updateDateConstraints(rawTypeName);

        // 3. Final Credit Check
        checkCreditsExceeded(balance, rawTypeName);
    }

    function updateDateConstraints(leaveTypeName) {
        // HR ignores lead-time constraints when recording for others
        if (isHrRecordingMode) return;

        let minDate = new Date(today);
        if (leaveTypeName === 'Vacation Leave') {
            minDate = addDays(today, 7);
        } else if (leaveTypeName === 'Sick Leave' || leaveTypeName === 'Service Incentive Leave') {
            minDate = addDays(today, -7);
        }

        const minDateStr = formatDate(minDate);
        if (startInput) startInput.setAttribute('min', minDateStr);
        if (endInput) endInput.setAttribute('min', minDateStr);
    }

    function checkCreditsExceeded(balance, leaveTypeName) {
        const startVal = startInput.value;
        const endVal = endInput.value;
        
        if (!startVal || !endVal) {
            if (exceedsCreditsContainer) exceedsCreditsContainer.classList.add('hidden');
            return;
        }

        const workDays = calculateWorkDays(startVal, endVal);
        const balanceInt = parseFloat(balance);

        if (workDays > balanceInt) {
            if (exceedsCreditsContainer) {
                let msg = `<strong>You are requesting ${workDays} days</strong>, but only <strong>${balanceInt} days</strong> are available.`;
                if (isHrRecordingMode) {
                    msg += `<br><span class="text-red-600 italic">Note: As HR, you may proceed, but this will be recorded as unpaid/overdrawn.</span>`;
                }
                exceedsCreditsMessage.innerHTML = msg;
                exceedsCreditsContainer.classList.remove('hidden');
            }
        } else {
            if (exceedsCreditsContainer) exceedsCreditsContainer.classList.add('hidden');
        }
    }

    // --- Event Listeners ---

    // HR Employee Selection Change
    if (empSelect && empSelect.tagName === 'SELECT') {
        empSelect.addEventListener('change', function() {
            if (!this.value) return;
            fetch(`/api/employee-credits/${this.value}`)
                .then(res => res.json())
                .then(data => {
                    activeCredits = data;
                    updateUI();
                });
        });
    }

    // Leave Type Change
    leaveTypeSelect.addEventListener('change', updateUI);

    // Date Changes
    [startInput, endInput].forEach(input => {
        if (input) {
            input.addEventListener('change', () => {
                const option = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                const typeName = option ? option.getAttribute('data-leave-type') : '';
                const typeKey = typeName.toLowerCase().replace(/ /g, '_');
                checkCreditsExceeded(activeCredits[typeKey] ?? 0, typeName);
            });
        }
    });

    // Initialize
    if (leaveTypeSelect.value) updateUI();
});
</script>