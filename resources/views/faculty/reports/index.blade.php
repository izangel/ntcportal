@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100 py-12 px-4">
    <div class="max-w-xl mx-auto">
        <div class="bg-white shadow-md border-t-4 border-indigo-600 overflow-hidden">
            <div class="p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 uppercase tracking-tight">Performance Analytics</h2>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Report Generation Portal</p>
                </div>

                <form action="{{ route('faculty.reports.view') }}" method="GET" class="space-y-5">
                    
                    @php
                        $userEmp = auth()->user()->employee;
                        $isPrivileged = $userEmp && in_array($userEmp->role, ['admin', 'hr', 'academic_head']);
                    @endphp

                    @if($isPrivileged)
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-gray-500 tracking-widest">Faculty Member</label>
                        <select name="faculty_id" required class="w-full bg-white border border-gray-300 rounded-none py-2 px-3 text-sm font-semibold text-gray-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="" disabled selected>Select Faculty...</option>
                            @foreach(\App\Models\Employee::orderBy('last_name')->get() as $faculty)
                                <option value="{{ $faculty->id }}">{{ $faculty->last_name }}, {{ $faculty->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase text-gray-500 tracking-widest">Academic Year</label>
                            <select name="academic_year_id" required class="w-full bg-white border border-gray-300 rounded-none py-2 px-3 text-sm font-semibold text-gray-700 outline-none">
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase text-gray-500 tracking-widest">Semester</label>
                            <select name="semester" required class="w-full bg-white border border-gray-300 rounded-none py-2 px-3 text-sm font-semibold text-gray-700 outline-none">
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="Summer">Summer Term</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-bold py-3 rounded-none uppercase text-xs tracking-[0.2em] transition-all">
                        Generate Report
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection