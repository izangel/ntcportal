@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase tracking-tight">
        My Academic Load
    </h2>
@endsection

@section('content')
<div class="py-12" x-data="{ 
    showEvalModal: false, 
    courseId: null, 
    courseName: '', 
    rating: 0, 
    hoverRating: 0,
    labels: {
        1: 'Poor / Unsatisfied', 
        2: 'Fair / Below Average', 
        3: 'Good / Average', 
        4: 'Very Good / Above Average', 
        5: 'Excellent / Outstanding'
    } 
}">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        @if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
        <h3 class="text-sm font-bold text-red-800 uppercase">Submission Errors:</h3>
        <ul class="mt-2 list-disc list-inside text-xs text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        </div>
    @endif

   
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-400 text-emerald-700 shadow-sm rounded-r-lg" role="alert">
                <p class="font-bold">Success!</p>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
            <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-extrabold text-gray-900">{{ $activeSemester->name }}</h3>
                <p class="text-sm text-indigo-600 font-medium tracking-wide uppercase">
                    Academic Year {{ $activeSemester->academicYear->name }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="px-8 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Course Code</th>
                            <th class="px-8 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Course Title</th>
                            <th class="px-8 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Instructor</th>
                            <th class="px-8 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($enrolledCourses as $block)
                            <tr class="hover:bg-indigo-50/20 transition-colors duration-150">
                                <td class="px-8 py-5 text-sm font-black text-indigo-600">
                                    {{ $block->course->code }}
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-sm font-semibold text-gray-900">{{ $block->course->name }}</span>
                                    <p class="text-[10px] text-gray-400 italic mt-0.5">{{ $block->schedule_string }}</p>
                                </td>
                                <td class="px-8 py-5 text-sm text-gray-600 font-medium">
                                    {{ $block->faculty->name }}
                                </td>
                                <td class="px-8 py-5 text-right">
                                    @if($block->has_evaluated)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-gray-100 text-gray-400 uppercase tracking-widest border border-gray-200">
                                            Evaluated
                                        </span>
                                    @else
                                        <button 
                                            @click="showEvalModal = true; courseId = '{{ $block->course->id }}'; courseName = '{{ $block->course->name }}'; rating = 0"
                                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded-xl shadow-md shadow-indigo-100 transition-all uppercase tracking-tighter"
                                        >
                                            Evaluate
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-10 text-center text-gray-400 italic text-sm">
                                    No courses found for the current term.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div x-show="showEvalModal" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4" 
     x-data="{ 
        step: 'A',
        responses: {}, 
        hoverState: {},
        labels: {
            1: 'Strongly Disagree', 
            2: 'Disagree', 
            3: 'Neutral', 
            4: 'Agree', 
            5: 'Strongly Agree'
        },
        sections: [
        { 
            id: 'A', title: 'Course Design & Content', 
            questions: [
                { k: 'q1', t: 'The course objectives were clearly explained.' },
                { k: 'q2', t: 'The course content was relevant to my program.' },
                { k: 'q3', t: 'The workload was appropriate for the course level.' },
                { k: 'q4', t: 'Learning activities helped me understand the lessons.' }
            ] 
        },
        { 
            id: 'B', title: 'Teaching Effectiveness', 
            questions: [
                { k: 'q5', t: 'The instructor explained concepts clearly.' },
                { k: 'q6', t: 'The instructor was prepared for each class.' },
                { k: 'q7', t: 'The instructor encouraged participation and questions.' },
                { k: 'q8', t: 'The instructor used appropriate teaching strategies.' }
            ] 
        },
        { 
            id: 'C', title: 'Assessment & Feedback', 
            questions: [
                { k: 'q9', t: 'Assessments were aligned with course objectives.' },
                { k: 'q10', t: 'Grading criteria were clear and fair.' },
                { k: 'q11', t: 'Feedback helped me improve my performance.' }
            ] 
        },
        { 
            id: 'D', title: 'Learning Resources', 
            questions: [
                { k: 'q12', t: 'Learning materials (slides, modules, LMS) were helpful.' },
                { k: 'q13', t: 'Technology/tools used supported my learning.' }
            ] 
        },
        { 
            id: 'E', title: 'Learning Outcomes', 
            questions: [
                { k: 'q14', t: 'I achieved the learning outcomes of this course.' },
                { k: 'q15', t: 'This course helped develop my knowledge and skills.' }
            ] 
        },
        { 
            id: 'F', title: 'Overall Evaluation', 
            questions: [
                { k: 'q16', t: 'Overall, I am satisfied with this course.' },
                { k: 'q17', t: 'I would recommend this course to other students.' }
            ] 
        }
    ]
     }"
     x-cloak>
    
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showEvalModal = false"></div>

    <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md flex flex-col max-h-[90vh] overflow-hidden transform transition-all">
        
        <div class="px-8 pt-8 pb-4 border-b border-gray-50 text-center">
            <h3 class="text-lg font-black text-gray-900" x-text="courseName"></h3>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Course Evaluation</p>
        </div>

        <div class="px-8 mb-4">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Completion</span>
                <span class="text-[10px] font-black text-indigo-600" x-text="Math.round((Object.keys(responses).length / 17) * 100) + '%'"></span>
            </div>
            <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                <div class="bg-indigo-500 h-full transition-all duration-500" 
                    :style="'width: ' + (Object.keys(responses).length / 17) * 100 + '%'"></div>
            </div>
        </div>

        <form action="{{ route('student.courses.evaluate') }}" method="POST" class="flex-1 overflow-y-auto px-8 py-6 space-y-8">
            @csrf
            <input type="hidden" name="course_id" :value="courseId">
            
            <div class="bg-indigo-50 p-4 rounded-2xl">
                <p class="text-[11px] text-indigo-700 leading-relaxed italic">
                    <strong>Scale:</strong> 5 (Strongly Agree) to 1 (Strongly Disagree). 
                    Please rate each statement honestly.
                </p>
            </div>

            <template x-for="section in sections" :key="section.id">
                <div class="space-y-6">
                    <h4 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-l-4 border-indigo-600 pl-3" x-text="section.id + '. ' + section.title"></h4>
                    
                    <template x-for="q in section.questions" :key="q.k">
                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-gray-700 leading-tight" x-text="q.t"></p>
                            
                            <div class="flex flex-col">
                                <div class="flex gap-1">
                                    <template x-for="i in 5">
                                        <button type="button" 
                                            @click="responses[q.k] = i" 
                                            @mouseenter="hoverState[q.k] = i" 
                                            @mouseleave="hoverState[q.k] = 0"
                                            class="focus:outline-none transition-transform active:scale-90">
                                            <svg class="w-8 h-8 transition-all duration-200"
                                                :style="(hoverState[q.k] || responses[q.k]) >= i 
                                                    ? 'fill: #fbbf24; color: #fbbf24;' 
                                                    : 'fill: #ffffff; color: #E5E7EB;'"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 6.541a.562.562 0 00.95.69h6.905c.483 0 .682.621.291.896l-5.584 4.058a.562.562 0 00-.204.629l2.126 6.541c.155.478-.394.877-.791.565l-5.584-4.058a.562.562 0 00-.629 0l-5.584 4.058c-.397.312-.947-.087-.791-.565l2.126-6.541a.562.562 0 00-.204-.629L1.22 11.127c-.391-.275-.192-.896.291-.896h6.905a.562.562 0 00.95-.69L11.48 3.5z" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>

                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-[10px] font-bold text-amber-600 h-3" 
                                        x-text="labels[hoverState[q.k] || responses[q.k]] || ''"></span>
                                    
                                    <template x-if="!responses[q.k]">
                                        <span class="text-[9px] font-black text-red-400 uppercase tracking-tighter">Required</span>
                                    </template>
                                </div>
                            </div>

                            <input type="hidden" :name="'ratings[' + q.k + ']'" :value="responses[q.k]">
                        </div>
                    </template>
                </div>
            </template>

            <div class="space-y-6 pt-4 border-t border-gray-100">
                <h4 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-l-4 border-indigo-600 pl-3">G. Qualitative Feedback</h4>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-[11px] font-bold text-gray-500 uppercase">What helped you learn the most?</label>
                        <textarea name="aspects_helped" rows="2" class="w-full mt-1 p-3 rounded-xl border-gray-100 bg-gray-50 text-sm focus:ring-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-gray-500 uppercase">What needs improvement?</label>
                        <textarea name="aspects_improved" rows="2" class="w-full mt-1 p-3 rounded-xl border-gray-100 bg-gray-50 text-sm focus:ring-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-gray-500 uppercase">Additional Comments</label>
                        <textarea name="comments" rows="2" class="w-full mt-1 p-3 rounded-xl border-gray-100 bg-gray-50 text-sm focus:ring-indigo-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 pb-4">
                <button type="submit" 
                    :disabled="Object.keys(responses).length < 17"
                    :class="Object.keys(responses).length < 17 
                            ? 'bg-gray-300 cursor-not-allowed opacity-70' 
                            : 'bg-indigo-600 hover:bg-indigo-700 shadow-lg active:scale-95'"
                    class="w-full py-4 text-white font-black rounded-2xl text-xs uppercase tracking-widest transition-all duration-300">
                    
                    <span x-show="Object.keys(responses).length < 17">
                        Answer <span x-text="17 - Object.keys(responses).length"></span> more to submit
                    </span>
                    <span x-show="Object.keys(responses).length === 17">
                        Submit Full Evaluation
                    </span>
                </button>

                <button type="button" @click="showEvalModal = false" class="text-gray-400 text-[10px] font-bold uppercase tracking-widest hover:text-gray-600">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection