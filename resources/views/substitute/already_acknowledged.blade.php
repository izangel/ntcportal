<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Substitute Assignment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <h3 class="text-xl font-medium text-gray-900 mb-4">This assignment has already been acknowledged.</h3>
                <p class="text-gray-600">
                    Thank you for your response. This assignment for **{{ $class->course_code }}** was acknowledged on {{ $class->sub_ack_at->format('M d, Y h:i A') }} by {{ $class->acknowledgedBy->name ?? 'an unknown user' }}.
                </p>
                <div class="mt-6">
                    <x-button onclick="window.location='{{ route('dashboard') }}'">
                        {{ __('Go to Dashboard') }}
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>