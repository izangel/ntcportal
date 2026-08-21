@php
    $setupAy = \App\Services\AcademicYearSetup::activeYear();
    $setupChecklist = \App\Services\AcademicYearSetup::checklist($setupAy);
@endphp
@if($setupAy && !$setupChecklist['complete'])
    <div class="bg-amber-50 border-b border-amber-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-amber-800">
                        <i class="fas fa-triangle-exclamation mr-1"></i>
                        A.Y. {{ $setupAy->start_year }}-{{ $setupAy->end_year }} setup incomplete — {{ $setupChecklist['done_count'] }} of {{ $setupChecklist['total'] }} tasks done.
                    </p>
                    <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                        @foreach($setupChecklist['items'] as $item)
                            <li class="flex items-center gap-1.5 text-xs text-amber-800">
                                @if($item['done'])
                                    <i class="fas fa-circle-check text-green-600"></i>
                                @else
                                    <i class="fas fa-circle-xmark text-red-500"></i>
                                @endif
                                {{ $item['label'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <a href="{{ route('academic_years.setup') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 text-white text-xs font-bold rounded-md hover:bg-amber-700">
                    <i class="fas fa-list-check"></i>
                    View Setup Guide
                </a>
            </div>
        </div>
    </div>
@endif