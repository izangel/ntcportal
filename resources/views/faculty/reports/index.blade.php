@extends('admin.admin')

@section('header')
    <div>
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li><span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Reports</span></li>
                <li><svg class="h-3 w-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg></li>
                <li><span class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Report Selection</span></li>
            </ol>
        </nav>
        <h2 class="font-black text-3xl text-gray-900 tracking-tight">Performance Analytics</h2>
        <p class="text-sm text-gray-500 font-medium">Select a period to view your consolidated 360° feedback.</p>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4">
        
        @if(session('error'))
            <div class="mb-8 flex items-center p-5 bg-rose-50 border border-rose-100 rounded-[2rem] shadow-sm animate-pulse">
                <div class="flex-shrink-0 w-10 h-10 bg-rose-500 text-white rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-black text-rose-900 uppercase tracking-widest mb-1">Data Not Found</p>
                    <p class="text-sm font-medium text-rose-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-8 p-6 bg-amber-50 border border-amber-100 rounded-[2rem] shadow-sm">
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-3">Please check your selection:</p>
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm text-amber-800 font-bold flex items-center gap-2">
                            <span class="w-1 h-1 bg-amber-400 rounded-full"></span>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-[3rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <div class="p-10 md:p-14">
                <form action="{{ route('faculty.reports.view') }}" method="GET" class="space-y-8">
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] ml-2">Academic Year</label>
                        <div class="relative">
                            <select name="academic_year_id" required 
                                class="w-full appearance-none bg-gray-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl py-4 px-6 font-bold text-gray-700 transition-all outline-none">
                                <option value="" disabled selected>Choose Academic Year</option>
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-6 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] ml-2">Semester Term</label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach(['1st', '2nd', 'Summer'] as $term)
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="semester" value="{{ $term }}" class="peer sr-only" required>
                                    <div class="w-full py-4 text-center rounded-2xl border-2 border-gray-50 bg-gray-50 text-xs font-black uppercase tracking-widest text-gray-400 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 group-hover:bg-gray-100 transition-all">
                                        {{ $term }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <hr class="border-gray-50 my-4">

                    <button type="submit" class="w-full group bg-gray-900 hover:bg-indigo-600 text-white font-black py-5 rounded-2xl uppercase text-[11px] tracking-[0.25em] transition-all flex items-center justify-center gap-3 shadow-lg shadow-gray-200 hover:shadow-indigo-200">
                        Generate Report
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </form>
            </div>

            <div class="px-10 py-6 bg-gray-50/50 border-t border-gray-50">
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest text-center leading-relaxed">
                    Data is calculated based on Peer, Student, Self, and Supervisor inputs. <br> 
                    Final scores are weighted at 25% per category.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection