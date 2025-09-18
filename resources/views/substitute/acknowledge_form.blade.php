<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Acknowledge Substitute Assignment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment Details:</h3>

                <div class="mb-4">
                    <p><strong>Leaving Teacher:</strong> {{ $class->leaveApplication->employee->last_name.' '.$class->leaveApplication->employee->first_name.' '.$class->leaveApplication->employee->mid_name }}</p>
                    <p><strong>Leave Dates:</strong> {{ $class->leaveApplication->start_date->format('M d, Y') }} - {{ $class->leaveApplication->end_date->format('M d, Y') }}</p>
                </div>

                <div class="mb-6 border-t border-b border-gray-200 py-4">
                    <p><strong>Course Code:</strong> {{ $class->course_code }}</p>
                    <p><strong>Title:</strong> {{ $class->title }}</p>
                    <p><strong>Day/Time/Room:</strong> {{ $class->day_time_room }}</p>
                    <p><strong>Topics to Cover:</strong> {{ $class->topics ?: 'N/A' }}</p>
                </div>

                <form method="POST" action="{{ route('substitute.process_acknowledgement', $class) }}">
                    @csrf
                    <p class="mb-4">By clicking "Acknowledge Assignment", you confirm that you have reviewed and accept this substitute teaching assignment.</p>
                    <x-button class="bg-green-500 hover:bg-green-600 text-white">
                        {{ __('Acknowledge Assignment') }}
                    </x-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>