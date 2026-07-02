{{-- resources/views/students/index.blade.php --}}

@extends('layouts.plain')

@section('content')
<div class="p-2 bg-gray-100 min-h-screen font-sans">
    <div class="mx-auto max-w-[1700px] flex flex-col lg:flex-row gap-3">

        
        
        {{-- LEFT SIDE: REGISTRY CONTENT (75%) --}}
        <div class="lg:w-3/4">
            {{-- 1. DASHBOARD ANALYTICS (College Focused) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-3">
                <div class="bg-white p-3 rounded shadow-sm border-l-4 border-blue-900">
                    <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest">College Enrolled</p>
                    <p class="text-xl font-black text-blue-900 leading-none">{{ $stats['total'] }}</p>
                </div>
                {{-- Note: Use $students->sum(...) or pass specific gender counts from Controller for accuracy with pagination --}}
                <div class="bg-white p-3 rounded shadow-sm border-l-4 border-blue-500">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Male</p>
                    <p class="text-xl font-black text-blue-700 leading-none">{{ $students->where('gender', 'Male')->count() }}</p>
                </div>
                <div class="bg-white p-3 rounded shadow-sm border-l-4 border-pink-500">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Female</p>
                    <p class="text-xl font-black text-pink-600 leading-none">{{ $students->where('gender', 'Female')->count() }}</p>
                </div>
                <div class="bg-blue-900 p-3 rounded shadow-sm text-white">
                    <p class="text-[8px] font-black text-blue-300 uppercase italic">College Context</p>
                    <p class="text-[10px] font-bold">{{ $context['ay']->start_year }}-{{ $context['ay']->end_year }} | {{ $context['semester'] }}</p>
                </div>
            </div>

            {{-- ALERTS BLOCK --}}
            @if(session('success'))
                <div class="mb-4 flex items-center bg-green-500 text-white text-xs font-black px-4 py-3 rounded shadow-md border-l-4 border-green-700" role="alert">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <p class="uppercase tracking-widest">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 flex items-center bg-red-500 text-white text-xs font-black px-4 py-3 rounded shadow-md border-l-4 border-red-700" role="alert">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="uppercase tracking-widest">{{ session('error') }}</p>
                </div>
            @endif

            {{-- 2. TOOLBAR --}}
            <div class="flex justify-between items-center mb-2 bg-white p-2 rounded shadow-sm border border-gray-200">
                <div class="flex items-center gap-4">
                    <h1 class="text-sm font-black text-blue-900 uppercase tracking-tighter">College Registry</h1>
                    <div class="flex gap-1 border-l pl-4 border-gray-200">
                        <button type="button" 
                            onclick="Livewire.dispatch('openEnrollModal')" 
                            class="bg-blue-900 text-white px-3 py-1.5 rounded text-[10px] font-black uppercase shadow-sm hover:bg-blue-800">
                            Enroll Old Student
                        </button>
                        <a href="{{ route('students.create') }}" class="bg-blue-600 text-white px-3 py-1.5 rounded text-[10px] font-black uppercase shadow-sm hover:bg-blue-700">
                            New Student
                        </a>
                        <a href="{{ route('students.promote.view') }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-[10px] font-black uppercase shadow-sm hover:bg-indigo-700">
                            Bulk Promote Old Students
                        </a>
                        <a href="{{ route('students.export', request()->all()) }}" class="bg-green-600 text-white px-3 py-1.5 rounded text-[10px] font-black uppercase tracking-widest">Export</a>
                        <button onclick="window.print()" class="bg-gray-800 text-white px-3 py-1.5 rounded text-[10px] font-black uppercase tracking-widest hover:bg-black">
                            Print ClassList
                        </button>
                    </div>
                </div>
                
                <form method="GET" action="{{ route('students.index') }}" class="flex gap-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID/Name..." class="px-2 py-1 border-gray-300 rounded text-xs w-48">
                    <button type="submit" class="bg-blue-900 text-white px-3 py-1 rounded text-[10px] font-black uppercase">Search</button>
                </form>
            </div>

            <div class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden">
                {{-- SECTION FILTER (Already filtered to College in Controller) --}}
                <div class="p-1.5 bg-gray-50 border-b">
                    <select onchange="location = this.value;" class="text-[11px] border-gray-300 rounded py-0.5 w-full max-w-sm font-bold text-blue-900">
                        <option value="{{ route('students.index') }}">-- ALL COLLEGE SECTIONS --</option>
                        @foreach($sections as $s)
                            <option value="{{ request()->fullUrlWithQuery(['section_id' => $s->id]) }}" {{ request('section_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->program->name }} » {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-[8px] uppercase font-black text-gray-500 tracking-widest">
                            <tr>
                                <th class="p-2 w-8"><input type="checkbox" id="select-all"></th>
                                <th class="p-2 text-left w-12">ID No.</th>
                                <th class="p-2 text-left">Full Name</th>
                                <th class="p-2 text-left">Program & Section</th>
                                <th class="p-2 text-right">Actions</th>
                                <th class="p-2 text-right">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            {{-- REMOVED MANUAL SORTBY TO PRESERVE CONTROLLER ORDER (ACT -> BSIS) --}}
                            @forelse ($students as $student)
                                <tr class="text-[11px] hover:bg-blue-50 group">
                                    <td class="p-2 text-center"><input type="checkbox" class="student-checkbox"></td>
                                    <td class="p-2 font-mono text-gray-400 font-bold uppercase">{{ $student->student_id ?? $student->id }}</td>
                                    <td class="p-2">
                                        <div class="font-black uppercase text-gray-900">{{ $student->last_name }}, {{ $student->first_name }}</div>
                                        <div class="text-[8px] text-blue-600 font-bold">{{ $student->gender }}</div>
                                    </td>
                                    <td class="p-2 leading-tight">
                                        <span class="text-[10px] font-black text-blue-900 uppercase block tracking-tighter">
                                            {{ $student->prog_name }}
                                        </span>
                                        <span class="text-[9px] font-bold text-gray-500 uppercase block italic">{{ $student->sec_name }}</span>
                                    </td>
                                    <td class="p-2 text-right">
                                        <div class="flex justify-end items-center gap-1">
                                            <a href="{{ route('students.show', $student) }}" class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-[8px] font-black uppercase">View</a>
                                            <a href="{{ route('students.edit', $student) }}" class="bg-blue-600 text-white px-2 py-0.5 rounded text-[8px] font-black uppercase shadow-sm">Edit</a>
                                            <a href="{{ route('students.cor', $student) }}" target="_blank" class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-[8px] font-black uppercase hover:bg-amber-200">COR</a>
                                        </div>
                                    </td>
                                    <td class="p-2 text-right">
                                        @php
                                            $docs = $student->requirements_submitted ?? [];
                                            $totalDocs = count($docs);
                                            $submitted = count(array_filter($docs));
                                        @endphp
                                        <span class="text-[9px] font-black {{ $submitted == $totalDocs ? 'text-green-600' : 'text-amber-600' }}">{{ $submitted }}/{{ $totalDocs }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-12 text-center text-gray-400 font-black uppercase italic">No college students found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $students->links() }}</div>
        </div>

        {{-- SIDEBAR: COLLEGE SUMMARY (25%) --}}
        <div class="lg:w-1/4">
            <div class="sticky top-2 space-y-3">
               

                <div class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-blue-900 p-2.5">
                        <h3 class="text-[10px] font-black text-white uppercase tracking-widest">College Enrollment Stats</h3>
                    </div>
                    
                    <div class="max-h-[calc(100vh-280px)] overflow-y-auto custom-scrollbar">
                        <table class="min-w-full">
                            <tbody class="divide-y divide-gray-50">
                                {{-- Filter summary to College only --}}
                                @foreach($sections->sortBy('program.name') as $statSection)
                                    @php
                                        $nSem = ($context['semester'] == 'Second Semester') ? '2nd Semester' : '1st Semester';
                                        $secCount = DB::table('section_student')
                                            ->where('section_id', $statSection->id)
                                            ->where('academic_year_id', $context['ay']->id)
                                            ->where('semester', $nSem)
                                            ->count();
                                    @endphp
                                    @if($secCount > 0)
                                    <tr class="hover:bg-blue-50/50">
                                        <td class="p-2 text-[9px] font-bold text-gray-700 uppercase">
                                            {{ $statSection->program->name }} - {{ $statSection->name }}
                                        </td>
                                        <td class="p-2 text-right text-xs font-black text-blue-900">{{ $secCount }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-gray-100 p-2 border-t flex justify-between items-center">
                        <span class="text-[9px] font-black uppercase text-gray-500">Total College</span>
                        <span class="text-sm font-black text-blue-900">{{ $students->total() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@livewire('enroll-old-student-modal')

<script>
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.student-checkbox');
    
    if(selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(c => c.checked = selectAll.checked);
        });
    }
</script>
@endsection