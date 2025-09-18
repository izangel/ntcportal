{{-- resources/views/leave_applications/_form.blade.php --}}

@props(['leaveApplication' => null, 'employees', 'loggedInEmployee' => null, 'teachers', 'staffPersonnel', 'leaveTypes'])

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
        <select id="leave_type_id" name="leave_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm transition duration-150 ease-in-out focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">-- Select Leave Type --</option>
             @foreach ($leaveTypes as $leaveType)
                <option value="{{ $leaveType->id }}" {{ old('leave_type_id', $leaveApplication?->leave_type_id) == $leaveType->id ? 'selected' : '' }}>
                    {{ $leaveType->name }}
                </option>
            @endforeach
        </select>
        @error('leave_type_id')
            <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
        @enderror
    </div>

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
            <x-input id="start_date" type="date" name="start_date" class="mt-1 block w-full" :value="old('start_date', $leaveApplication?->start_date?->format('Y-m-d'))" required />
            @error('start_date')
                <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <x-label for="end_date" value="{{ __('End Date') }}" class="font-semibold text-gray-700" />
            <x-input id="end_date" type="date" name="end_date" class="mt-1 block w-full" :value="old('end_date', $leaveApplication?->end_date?->format('Y-m-d'))" required />
            @error('end_date')
                <span class="mt-1 text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

---

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