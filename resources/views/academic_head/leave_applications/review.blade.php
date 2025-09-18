<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Review Leave Application (Academic Head)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('info'))
                <div class="mb-4 font-medium text-sm text-blue-600 bg-blue-100 p-3 rounded-md">
                    {{ session('info') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="font-semibold text-lg">{{ $leaveApplication->employee->first_name.' '.$leaveApplication->employee->last_name  }}'s Leave Application</h3>
                    <p class="text-sm text-gray-600">Type: {{ $leaveApplication->leaveType->name }}</p>
                    <p class="text-sm text-gray-600">Period: {{ $leaveApplication->start_date->format('M d, Y') }} - {{ $leaveApplication->end_date->format('M d, Y') }}</p>
                    <p class="text-sm text-gray-600">Reason: {{ $leaveApplication->reason }}</p>
                    <p class="text-sm text-gray-600 mt-2 font-semibold">Academic Head Status: <span class="uppercase {{ $leaveApplication->ah_status === 'approved' ? 'text-green-700' : ($leaveApplication->ah_status === 'rejected' ? 'text-red-700' : 'text-gray-700') }}">{{ $leaveApplication->ah_status }}</span></p>

                    @if($leaveApplication->ah_status !== 'pending' && $leaveApplication->ah_approved_at)
                        <p class="text-sm text-gray-600">Decision on: {{ $leaveApplication->ah_approved_at->format('M d, Y') }} by {{ $leaveApplication->ahApprover->name ?? 'N/A' }}</p>
                        @if($leaveApplication->ah_remarks)
                            <p class="text-sm text-gray-600">Remarks: "{{ $leaveApplication->ah_remarks }}"</p>
                        @endif
                    @endif

                    {{-- Display classes to miss --}}
                    @if($leaveApplication->classesToMiss->isNotEmpty())
                        <h4 class="font-semibold text-md mt-4">Classes to Miss:</h4>
                        <ul class="list-disc list-inside text-sm text-gray-700">
                            @foreach($leaveApplication->classesToMiss as $classToMiss)
                                <li>
                                    {{ $classToMiss->course_code }} - {{ $classToMiss->title }}
                                    ({{ $classToMiss->day_time_room }})
                                    @if($classToMiss->substituteTeacher)
                                        (Sub: {{ $classToMiss->substituteTeacher->first_name.' '.$classToMiss->substituteTeacher->last_name }})
                                    @endif
                                    @if($classToMiss->sub_ack_at)
                                        - Acknowledged by Sub on {{ $classToMiss->sub_ack_at->format('M d, Y') }}
                                    @else
                                        - Not yet acknowledged by Sub
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if($leaveApplication->ah_status === 'pending')
                    <div class="p-6">
                        <form method="POST" action="{{ route('ah.leave_applications.decide', $leaveApplication) }}">
                            @csrf
                            <div class="mb-4">
                                <x-input-label for="ah_status" :value="__('Decision')" />
                                <select id="decision" name="ah_status" class="block w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select decision</option>
                                    <option value="approved">Approve</option>
                                    <option value="rejected">Reject</option>
                                </select>
                                <x-input-error for="ah_status" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-input-label for="ah_remarks" :value="__('Remarks (Optional)')" />
                                <textarea id="remarks" name="ah_remarks" rows="3" class="block w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                <x-input-error for="ah_remarks" class="mt-2" />
                            </div>
                            <div class="flex items-center justify-end">
                                <x-primary-button>
                                    {{ __('Submit Decision') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="p-6 bg-gray-100 text-center text-gray-700">
                        <p>This application has already been processed by the Academic Head.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>