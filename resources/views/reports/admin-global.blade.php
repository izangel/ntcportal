@extends('admin.admin')

@section('header')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="font-black text-2xl text-gray-900 tracking-tight">Institutional Evaluation Report</h2>
            <p class="text-sm text-purple-600 font-bold uppercase tracking-widest">Administrative Overview | {{ $activeSem->name }}</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl text-xs font-bold uppercase hover:bg-gray-50 transition">
                Print Report
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Global Average</p>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-purple-600">{{ number_format($reports->avg('average_rating'), 2) }}</span>
                    <svg class="w-5 h-5 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Total Participation</p>
                <span class="text-2xl font-black text-gray-900">{{ $reports->sum('total_responses') }}</span>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Active Courses</p>
                <span class="text-2xl font-black text-gray-900">{{ $reports->count() }}</span>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Highest Rating</p>
                <span class="text-2xl font-black text-emerald-500">{{ number_format($reports->max('average_rating'), 1) }}</span>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden mb-12">
            <div class="px-8 py-6 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-black text-gray-900 uppercase tracking-tighter">Course Satisfaction Matrix</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/30 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-8 py-4 text-left">Instructor & Subject</th>
                            <th class="px-8 py-4 text-left">Rating Metric</th>
                            <th class="px-8 py-4 text-center">Data Points</th>
                            <th class="px-8 py-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($reports as $report)
                        <tr class="hover:bg-purple-50/30 transition-colors">
                            <td class="px-8 py-5">
                                <div class="text-sm font-black text-gray-900">{{ $report->faculty_name }}</div>
                                <div class="text-[10px] font-bold text-purple-500 uppercase">{{ $report->course_code }} — {{ $report->course_name }}</div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3.5 h-3.5" 
                                                style="fill: {{ $i <= round($report->average_rating) ? '#fbbf24' : '#E5E7EB' }};" 
                                                viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-sm font-black text-gray-700">{{ number_format($report->average_rating, 2) }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center text-xs font-bold text-gray-500">
                                {{ $report->total_responses }}
                            </td>
                            <td class="px-8 py-5 text-right">
                                @if($report->average_rating >= 4.5)
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-lg uppercase">Excellent</span>
                                @elseif($report->average_rating >= 3.0)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-[10px] font-black rounded-lg uppercase">Satisfactory</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 text-[10px] font-black rounded-lg uppercase">Needs Review</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <h3 class="font-black text-gray-900 uppercase tracking-tighter mb-6 px-2">Global Student Feedback Feed</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($recentComments as $comment)
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-[9px] font-black bg-purple-50 text-purple-600 px-2 py-1 rounded-md uppercase">{{ $comment->course->code }}</span>
                        <span class="text-[14px] font-bold text-amber-500">{{ $comment->rating }} ★</span>
                    </div>
                    <p class="text-sm text-gray-600 italic leading-relaxed">"{{ $comment->comments }}"</p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-50 flex justify-between items-center">
                    <span class="text-[9px] text-gray-300 font-bold uppercase">{{ $comment->created_at->format('M d, Y') }}</span>
                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Anonymous Student</span>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</div>
@endsection