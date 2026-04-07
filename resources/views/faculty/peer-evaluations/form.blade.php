@extends('layouts.admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Peer Evaluation</h1>
                <p class="text-sm text-gray-500">Evaluating: <span class="font-bold text-indigo-600">{{ $assignment->teacher->last_name }}, {{ $assignment->teacher->first_name }}</span></p>
            </div>
            <div class="flex gap-2">
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-[10px] font-bold uppercase tracking-wider border border-indigo-100">
                    {{ $assignment->semester }}
                </span>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-[10px] font-bold uppercase tracking-wider border border-gray-200">
                    {{ $assignment->academicYear->name }}
                </span>
            </div>
        </div>

        <form action="{{ route('faculty.peer-evaluations.store', $assignment) }}" method="POST">
            @csrf
            
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest w-1/2">Performance Criteria</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-center">
                                    <div class="flex justify-between max-w-[240px] mx-auto">
                                        <span>Poor</span>
                                        <span>Excellent</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach(config('evaluation_questions.peer') as $key => $question)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-6">
                                    <div class="flex gap-3">
                                        <span class="text-gray-300 font-bold text-sm">{{ $loop->iteration }}.</span>
                                        <p class="text-sm font-semibold text-gray-700 leading-snug">{{ $question }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center justify-center gap-2 max-w-[240px] mx-auto">
                                        @for($i = 1; $i <= 5; $i++)
                                        <label class="relative flex-1 cursor-pointer">
                                            <input type="radio" name="ratings[{{ $key }}]" value="{{ $i }}" required class="peer sr-only">
                                            
                                            <div class="h-10 w-full flex items-center justify-center rounded-lg border-2 border-gray-100 bg-white text-gray-400 font-black text-sm transition-all
                                                peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:text-white peer-checked:shadow-md
                                                peer-hover:border-indigo-300">
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

            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6 shadow-sm">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Additional Comments</label>
                <textarea name="comments" rows="3" 
                    placeholder="Provide context for your ratings (optional)..."
                    class="w-full border-gray-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all"></textarea>
            </div>

            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('faculty.peer-evaluations.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors">
                    Cancel and Return
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-10 rounded-xl text-xs uppercase tracking-[0.2em] transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                    Submit Evaluation
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection