@extends('admin.admin') {{-- Or your main layout file --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Course Evaluation') }}
    </h2>
@endsection

@section('content')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-10">
            <h1 class="text-2xl font-black text-gray-900 mb-2">Course Evaluation Reports</h1>
            <p class="text-sm text-gray-500 mb-8">Select the parameters below to generate your performance report.</p>

            <form action="{{ route('faculty.evaluations.report') }}" method="GET" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Academic Year</label>
                    <select name="academic_year_id" required class="w-full bg-gray-50 border-none rounded-2xl py-4 px-5 text-sm font-semibold focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Year</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Semester</label>
                    <select name="semester" required class="w-full bg-gray-50 border-none rounded-2xl py-4 px-5 text-sm font-semibold focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Semester</option>
                        <option value="1st">First Semester</option>
                        <option value="2nd">Second Semester</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>

        

                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-black rounded-2xl text-xs uppercase tracking-widest shadow-lg hover:bg-indigo-700 transition-all pt-5">
                    Generate Report
                </button>
            </form>
        </div>
    </div>
</div>
@endsection