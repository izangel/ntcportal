<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors font-medium text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                        {{ __('SSG Election Results') }}
                    </h2>
                    <p class="text-sm text-gray-500 font-medium">
                        @if($activeAcademicYear)
                            Academic Year {{ $activeAcademicYear->start_year }}-{{ $activeAcademicYear->end_year }}
                        @else
                            All-time Records
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div>
        <livewire:faculty-voting-results />
    </div>
</x-app-layout>