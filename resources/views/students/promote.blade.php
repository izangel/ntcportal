@extends('layouts.plain')

@section('content')
<div class="p-6 bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto">
        {{-- Header & Navigation --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-blue-900 uppercase">Bulk Promotion Portal</h1>
                <p class="text-xs text-gray-500 font-bold">Batch transfer students to new Academic Years or Semesters</p>
            </div>
            <a href="{{ route('students.index') }}" class="text-blue-600 font-black text-xs uppercase hover:underline">
                ← Back to College Registry
            </a>
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

        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
            <form method="GET" action="{{ route('students.promote.view') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Source Academic Year</label>
                    <select name="filter_ay" class="w-full border-gray-300 rounded text-xs font-bold">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $filterAY == $ay->id ? 'selected' : '' }}>
                                {{ $ay->start_year }}-{{ $ay->end_year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Source Semester</label>
                    <select name="filter_semester" class="w-full border-gray-300 rounded text-xs font-bold">
                        {{-- Use the full name here; the controller will look for both full and short versions --}}
                        <option value="1st Semester" {{ $selectedSem == '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                        <option value="2nd Semester" {{ $selectedSem == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                        <option value="Summer" {{ $selectedSem == 'Summer' ? 'selected' : '' }}>Summer</option>
                    </select>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Source Section</label>
                    <select name="section_id" class="w-full border-gray-300 rounded text-xs font-bold">
                        <option value="">-- All Sections --</option>
                        @foreach($sections as $s)
                            <option value="{{ $s->id }}" {{ request('section_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->program->name }} - {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-xs font-black uppercase hover:bg-blue-700">
                    Filter List
                </button>
            </form>
        </div>

        <form action="{{ route('students.bulkPromote') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left: Promotion Settings --}}
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-white p-5 rounded-lg shadow-sm border-t-4 border-blue-600">
                        <h2 class="text-xs font-black uppercase text-gray-400 mb-4 tracking-widest">Target Destination</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] font-black uppercase">Target Section</label>
                                <select name="target_section_id" required class="w-full border-gray-300 rounded text-xs">
                                    <option value="">-- Select Section --</option>
                                    @foreach($sections as $s)
                                        <option value="{{ $s->id }}">{{ $s->program->name }} - {{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase">Target Semester</label>
                                <select name="target_semester" required class="w-full border-gray-300 rounded text-xs">
                                    <option value="1st Semester">1st Semester</option>
                                    <option value="2nd Semester">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase">Enrollment Status</label>
                                <select name="status" class="w-full border-gray-300 rounded text-xs">
                                    <option value="Regular">Regular (Promoted)</option>
                                    <option value="Irregular">Irregular</option>
                                    <option value="Returnee">Returnee</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="w-full mt-6 bg-blue-900 text-white py-3 rounded font-black text-xs uppercase tracking-widest hover:bg-blue-800 transition">
                            Execute Bulk Promotion
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                            <span class="text-xs font-black uppercase text-blue-900">Select Students to Promote</span>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="check-all" class="rounded">
                                <label for="check-all" class="text-[10px] font-bold uppercase cursor-pointer">Select All</label>
                            </div>
                        </div>

                        <div class="max-h-[600px] overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr class="text-[9px] uppercase font-black text-gray-400">
                                        <th class="p-3 text-left w-10">Select</th>
                                        <th class="p-3 text-left">Student ID</th>
                                        <th class="p-3 text-left">Full Name</th>
                                        {{-- Updated Header --}}
                                        <th class="p-3 text-left">Current Program & Section</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($students as $student)
                                    @php
                                        // Get the specific section for this student
                                        $currentEnrollment = $student->sections->first();
                                    @endphp
                                    <tr class="hover:bg-blue-50">
                                        <td class="p-3 text-center">
                                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox rounded text-blue-600">
                                        </td>
                                        <td class="p-3 text-xs font-mono font-bold text-gray-500">{{ $student->student_id }}</td>
                                        <td class="p-3 text-xs font-black uppercase text-gray-900">{{ $student->last_name }}, {{ $student->first_name }}</td>
                                        {{-- Updated Cell: Displays "PROGRAM » SECTION" --}}
                                        <td class="p-3 leading-tight">
                                            @if($currentEnrollment)
                                                <span class="text-[10px] font-black text-blue-900 uppercase block tracking-tighter">
                                                    {{ $currentEnrollment->program->name ?? 'N/A' }}
                                                </span>
                                                <span class="text-[9px] font-bold text-gray-500 uppercase block italic">
                                                    {{ $currentEnrollment->name }}
                                                </span>
                                            @else
                                                <span class="text-[9px] font-bold text-gray-400 uppercase italic">No Section Found</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('check-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection