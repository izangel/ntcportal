<div class="p-6 bg-white rounded-lg shadow-lg">
    
    @if(!$activeAcademicYear)
        <div class="p-4 mb-6 border-l-4 border-red-500 bg-red-50 text-red-700 rounded-r-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="font-bold">System Configuration Error</p>
                    <p class="text-sm">Leave applications cannot be processed because there is no active school year configured in the database.</p>
                </div>
            </div>
        </div>
    @else
        <div class="p-3 mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 text-sm rounded-r-md">
            Recording data allocation to School Year: <strong>{{ $activeAcademicYear->name ?? 'Active Year' }}</strong>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 mb-6 border-l-4 border-orange-500 bg-orange-50 text-orange-700 rounded-r-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 mb-6 border-l-4 border-red-500 bg-red-50 text-red-700 rounded-r-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="font-bold">Please correct the following errors before submitting:</p>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            @if($isHrRecordingMode)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Target Employee</label>
                    <select wire:model.live="employee_id" class="mt-1 block w-full rounded-md @error('employee_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror border-gray-300 shadow-sm">
                        <option value="">-- Select Employee --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700">Leave Type</label>
                <select wire:model.live="leave_type_id" class="mt-1 block w-full rounded-md @error('leave_type_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror border-gray-300 shadow-sm">
                    <option value="">-- Select Type --</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('leave_type_id') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        @if($employee_id && $leave_type_id)
            <div class="mt-4 p-3 bg-gray-50 border rounded-md flex justify-between text-sm">
                <span>Available Credits: <strong class="text-indigo-600">{{ $availableCredits }}</strong></span>
                <span>Calculated Days requested: <strong class="{{ $total_days > $availableCredits && !$isHrRecordingMode ? 'text-red-600 font-bold' : 'text-green-600' }}">{{ $total_days }} Days</strong></span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" wire:model.live="start_date" class="mt-1 block w-full rounded-md @error('start_date') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror border-gray-300 shadow-sm">
                @error('start_date') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" wire:model.live="end_date" class="mt-1 block w-full rounded-md @error('end_date') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror border-gray-300 shadow-sm">
                @error('end_date') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700">Reason for Leave</label>
            <textarea wire:model="reason" rows="3" class="mt-1 block w-full rounded-md @error('reason') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror border-gray-300 shadow-sm"></textarea>
            @error('reason') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        @if(!$isHrRecordingMode)
            <div class="mt-6 border-t pt-6">
                <h3 class="text-md font-medium text-gray-900 mb-4">Handover & Coverage Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Personnel to Take Over Tasks</label>
                        <select wire:model="personnel_to_take_over_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- Select Employee --</option>
                            @foreach($staffPersonnel as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->last_name }}, {{ $staff->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Endorsed Tasks Specifics</label>
                        <input type="text" wire:model="tasks_endorsed" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-700">Classes to Miss & Alternate Substitute Allocations</label>
                        <button type="button" wire:click="addClassRow" class="px-2 py-1 bg-gray-800 text-white text-xs rounded hover:bg-gray-700">+ Add Class</button>
                    </div>
                    
                    @foreach($classes_data as $index => $classRow)
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-2 items-center mb-2 p-2 border border-dashed rounded bg-gray-50">
                            <div>
                                <input type="text" wire:model="classes_data.{{$index}}.course_code" placeholder="Course Code" class="w-full rounded-md border-gray-300 text-sm @error('classes_data.'.$index.'.course_code') border-red-300 @enderror">
                                @error('classes_data.'.$index.'.course_code') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <input type="text" wire:model="classes_data.{{$index}}.title" placeholder="Class Title" class="w-full rounded-md border-gray-300 text-sm">
                            <input type="text" wire:model="classes_data.{{$index}}.day_time_room" placeholder="Day/Time/Room" class="w-full rounded-md border-gray-300 text-sm">
                            
                            <div>
                                <select wire:model="classes_data.{{$index}}.substitute_teacher_id" class="w-full rounded-md border-gray-300 text-sm @error('classes_data.'.$index.'.substitute_teacher_id') border-red-300 @enderror">
                                    <option value="">-- Substitute --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->last_name }}, {{ $teacher->first_name }}</option>
                                    @endforeach
                                </select>
                                @error('classes_data.'.$index.'.substitute_teacher_id') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                            </div>

                            <button type="button" wire:click="removeClassRow({{ $index }})" class="text-red-600 text-sm hover:underline text-left md:text-center">Remove</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-8 pt-6 border-t flex justify-end">
            <button type="submit" 
                @if(!$activeAcademicYear) disabled @endif
                class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md shadow hover:bg-indigo-700 disabled:opacity-50 transition">
                Submit Leave Application
            </button>
        </div>
    </form>
</div>