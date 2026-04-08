@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Manage Students') }}
    </h2>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-4 p-2 bg-green-100 border-l-4 border-green-500 text-green-700 text-sm rounded shadow-sm flex justify-between items-center" id="status-message">
                <span>{{ session('success') }}</span>
                <button onclick="document.getElementById('status-message').remove()" class="font-bold">&times;</button>
            </div>
        @endif

        <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 mb-4">
            <form action="{{ route('students.studentportal') }}" method="GET" class="flex flex-col md:flex-row items-end gap-3">
                
                <div class="w-full md:w-64">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Academic Year</label>
                    <select name="academic_year_id" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm py-1">
                        <option value="">-- Select Year --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $selectedYear == $year->id ? 'selected' : '' }}>
                                AY {{ $year->start_year }}-{{ $year->end_year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-64">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Semester</label>
                    <select name="semester" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm py-1">
                        <option value="">-- Select Semester --</option>
                        <option value="1st" {{ $selectedSemester == '1st' ? 'selected' : '' }}>1st Semester</option>
                        <option value="2nd Semester" {{ $selectedSemester == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                    </select>
                </div>

                @if($selectedYear && $selectedSemester)
                    <div class="w-full md:w-64">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Filter by Section</label>
                        <select name="section_id" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm py-1">
                            <option value="">All Sections</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec->id }}" {{ $selectedSection == $sec->id ? 'selected' : '' }}>
                                    {{ $sec->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                   
                    <div class="w-full md:w-64">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Search Name/ID</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ $searchTerm }}" 
                                placeholder="Type to search..."
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm py-1 pl-8">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="hidden"></button>
               

                    <a href="{{ route('students.studentportal') }}" class="text-xs text-slate-500 hover:text-red-600 pb-2 underline">
                        Clear All
                    </a>
                @endif
            </form>
        </div>

        @if($selectedYear && $selectedSemester)
            <div class="bg-white shadow-sm border border-slate-200 rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="divide-x divide-slate-200">
                            <th class="px-3 py-2 text-left text-[10px] font-bold text-slate-500 uppercase">Full Name</th>
                            <th class="px-3 py-2 text-left text-[10px] font-bold text-slate-500 uppercase w-32">Student ID</th>
                            <th class="px-3 py-2 text-left text-[10px] font-bold text-slate-500 uppercase w-48">Section Assignment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            <tr class="hover:bg-slate-50 transition-colors divide-x divide-slate-100">
                                <td class="px-3 py-0.5 whitespace-nowrap text-sm text-slate-900">
                                    <span class="font-bold text-slate-700">{{ strtoupper($student->last_name) }}</span>, {{ $student->first_name }}
                                </td>
                                <td class="px-3 py-0.5 whitespace-nowrap text-xs font-mono text-slate-500">
                                    {{ $student->student_id }}
                                </td>
                                <td class="px-3 py-0.5 whitespace-nowrap">
                                    <form action="{{ route('students.updateSection', $student->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="academic_year_id" value="{{ $selectedYear }}">
                                        <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                                        
                                        <select name="section_id" onchange="this.form.submit()" 
                                                class="text-[11px] rounded border-gray-300 py-0 px-1 focus:ring-indigo-500 focus:border-indigo-500 bg-transparent hover:bg-white transition cursor-pointer h-6 w-full">
                                            @php $currentSecId = $student->sections->first()->id ?? null; @endphp
                                            <option value="" disabled {{ is_null($currentSecId) ? 'selected' : '' }}>-- Assign --</option>
                                            @foreach($sections as $sec)
                                                <option value="{{ $sec->id }}" {{ $currentSecId == $sec->id ? 'selected' : '' }}>
                                                    {{ $sec->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-6 text-center text-slate-400 text-xs italic">
                                    No students found for this selection.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="bg-slate-50 px-3 py-1 border-t border-slate-200 flex justify-between items-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Count: {{ $students->count() }}</span>
                </div>
            </div>
        @else
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-lg p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">No Academic Period Selected</h3>
                <p class="mt-1 text-sm text-slate-500">Please select an Academic Year and Semester above to view the student list.</p>
            </div>
        @endif
    </div>
</div>
@endsection