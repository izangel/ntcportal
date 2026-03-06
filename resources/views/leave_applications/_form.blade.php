{{-- resources/views/leave_applications/_form.blade.php --}}

@props(['leaveApplication' => null, 'employees', 'loggedInEmployee' => null, 'teachers', 'staffPersonnel', 'leaveTypes', 'remainingCredits'])

<div class="space-y-6">
    {{-- Automatic Employee Name --}}
    <div>
        <x-label for="employee_name_display" value="{{ __('Employee') }}" class="font-semibold text-gray-700" />
        @if($loggedInEmployee)
            <p id="employee_name_display" class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 p-2 text-gray-700 shadow-sm">
                {{ $loggedInEmployee->last_name .', '.$loggedInEmployee->first_name.' '.$loggedInEmployee->mid_name }} ({{ ucwords($loggedInEmployee->role) }})
            </p>
            <input type="hidden" name="employee_id" value="{{ $loggedInEmployee->id }}">
        @else
            <p class="mt-1 block w-full p-2 text-sm text-red-500">Error: Logged-in employee not found. Please ensure your user account is linked to an employee.</p>
            <input type="hidden" name="employee_id" value="">
        @endif
        @error('employee_id')
            <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
        @enderror
    </div>

    {{-- Leave Type Dropdown --}}
    <div>
        <x-label for="leave_type_id" value="{{ __('Leave Type') }}" class="font-semibold text-gray-700" />
        <select id="leave_type_id" name="leave_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm transition duration-150 ease-in-out focus:border-indigo-500 focus:ring-indigo-500" required onchange="validateLeaveTypeSelection()">
            <option value="">-- Select Leave Type --</option>
             @foreach ($leaveTypes as $leaveType)
                @php
                    $creditColumn = strtolower(str_replace(' ', '_', $leaveType->name));
<<<<<<< HEAD
                    $balance = $leaveCredits ? ($leaveCredits->$creditColumn ?? 0) : 0;
=======
                    $balance = $remainingCredits ? ($remainingCredits[$creditColumn] ?? 0) : 0;
>>>>>>> origin/main
                @endphp
                <option value="{{ $leaveType->id }}" 
                    data-balance="{{ $balance }}"
                    data-leave-type="{{ $leaveType->name }}"
                    {{ old('leave_type_id', $leaveApplication?->leave_type_id) == $leaveType->id ? 'selected' : '' }}>
                    {{ $leaveType->name }}
                </option>
            @endforeach
        </select>
        <div id="leave_type_error" class="mt-1 text-sm text-red-500 hidden">Please select a valid leave type</div>
        @error('leave_type_id')
            <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
        @enderror
    </div>

    {{-- Leave Credit Balance Display --}}
<<<<<<< HEAD
    @if($leaveCredits)
=======
    @if($remainingCredits)
>>>>>>> origin/main
        <div id="credit_balance_container" class="rounded-lg border border-blue-200 bg-blue-50 p-4 hidden">
            <h4 class="font-semibold text-blue-900 mb-2">Current Leave Credits</h4>
            <div id="credit_balance" class="text-sm text-blue-800">
                -- Select a leave type to view available balance --
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const leaveTypeSelect = document.getElementById('leave_type_id');
                const creditBalanceContainer = document.getElementById('credit_balance_container');
                const creditBalanceText = document.getElementById('credit_balance');
                
                function updateBalance() {
                    const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                    const balance = selectedOption.getAttribute('data-balance');
                    const leaveTypeName = selectedOption.getAttribute('data-leave-type');
                    
                    if (leaveTypeSelect.value && balance !== null) {
                        const balanceInt = parseInt(balance);
                        creditBalanceText.innerHTML = `<strong>${leaveTypeName}</strong>: ${balanceInt} days available`;
                        creditBalanceContainer.classList.remove('hidden');
                        
                        // Show warning if no credits
                        if (balanceInt <= 0) {
                            creditBalanceContainer.classList.remove('bg-blue-50', 'border-blue-200');
                            creditBalanceContainer.classList.add('bg-red-50', 'border-red-200');
                            creditBalanceText.classList.remove('text-blue-800');
                            creditBalanceText.classList.add('text-red-800');
                            creditBalanceText.innerHTML = `<strong class="text-red-900">${leaveTypeName}</strong>: <span class="font-bold">NO CREDITS AVAILABLE</span>. You cannot file a leave application for this type.`;
                        } else {
                            creditBalanceContainer.classList.remove('bg-red-50', 'border-red-200');
                            creditBalanceContainer.classList.add('bg-blue-50', 'border-blue-200');
                            creditBalanceText.classList.remove('text-red-800');
                            creditBalanceText.classList.add('text-blue-800');
                        }
                    } else {
                        creditBalanceContainer.classList.add('hidden');
                    }
                }

                leaveTypeSelect.addEventListener('change', updateBalance);

                // Trigger on page load if leave type was already selected
                if (leaveTypeSelect.value) {
                    updateBalance();
                }
            });
        </script>
    @endif

    {{-- Reason for Leave Textarea --}}
    <div>
        <x-label for="reason" value="{{ __('Reason for Leave') }}" class="font-semibold text-gray-700" />
        <textarea name="reason" id="reason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm transition duration-150 ease-in-out focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="5" required>{{ old('reason', $leaveApplication->reason ?? '') }}</textarea>
        @error('reason')
            <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
        @enderror
    </div>

    {{-- Start and End Date Inputs --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <x-label for="start_date" value="{{ __('Start Date') }}" class="font-semibold text-gray-700" />
            <x-input id="start_date" type="date" name="start_date" min="{{ date('Y-m-d') }}" class="mt-1 block w-full" :value="old('start_date', $leaveApplication?->start_date?->format('Y-m-d'))" required />
            @error('start_date')
                <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <x-label for="end_date" value="{{ __('End Date') }}" class="font-semibold text-gray-700" />
            <x-input id="end_date" type="date" name="end_date" min="{{ date('Y-m-d') }}" class="mt-1 block w-full" :value="old('end_date', $leaveApplication?->end_date?->format('Y-m-d'))" required />
            @error('end_date')
                <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Exceeds Leave Credits Notification --}}
    @if($remainingCredits)
        <div id="exceeds_credits_container" class="rounded-lg border border-red-200 bg-red-50 p-4 hidden">
            <h4 class="font-semibold text-red-900 mb-2">Leave Duration Exceeds Available Credits</h4>
            <div id="exceeds_credits_message" class="text-sm text-red-800">
                -- Notification message will appear here --
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const start = document.getElementById('start_date');
            const end = document.getElementById('end_date');
            const leaveTypeSelect = document.getElementById('leave_type_id');
            const exceedsCreditsContainer = document.getElementById('exceeds_credits_container');
            const exceedsCreditsMessage = document.getElementById('exceeds_credits_message');
            const today = new Date("{{ date('Y-m-d') }}");

            // Hardcoded Holidays
            const holidays = [
                '2025-12-30', '2025-12-31',
                '2026-01-01', '2026-01-02', '2026-02-25', '2026-04-02', '2026-04-03', '2026-04-04',
                '2026-04-09', '2026-05-01', '2026-06-12', '2026-08-21', '2026-08-31',
                '2026-11-01', '2026-11-02', '2026-11-30', '2026-12-08', '2026-12-24',
                '2026-12-25', '2026-12-30', '2026-12-31'
            ];

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
                return day !== 0 && day !== 6; // 0 = Sunday, 6 = Saturday
            }

            function isHoliday(date) {
                return holidays.includes(formatDate(date));
            }

            function calculateWorkDays(startStr, endStr) {
                if (!startStr || !endStr) return 0;
                
                const startDate = new Date(startStr);
                const endDate = new Date(endStr);
                
<<<<<<< HEAD
                let workDays = 0;
                const currentDate = new Date(startDate);
                
                // Include the end date
                while (currentDate <= endDate) {
                    if (isWeekday(currentDate) && !isHoliday(currentDate)) {
                        workDays++;
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                
                return workDays;
=======
                // Calculate total days inclusive (including weekends)
                const timeDiff = endDate.getTime() - startDate.getTime();
                const totalDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                
                // Count holidays within the date range (inclusive)
                let holidaysInRange = 0;
                holidays.forEach(holiday => {
                    const hDate = new Date(holiday);
                    if (hDate >= startDate && hDate <= endDate) {
                        holidaysInRange++;
                    }
                });
                
                return totalDays - holidaysInRange;
>>>>>>> origin/main
            }

            function checkCreditsExceeded() {
                if (!start || !end || !leaveTypeSelect) return;
                
                const startVal = start.value;
                const endVal = end.value;
                const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                const balance = selectedOption.getAttribute('data-balance');
                const leaveTypeName = selectedOption.getAttribute('data-leave-type');
                
                if (!startVal || !endVal || !balance) {
                    if (exceedsCreditsContainer) {
                        exceedsCreditsContainer.classList.add('hidden');
                    }
                    return;
                }
                
                const workDays = calculateWorkDays(startVal, endVal);
                const balanceInt = parseInt(balance);
                
                if (workDays > balanceInt) {
                    if (exceedsCreditsContainer) {
                        exceedsCreditsMessage.innerHTML = `<strong>You are requesting ${workDays} days of leave</strong>, but you only have <strong>${balanceInt} days</strong> available for <strong>${leaveTypeName}</strong>. Please adjust your dates or contact HR.`;
                        exceedsCreditsContainer.classList.remove('hidden');
                    }
                } else {
                    if (exceedsCreditsContainer) {
                        exceedsCreditsContainer.classList.add('hidden');
                    }
                }
            }

            function updateDateConstraints() {
                const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                const leaveTypeName = selectedOption.getAttribute('data-leave-type');

                let minDate = null;

                if (leaveTypeName === 'Vacation Leave') {
                    // Vacation Leave: exactly 1 week from today onwards
                    minDate = addDays(today, 7);
                } else if (leaveTypeName === 'Sick Leave' || leaveTypeName === 'Service Incentive Leave') {
                    // Sick Leave & Service Incentive Leave: 1 week before today and onwards
                    minDate = addDays(today, -7);
                } else {
                    // Default: from today onwards
                    minDate = new Date(today);
                }

                const minDateStr = formatDate(minDate);
                if (start) start.setAttribute('min', minDateStr);
                if (end) end.setAttribute('min', minDateStr);

                // Set validation attributes for visual feedback
                if (start) start.setAttribute('data-min-date', minDateStr);
                if (end) end.setAttribute('data-min-date', minDateStr);
                
                // Check credits after updating constraints
                checkCreditsExceeded();
            }

            // Initialize constraints on page load if leave type is selected
            if (leaveTypeSelect && leaveTypeSelect.value) {
                updateDateConstraints();
            }

            // Update constraints when leave type changes
            if (leaveTypeSelect) {
                leaveTypeSelect.addEventListener('change', function() {
                    updateDateConstraints();
                });
            }

            // When start date changes, update end.min and check credits
            if (start) {
                start.addEventListener('change', function() {
                    const startVal = this.value;
                    if (end) {
                        const minDate = this.getAttribute('data-min-date');
                        const endMin = startVal > minDate ? startVal : minDate;
                        end.setAttribute('min', endMin);
                        if (end.value && end.value < endMin) {
                            end.value = endMin;
                        }
                    }
                    checkCreditsExceeded();
                });
            }

            // When end date changes, check credits
            if (end) {
                end.addEventListener('change', function() {
                    checkCreditsExceeded();
                });
            }
        });
    </script>
</div>



{{-- Classes to Miss Section --}}
<div id="classes_to_miss_container" class="mt-8 overflow-x-auto">
    <h3 class="mb-4 text-xl font-bold text-gray-800">Classes to Miss (For Teachers)</h3>
    <div class="min-w-[900px] rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        {{-- Header Row for Class Details --}}
        <div class="grid grid-cols-6 gap-4 border-b-2 border-gray-300 pb-2 font-semibold text-gray-700">
            <div class="col-span-1 text-xs sm:text-sm">{{ __('Course Code') }}</div>
            <div class="col-span-1 text-xs sm:text-sm">{{ __('Title') }}</div>
            <div class="col-span-1 text-xs sm:text-sm">{{ __('Day/Time/Room') }}</div>
            <div class="col-span-1 text-xs sm:text-sm">{{ __('Topics to Discuss') }}</div>
            <div class="col-span-1 text-xs sm:text-sm">{{ __('Substitute Teacher') }}</div>
            <div class="col-span-1 text-xs sm:text-sm">{{ __('Signature') }}</div>
        </div>
        {{-- End Header Row --}}

        <!-- @php
            $existingClasses = [];
            if ($leaveApplication && $leaveApplication->classesToMiss) {
                $existingClasses = $leaveApplication->classesToMiss->toArray();
            }
            if (old('classes_data') && is_array(old('classes_data'))) {
                $existingClasses = old('classes_data');
            }
            $numRowsToDisplay = 8;
        @endphp

        {{-- Loop to render 8 rows --}}
        @for ($i = 0; $i < $numRowsToDisplay; $i++)
            @php
                $classData = $existingClasses[$i] ?? [];
            @endphp
            {{-- Include the partial for each row --}}
            @include('leave_applications.partials.class_row', [
                'index' => $i,
                'class' => $classData,
                'teachers' => $teachers,
                'isNew' => !isset($classData['id'])
            ])
        @endfor -->


        @php
            $rowsData = old('classes_data') ?? $existingClasses;
            $numRowsToDisplay = max(count($rowsData), 8); // Ensure at least 8 rows are displayed
        @endphp

        {{-- Loop to render rows --}}
        @for ($i = 0; $i < $numRowsToDisplay; $i++)
            @php
                $classData = $rowsData[$i] ?? [];
            @endphp
            @include('leave_applications.partials.class_row', [
                'index' => $i,
                'class' => $classData,
                'teachers' => $teachers,
            ])
        @endfor
    </div>
</div>

---

{{-- Staff Specific Fields Section --}}
<div id="staff_fields" class="mt-8 rounded-md border border-green-200 bg-green-50 p-6 shadow-sm">
    <h4 class="mb-4 text-xl font-bold text-gray-800">For Staff Personnel</h4>
    <div class="space-y-6">
        {{-- List of Works/Tasks Endorsed --}}
        <div>
            <x-label for="tasks_endorsed" value="{{ __('List of Works/Tasks Endorsed') }}" class="font-semibold text-gray-700" />
            <textarea id="tasks_endorsed" name="tasks_endorsed" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm transition duration-150 ease-in-out focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('tasks_endorsed', $leaveApplication->tasks_endorsed ?? '') }}</textarea>
            @error('tasks_endorsed')
                <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>

        {{-- Personnel to Take Over & Signature --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-label for="personnel_to_take_over_id" value="{{ __('Personnel to Take Over') }}" class="font-semibold text-gray-700" />
                <select id="personnel_to_take_over_id" name="personnel_to_take_over_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm transition duration-150 ease-in-out focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Select Personnel --</option>
                    @foreach($staffPersonnel as $staff)
                        <option value="{{ $staff->id }}"
                            {{ (old('personnel_to_take_over_id', $leaveApplication->personnel_to_take_over_id ?? '') == $staff->id) ? 'selected' : '' }}>
                            {{ $staff->last_name. ' ' . $staff->first_name. ' ' .$staff->mid_name}}
                        </option>
                    @endforeach
                </select>
                @error('personnel_to_take_over_id')
                    <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <x-label for="acknowledgement_personnel_take_over_signature" value="{{ __('Signature of Personnel (Name)') }}" class="font-semibold text-gray-700" />
                <x-input id="acknowledgement_personnel_take_over_signature" type="text" name="acknowledgement_personnel_take_over_signature" class="mt-1 block w-full" :value="old('acknowledgement_personnel_take_over_signature', $leaveApplication->acknowledgement_personnel_take_over_signature ?? '')" placeholder="Type personnel's name" />
                @error('acknowledgement_personnel_take_over_signature')
                    <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
</div>