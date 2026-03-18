{{-- resources/views/semesters/edit.blade.php --}}

@extends('admin.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Leave Credit') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Leave Credit: {{ $leavecredit->employee->name }} ({{ $leavecredit->academicYear->start_year ?? 'N/A' }} - {{ $leavecredit->academicYear->end_year ?? 'N/A' }})</h3>

                <form method="POST" action="{{ route('leave-credits.update', $leavecredit) }}">
                    @csrf
                    @method('PUT')

                    
                    <div class="mt-4">
                        <x-label for="employee_name" value="{{ __('Employee Name') }}" />
    
                            {{-- 1. Display the name as a non-editable text field --}}
                            <input 
                                id="employee_name" 
                                class="block mt-1 w-full border-gray-300 bg-gray-100 rounded-md shadow-sm cursor-not-allowed" 
                                type="text" 
                                value="{{ $leavecredit->employee->last_name .' '.$leavecredit->employee->first_name.' '.$leavecredit->employee->mid_name }}" 
                                disabled 
                            />

                            {{-- 2. Add a HIDDEN field to send the required ID to the server --}}
                            <input 
                                type="hidden" 
                                name="employee_id" 
                                value="{{ $leavecredit->employee_id }}" 
                            />
                    </div>

                    <div class="mt-4">
                        <x-label for="sick_leave" value="{{ __('Sick Leave') }}" />
                        <x-input id="sick_leave" class="block mt-1 w-full" type="number" name="sick_leave" :value="old('sick_leave', $leavecredit->sick_leave)" required />
                        <x-input-error for="sick_leave" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="vacation_leave" value="{{ __('Vacation Leave') }}" />
                        <x-input id="vacation_leave" class="block mt-1 w-full" type="number" name="vacation_leave" :value="old('vacation_leave', $leavecredit->vacation_leave)" required />
                        <x-input-error for="vacation_leave" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="service_incentive_leave" value="{{ __('Service Incentive Leave') }}" />
                        <x-input id="service_incentive_leave" class="block mt-1 w-full" type="number" name="service_incentive_leave" :value="old('service_incentive_leave', $leavecredit->service_incentive_leave)" required />
                        <x-input-error for="service_incentive_leave" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-label for="academic_year_id" value="{{ __('Academic Year') }}" />
                        <select id="academic_year_id" name="academic_year_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Select an Academic Year --</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $leavecredit->academic_year_id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->start_year }} - {{ $year->end_year }} {{ $year->is_active ? '(Active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="academic_year_id" class="mt-2" />
                    </div>

                </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Update Leave Credit') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection