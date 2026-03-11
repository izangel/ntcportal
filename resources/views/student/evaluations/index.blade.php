@extends('layouts.admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-5xl mx-auto px-4">
        
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6 shadow-sm flex justify-between items-center">
            <div>
                <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">Student Feedback</p>
                <h1 class="text-xl font-bold text-gray-900">
                    Academic Evaluation:
                    {{ $semesterName !== 'N/A' ? $semesterName . ' Semester' : 'No Active Semester' }},
                    {{ $activeSemester?->academicYear?->start_year ?? 'N/A' }}-{{ $activeSemester?->academicYear?->end_year ?? 'N/A' }}
                </h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Progress</p>
                    <p class="text-sm font-bold text-gray-900">{{ $completedCount }}/{{ $totalCount }} Courses</p>
                </div>
                <div class="w-10 h-10 rounded-full border-2 border-indigo-100 flex items-center justify-center">
                    <span class="text-[10px] font-black text-indigo-600">{{ $totalCount > 0 ? round(($completedCount/$totalCount)*100) : 0 }}%</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Instructor & Course</th>
                        <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($enrolledCourses as $block)
                    <tr class="hover:bg-indigo-50/20 transition-colors">
                        <td class="px-6 py-5">
                            <p class="text-sm font-bold text-gray-800">{{ $block->faculty->first_name }} {{ $block->faculty->last_name }}</p>
                            <p class="text-[11px] font-medium text-gray-400">{{ $block->course->code }}: {{ $block->course->name }}</p>
                        </td>
                        <td class="px-6 py-5 text-center">
                            @if($block->has_evaluated)
                                <span class="bg-green-50 text-green-600 text-[9px] font-black px-3 py-1 rounded-full border border-green-100 uppercase tracking-widest">Completed</span>
                            @else
                                <span class="bg-amber-50 text-amber-600 text-[9px] font-black px-3 py-1 rounded-full border border-amber-100 uppercase tracking-widest">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-right">
                            @if($block->has_evaluated)
                                <span class="text-[10px] font-black text-gray-300 uppercase italic">Submitted</span>
                            @else
                                <a href="{{ route('student.evaluations.create', $block->id) }}" class="bg-indigo-600 text-white font-black py-2 px-6 rounded-lg text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-100">
                                    Start Review
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-gray-400 text-sm italic">No courses available for evaluation.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
