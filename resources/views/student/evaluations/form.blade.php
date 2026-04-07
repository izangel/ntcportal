@extends('layouts.admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8 antialiased">
    <div class="max-w-4xl mx-auto px-4">
        <form action="{{ route('student.evaluations.store', $courseBlock->id) }}" method="POST">
            @csrf
            
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-8 shadow-sm flex justify-between items-center sticky top-4 z-20">
                <div>
                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">Student Evaluation Form</p>
                    <h1 class="text-xl font-bold text-gray-900 leading-tight">
                        {{ $courseBlock->course->name }}
                    </h1>
                    <p class="text-[11px] font-bold text-gray-400 uppercase mt-1">
                        Instructor: <span class="text-gray-700">{{ $courseBlock->faculty->last_name }}, {{ $courseBlock->faculty->first_name }}</span>
                    </p>
                </div>
                <button type="submit" class="bg-indigo-600 text-white font-black py-3 px-8 rounded-xl text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                    Submit Evaluation
                </button>
            </div>

            @foreach(config('evaluation_questions.student') as $section)
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-4 px-2">
                    <span class="bg-gray-900 text-white text-[10px] font-black px-2 py-1 rounded">SECTION {{ $section['id'] }}</span>
                    <h2 class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ $section['title'] }}</h2>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest w-1/2">Evaluation Criteria</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-center">
                                    <div class="flex justify-between max-w-[240px] mx-auto text-indigo-400">
                                        <span>Poor</span>
                                        <span>Excellent</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($section['questions'] as $q)
                            <tr class="hover:bg-indigo-50/30 transition-colors">
                                <td class="px-6 py-6 text-sm font-bold text-gray-700">
                                    <div class="flex gap-3">
                                        <span class="text-gray-300 font-black text-[10px] mt-1">{{ $q['k'] }}</span>
                                        <span>{{ $q['t'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center justify-center gap-2 max-w-[240px] mx-auto">
                                        @for($i = 1; $i <= 5; $i++)
                                        <label class="relative flex-1 cursor-pointer">
                                            <input type="radio" name="ratings[{{ $q['k'] }}]" value="{{ $i }}" required class="peer sr-only">
                                            <div class="h-10 w-full flex items-center justify-center rounded-lg border-2 border-gray-100 bg-white text-gray-400 font-black text-sm transition-all
                                                peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:text-white">
                                                {{ $i }}
                                            </div>
                                        </label>
                                        @endfor
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach

            
            <div class="space-y-6 mb-12">
                <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">What did the teacher do that helped you learn most?</label>
                    </div>
                    <textarea 
                        name="aspects_helped" 
                        rows="3" 
                        placeholder="e.g., The clear examples provided during lectures, prompt feedback on assignments..." 
                        class="w-full border-gray-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all p-4 bg-gray-50/30"
                    ></textarea>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">What aspects of the course could be improved?</label>
                    </div>
                    <textarea 
                        name="aspects_improved" 
                        rows="3" 
                        placeholder="e.g., More time for laboratory exercises, clearer instructions for the final project..." 
                        class="w-full border-gray-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all p-4 bg-gray-50/30"
                    ></textarea>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">General Comments / Recommendations</label>
                    </div>
                    <textarea 
                        name="comments" 
                        rows="3" 
                        placeholder="Any other feedback you would like to share..." 
                        class="w-full border-gray-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all p-4 bg-gray-50/30"
                    ></textarea>
                </div>
                 <p class="mt-4 text-[10px] text-gray-400 italic font-medium">Your response is anonymous and will be used for institutional development purposes.</p>
            </div>
            
            <div class="flex items-center justify-center gap-8 pb-20">
                <a href="{{ route('student.evaluations.index') }}" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-red-500 transition-colors">Discard Draft</a>
                <div class="h-4 w-px bg-gray-200"></div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Term: {{ $semesterName }} | AY {{ $activeSemester->academicYear->start_year }}-{{ $activeSemester->academicYear->end_year }}</p>
            </div>
        </form>
    </div>
</div>

<style>
    /* Clean up focus outlines */
    textarea:focus { outline: none !important; }
    
    /* Smooth scaling for selected ratings */
    .peer-checked\:bg-indigo-600 + div {
        transform: scale(1.02);
    }
</style>
@endsection