@extends('admin.admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <form action="{{ route('supervisor.evaluations.store', $assignment) }}" method="POST">
            @csrf
            
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6 shadow-sm flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">Supervisor Rating Form</p>
                    <h1 class="text-xl font-bold text-gray-900">{{ $assignment->teacher->last_name }}, {{ $assignment->teacher->first_name }}</h1>
                </div>
                <button type="submit" class="bg-indigo-600 text-white font-black py-3 px-8 rounded-xl text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-100">
                    Submit Review
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest w-1/2">Leadership & Core Competencies</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-center">
                                <div class="flex justify-between max-w-[240px] mx-auto text-indigo-400">
                                    <span>Below</span>
                                    <span>Exceeds</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach(config('evaluation_questions.supervisor') as $key => $question)
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-6 text-sm font-bold text-gray-700">
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

            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Administrative Comments / Recommendations</label>
                <textarea name="comments" rows="4" placeholder="Enter formal feedback..." class="w-full border-gray-200 rounded-xl text-sm focus:ring-indigo-500"></textarea>
            </div>
        </form>
    </div>
</div>
@endsection