@extends('admin.admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-5xl mx-auto px-4">
        
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6 shadow-sm">
            <h1 class="text-xl font-bold text-gray-900 mb-4">Monitor Student Evaluation</h1>
            <form action="{{ route('admin.monitoring.evaluations') }}" method="GET" class="flex gap-3">
                <div class="flex-1">
                    <select name="student_id" id="student_select" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 appearance-none">
                        <option value="">-- Select a Student --</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>
                                {{ strtoupper($s->last_name) }}, {{ $s->first_name }} 
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                    View Progress
                </button>
            </form>
        </div>

        @if($selectedStudent)
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6 shadow-sm flex justify-between items-center border-l-4 border-l-indigo-600">
                <div>
                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-1">Active Record</p>
                    <h2 class="text-xl font-bold text-gray-900">{{ $selectedStudent->user->first_name }} {{ $selectedStudent->user->last_name }}</h2>
                    <p class="text-xs text-gray-400 font-bold uppercase">{{ $semesterName }} Semester | AY {{ $activeSemester->academicYear->start_year }}-{{ $activeSemester->academicYear->end_year }}</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Completion</p>
                        <p class="text-lg font-black text-gray-900">{{ $completedCount }}/{{ $totalCount }}</p>
                    </div>
                    <div class="relative w-14 h-14 flex items-center justify-center rounded-full bg-indigo-50 border border-indigo-100">
                        <span class="text-[11px] font-black text-indigo-600">
                            {{ $totalCount > 0 ? round(($completedCount/$totalCount)*100) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Instructor & Course</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($enrolledCourses as $block)
                        <tr class="hover:bg-indigo-50/10 transition-colors">
                            <td class="px-6 py-5">
                                <p class="text-sm font-bold text-gray-800">{{ $block->faculty->first_name }} {{ $block->faculty->last_name }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">{{ $block->course->code }}: {{ $block->course->name }}</p>
                            </td>
                            <td class="px-6 py-5 text-center">
                                @if($block->has_evaluated)
                                    <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-600 text-[9px] font-black px-3 py-1 rounded-full border border-green-200 uppercase tracking-widest">
                                        <div class="w-1 h-1 rounded-full bg-green-600"></div> Completed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-600 text-[9px] font-black px-3 py-1 rounded-full border border-amber-200 uppercase tracking-widest">
                                        <div class="w-1 h-1 rounded-full bg-amber-600"></div> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-right">
                                <span class="text-[10px] font-black text-gray-300 uppercase italic">
                                    {{ $block->has_evaluated ? 'Feedback Recorded' : 'Not yet accessed' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <p class="text-gray-400 text-sm italic">This student has no active enrollments for this semester.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-white p-20 text-center rounded-2xl border-2 border-dashed border-gray-200">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h3 class="text-gray-900 font-bold">No Student Selected</h3>
                <p class="text-gray-400 text-sm">Select a student from the dropdown to monitor their evaluation status.</p>
            </div>
        @endif
    </div>
</div>

{{-- Add TomSelect or Select2 for better UX if desired --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    new TomSelect("#student_select",{
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
</script>
@endsection