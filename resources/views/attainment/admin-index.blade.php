@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('OBE Attainment Monitoring') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        {{-- Using max-w-full to ensure all columns fit comfortably --}}
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-2">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest">
                        College Compliance Audit
                    </h3>
                    <span class="text-[10px] text-indigo-500 font-bold uppercase italic">Excluding SHS Courses</span>
                </div>

                {{-- Compact Filter Form --}}
                <form action="{{ route('attainment.admin') }}" method="GET" class="mb-6 flex flex-wrap items-center gap-4 bg-gray-50 p-3 rounded border border-gray-100">
                    <div class="flex items-center space-x-2">
                        <label class="text-[10px] font-bold text-gray-500 uppercase">Year:</label>
                        <select name="academic_year_id" class="text-xs border-gray-300 rounded shadow-sm py-1">
                            <option value="">All Years</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>
                                    {{ $ay->start_year }}-{{ $ay->end_year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center space-x-2 border-l pl-4">
                        <label class="text-[10px] font-bold text-gray-500 uppercase">Term:</label>
                        <select name="semester" class="text-xs border-gray-300 rounded shadow-sm py-1">
                            <option value="">All Semesters</option>
                            <option value="1st Semester" {{ request('semester') == '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2nd Semester" {{ request('semester') == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="Summer" {{ request('semester') == 'Summer' ? 'selected' : '' }}>Summer</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-800 text-white text-[10px] px-4 py-1.5 rounded font-bold uppercase tracking-widest hover:bg-gray-700 transition">
                        Filter
                    </button>
                    <a href="{{ route('attainment.admin') }}" class="text-[10px] text-gray-400 uppercase font-bold hover:underline">Reset</a>
                </form>

                {{-- Data Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider w-1/5">Faculty Member</th>
                                <th scope="col" class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Course</th>
                                <th scope="col" class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Schedule & Room</th>
                                <th scope="col" class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Program & Sections</th>
                                <th scope="col" class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider w-24">Status</th>
                                <th scope="col" class="px-3 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider w-24">Evidence</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($submissions as $item)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    {{-- 1. Faculty (Sorted by Last Name in Controller) --}}
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <div class="text-[11px] font-bold text-gray-900 uppercase">
                                            {{ $item->faculty->last_name ?? 'N/A' }}, {{ $item->faculty->first_name ?? '' }}
                                        </div>
                                    </td>

                                    {{-- 2. Course Code and Name --}}
                                    <td class="px-3 py-3">
                                        <div class="text-[10px] font-black text-indigo-600 uppercase leading-none mb-1">
                                            {{ $item->course?->code ?? '---' }}
                                        </div>
                                        <div class="text-[11px] text-gray-600 font-medium truncate max-w-[150px]" title="{{ $item->course?->name }}">
                                            {{ $item->course?->name ?? 'Not Set' }}
                                        </div>
                                    </td>

                                    {{-- 3. Schedule and Room --}}
                                    <td class="px-3 py-3">
                                        <div class="text-[10px] font-bold text-gray-700 leading-tight">
                                            {{ $item->schedule_string ?? 'No Schedule' }}
                                        </div>
                                        <div class="text-[9px] text-gray-400 font-bold uppercase mt-0.5">
                                            Rm: {{ $item->room_name ?? 'TBA' }}
                                        </div>
                                    </td>

                                    {{-- 4. Program and Multiple Sections (Pill Style) --}}
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap gap-1 max-w-[250px]">
                                            @forelse($item->sections as $section)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-black bg-gray-100 text-gray-600 border border-gray-200 uppercase">
                                                    {{ $section->program?->name ?? '??' }}-{{ $section->name }}
                                                </span>
                                            @empty
                                                <span class="text-[9px] text-gray-300 italic">Unassigned</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    {{-- 5. Status Badge --}}
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        @php $status = strtolower($item->attainment?->status ?? 'pending'); @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black border uppercase 
                                            {{ $status == 'approved' ? 'bg-green-100 text-green-800 border-green-200' : 
                                              ($status == 'submitted' ? 'bg-indigo-100 text-indigo-800 border-indigo-200' : 
                                               'bg-amber-100 text-amber-800 border-amber-200') }}">
                                            {{ $status }}
                                        </span>
                                    </td>

                                    {{-- 6. Direct Link --}}
                                    <td class="px-3 py-3 text-right whitespace-nowrap">
                                        @if($item->attainment?->google_sheet_url)
                                            <a href="{{ $item->attainment->google_sheet_url }}" target="_blank" 
                                               class="text-[10px] font-bold text-indigo-600 hover:text-indigo-900 uppercase underline decoration-indigo-200">
                                                Review Sheet
                                            </a>
                                        @else
                                            <span class="text-[9px] text-gray-300 font-bold uppercase italic tracking-tighter">No Link</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-[11px] text-gray-400 text-center italic">
                                        No records found for the selected criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection