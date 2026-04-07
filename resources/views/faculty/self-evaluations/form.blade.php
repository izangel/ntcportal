@extends('layouts.admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6 shadow-sm">
            <h1 class="text-xl font-bold text-gray-900">Self-Evaluation Form</h1>
            <p class="text-sm text-gray-500">Reflect on your performance for <span class="font-bold text-indigo-600">{{ $currentAY->name }} - {{ $currentSemester }} Semester</span></p>
        </div>

        <form action="{{ route('faculty.self-evaluations.store') }}" method="POST">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $currentAY->id }}">
            <input type="hidden" name="semester" value="{{ $currentSemester }}">
            
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest w-1/2">Self-Assessment Criteria</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-center">
                                <div class="flex justify-between max-w-[240px] mx-auto text-indigo-600">
                                    <span>Rarely</span>
                                    <span>Always</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach(config('evaluation_questions.self') as $key => $question)
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-6 text-sm font-semibold text-gray-700">
                                {{ $loop->iteration }}. {{ $question }}
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center justify-center gap-2 max-w-[240px] mx-auto">
                                    @for($i = 1; $i <= 5; $i++)
                                    <label class="relative flex-1 cursor-pointer">
                                        <input type="radio" name="ratings[{{ $key }}]" value="{{ $i }}" required class="peer sr-only">
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

            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Self-Reflection / Comments</label>
                <textarea name="comments" rows="3" class="w-full border-gray-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl text-xs uppercase tracking-[0.2em] shadow-lg shadow-indigo-100">
                Submit My Self-Evaluation
            </button>
        </form>
    </div>
</div>
@endsection