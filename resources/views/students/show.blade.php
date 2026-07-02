@extends('layouts.plain')

@section('content')
<div class="p-4 bg-gray-100 min-h-screen font-sans">
    <div class="mx-auto max-w-5xl">
        
        {{-- HEADER / NAVIGATION --}}
        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('students.index') }}" class="text-[10px] font-black uppercase text-gray-500 hover:text-blue-900 transition flex items-center gap-1">
                ← Back to Registry
            </a>
            <div class="flex gap-2">
                <a href="{{ route('students.edit', $student) }}" class="bg-blue-600 text-white px-4 py-1.5 rounded text-[10px] font-black uppercase shadow-sm">Edit Profile</a>
                <a href="{{ route('students.cor', $student) }}" target="_blank" class="bg-amber-500 text-white px-4 py-1.5 rounded text-[10px] font-black uppercase shadow-sm">Print COR</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            {{-- LEFT COL: STUDENT INFO --}}
            <div class="md:col-span-1 space-y-4">
                <div class="bg-white p-6 rounded shadow-sm border-t-4 border-blue-900">
                    <div class="text-center mb-4">
                        <div class="w-24 h-24 bg-gray-200 rounded-full mx-auto mb-2 flex items-center justify-center text-gray-400">
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                        </div>
                        <h2 class="text-xl font-black text-gray-900 uppercase tracking-tighter">{{ $student->last_name }}, {{ $student->first_name }}</h2>
                        <p class="text-xs font-bold text-blue-600 uppercase">{{ $student->student_id }}</p>
                    </div>

                    <div class="space-y-3 border-t pt-4">
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Section</p>
                            <p class="text-xs font-bold uppercase text-gray-800">
                                {{ $student->sections->first()->program->name ?? 'N/A' }} - {{ $student->sections->first()->name ?? 'Unassigned' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Gender / Birthday</p>
                            <p class="text-xs font-bold uppercase text-gray-800">{{ $student->gender }} | {{ \Carbon\Carbon::parse($student->birthday)->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- REQUIREMENTS CHECKLIST --}}
                <div class="bg-white p-4 rounded shadow-sm border border-gray-200">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Submitted Requirements</h3>
                    <div class="space-y-2">
                        @php $reqs = $student->requirements_submitted ?? []; @endphp
                        @foreach(['Form 138', 'PSA Birth Certificate', 'Good Moral', 'Medical Certificate'] as $doc)
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-gray-600 uppercase">{{ $doc }}</span>
                                @if(isset($reqs[$doc]) && $reqs[$doc])
                                    <span class="text-green-600 font-black text-[10px]">✔</span>
                                @else
                                    <span class="text-gray-300 font-black text-[10px]">✘</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- RIGHT COL: CLASS SCHEDULE --}}
            <div class="md:col-span-2">
                <div class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                        <h3 class="text-[10px] font-black text-blue-900 uppercase tracking-widest">Enrolled Subjects</h3>
                        <span class="text-[9px] font-bold text-gray-400 uppercase">{{ $context['semester'] }} {{ $context['ay']->start_year }}-{{ $context['ay']->end_year }}</span>
                    </div>
                    {{-- Add this above the table to verify the filter is correct --}}
                    <div class="mb-2 flex items-center gap-2">
                        <span class="text-[9px] font-black bg-blue-100 text-blue-700 px-2 py-0.5 rounded uppercase">
                            Filtering for: {{ $sem }} | AY {{ $context['ay']->start_year }}-{{ $context['ay']->end_year }}
                        </span>
                    </div>

                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-[8px] uppercase font-black text-gray-400 tracking-widest">
                            <tr>
                                <th class="px-4 py-2 text-left">Code</th>
                                <th class="px-4 py-2 text-left">Subject Name</th>
                                <th class="px-4 py-2 text-left">Instructor</th>
                                <th class="px-4 py-2 text-left">Schedule & Room</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($subjects as $subject)
                                <tr class="text-[11px] hover:bg-blue-50/30">
                                    <td class="px-4 py-3 font-bold text-blue-900 uppercase tracking-tighter">{{ $subject->code }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-700 uppercase">{{ $subject->name }}</td>
                                    <td class="px-4 py-3 text-gray-500 uppercase">{{ $subject->instructor }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-[10px] text-gray-600 font-mono italic">{{ $subject->schedule_string }}</div>
                                        <div class="text-[9px] font-black text-blue-600 uppercase">{{ $subject->room_name }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center">
                                        <p class="text-gray-400 font-black uppercase tracking-widest text-[10px]">No subjects found for this term.</p>
                                        <p class="text-[8px] text-gray-400 mt-1 uppercase">Check if student is enrolled in {{ $sem }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection