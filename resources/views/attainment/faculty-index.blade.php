@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Course Attainment Submission') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">My Teaching Load</h3>
                </div>

                {{-- Alert Notifications --}}
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md text-sm shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Compact Filter Form --}}
                <form action="{{ route('attainment.index') }}" method="GET" class="mb-6 flex flex-wrap items-center gap-4 bg-gray-50 p-3 rounded border border-gray-100">
                    <div class="flex items-center space-x-2">
                        <label class="text-[10px] font-bold text-gray-500 uppercase">Academic Year:</label>
                        <select name="academic_year_id" class="text-xs border-gray-300 rounded shadow-sm focus:ring-indigo-500 py-1">
                            <option value="">All Years</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>
                                    {{ $ay->start_year }}-{{ $ay->end_year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center space-x-2 border-l pl-4">
                        <label class="text-[10px] font-bold text-gray-500 uppercase">Semester:</label>
                        <select name="semester" class="text-xs border-gray-300 rounded shadow-sm focus:ring-indigo-500 py-1">
                            <option value="">All Semesters</option>
                            <option value="1st Semester" {{ request('semester') == '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2nd Semester" {{ request('semester') == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="Summer" {{ request('semester') == 'Summer' ? 'selected' : '' }}>Summer</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="submit" class="bg-gray-800 text-white text-[10px] px-4 py-1.5 rounded font-bold uppercase tracking-widest hover:bg-indigo-700 transition">
                            Filter
                        </button>
                        <a href="{{ route('attainment.index') }}" class="text-[10px] text-gray-400 uppercase font-bold hover:underline">
                            Reset
                        </a>
                    </div>
                </form>

                {{-- Data Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="w-1/4 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                <th scope="col" class="w-1/2 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Google Sheet Link</th>
                                <th scope="col" class="w-1/6 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-[12px]">
                            @forelse ($courses as $course)
                                <tr x-data="{ editing: false }">
                                    <td class="px-4 py-3">
                                        <div class="text-[11px] font-bold text-indigo-600 uppercase leading-none">{{ $course->course?->code ?? 'N/A' }}</div>
                                        <div class="text-gray-900 font-semibold truncate max-w-[180px]">{{ $course->course?->name ?? 'Course Not Found' }}</div>
                                        <div class="text-[10px] text-gray-400 font-medium mt-1">{{ $course->schedule_string }}</div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @php $hasLink = $course->attainment?->google_sheet_url; @endphp
                                        
                                        {{-- Clickable Link View --}}
                                        <template x-if="!editing && {{ $hasLink ? 'true' : 'false' }}">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ $hasLink }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-medium underline truncate max-w-sm">
                                                    {{ $hasLink }}
                                                </a>
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            </div>
                                        </template>

                                        {{-- Input Form View --}}
                                        <div x-show="editing || !{{ $hasLink ? 'true' : 'false' }}">
                                            <form id="form-{{ $course->id }}" action="{{ route('attainment.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="course_block_id" value="{{ $course->id }}">
                                                <input type="url" name="google_sheet_url" 
                                                    value="{{ $hasLink }}" 
                                                    placeholder="Paste spreadsheet link here..."
                                                    class="w-full text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-1"
                                                    {{ $course->finalized ? 'disabled' : '' }}>
                                            </form>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        @php $status = strtolower($course->attainment?->status ?? 'pending'); @endphp
                                        
                                        @if($status == 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-green-100 text-green-800 border border-green-200 uppercase">
                                                Approved
                                            </span>
                                        @elseif($status == 'submitted')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 uppercase">
                                                Submitted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase">
                                                Pending
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <template x-if="!editing && {{ $hasLink ? 'true' : 'false' }}">
                                            <button @click="editing = true" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-[10px] text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition">
                                                Update
                                            </button>
                                        </template>

                                        <div x-show="editing || !{{ $hasLink ? 'true' : 'false' }}" class="flex justify-end items-center space-x-3">
                                            <template x-if="editing">
                                                <button @click="editing = false" type="button" class="text-[10px] text-gray-400 uppercase font-bold hover:text-gray-600">Cancel</button>
                                            </template>
                                            <button type="submit" form="form-{{ $course->id }}" 
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-[10px] text-white uppercase tracking-widest hover:bg-indigo-700 shadow-sm disabled:opacity-25 transition"
                                                {{ $course->finalized ? 'disabled' : '' }}>
                                                Save Link
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-sm text-gray-500 text-center italic">No courses found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection