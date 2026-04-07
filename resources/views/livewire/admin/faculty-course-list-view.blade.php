<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">🧑‍🏫 Grade Submission Tracking</h2>
    
    {{-- FILTER courseBlock (Unchanged) --}}
    <div class="bg-white shadow-xl rounded-xl p-6 mb-8 border-t-4 border-blue-600">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Select Academic Period & Search</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            
            {{-- Academic Year Filter --}}
            <div>
                <label for="ay" class="courseBlock text-sm font-medium text-gray-700">Academic Year</label>
                <select id="ay" wire:model.live="academicYearId" class="mt-1 courseBlock w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select AY</option>
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }} - {{ $ay->end_year }}</option> 
                    @endforeach
                </select>
            </div>
            
            {{-- Semester Filter --}}
            <div>
                <label for="sem" class="courseBlock text-sm font-medium text-gray-700">Semester</label>
                <select id="sem" wire:model.live="semester" class="mt-1 courseBlock w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Semester</option>
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }} Semester</option>
                    @endforeach
                </select>
            </div>

            {{-- Search Input --}}
            <div class="md:col-span-2">
                <label for="search" class="courseBlock text-sm font-medium text-gray-700">Search Faculty (Name or ID)</label>
                <input type="text" id="search" wire:model.live.debounce.300ms="search" placeholder="Type faculty name or ID..."
                       class="mt-1 courseBlock w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
        </div>
    </div>

    {{-- Faculty List Table (Final Revised) --}}
    @if ($academicYearId && $semester)
        <div class="bg-white shadow-lg rounded-xl overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Faculty Name (ID)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-4/5">Handled Courses for {{ $semester }} Semester</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($facultyList as $faculty)
                        <tr class="@if($loop->odd) bg-gray-50 @endif">
                            {{-- Faculty Name Column --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $faculty->last_name }}, {{ $faculty->first_name }}  {{ $faculty->mid_name }} 
                                <span class="text-gray-500 courseBlock text-xs">ID: {{ $faculty->employee_id ?? 'N/A' }}</span>
                            </td>

                            {{-- Courses Table Column (Nested) --}}
                            <td class="p-4">
                                {{-- The 'courseBlocks' relationship is already filtered by the Livewire component --}}
                                @if ($faculty->courseBlocks->isNotEmpty())
                                    <table class="w-full text-sm border border-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="p-2 text-left text-xs font-semibold text-gray-600 w-1/6">Code</th>
                                                <th class="p-2 text-left text-xs font-semibold text-gray-600 w-2/6">Course Title</th>
                                                <th class="p-2 text-left text-xs font-semibold text-gray-600 w-1/6">Section/Room</th>
                                                <th class="p-2 text-left text-xs font-semibold text-gray-600 w-1/6">Schedule</th>
                                                <th class="p-2 text-center text-xs font-semibold text-gray-600 w-1/6">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($faculty->courseBlocks->sortBy('course.code') as $courseBlock)
                                                <tr class="border-t border-gray-200 hover:bg-white">
                                                    <td class="p-2 font-semibold">{{ $courseBlock->course->code ?? 'N/A' }}</td>
                                                    <td class="p-2">{{ $courseBlock->course->name ?? 'N/A' }}</td>
                                                    
                                                    {{-- NEW COLUMN: Section & Room --}}
                                                    <td class="p-2 text-xs">
                                                        {{ $courseBlock->section->program->name ?? 'N/A' }}-{{ $courseBlock->section->name ?? 'N/A' }} / 
                                                        **{{ $courseBlock->room_name ?? 'N/A' }}**
                                                    </td>
                                                    
                                                    <td class="p-2 text-xs">{{ $courseBlock->schedule_string ?? 'N/A' }}</td>
                                                    
                                                    {{-- REMARKS Column Logic --}}
                                                    <td class="p-2 text-center">
                                                        @if ($courseBlock->finalized)
                                                            <span class="font-bold text-green-600">Submitted</span>
                                                        @else
                                                            <span class="text-gray-400 italic"></span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    {{-- This message should technically never appear due to the whereHas constraint in the component --}}
                                    <p class="italic text-gray-500 p-2">No courses assigned to this faculty for the selected period.</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-10 text-center text-gray-500">
                                No faculty found with course assignments for the selected period or matching the search query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="p-4">
                {{ $facultyList->links() }}
            </div>
            
        </div>
    @else
        <p class="text-center text-gray-500 mt-10 p-6 bg-white shadow-lg rounded-xl">
            Please select an **Academic Year** and **Semester** to view the course assignments.
        </p>
    @endif
</div>