
@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Leave Summary') }}
    </h2>
@endsection

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">{{ $monthName }}</h1>
                <p class="text-sm text-gray-500">Approved employee leaves summary</p>
            </div>
            
            <form action="{{ route('admin.leave.summary') }}" method="GET" class="flex items-center gap-2">
                <label for="month" class="text-sm font-medium text-gray-700">Select Month:</label>
                <input type="month" name="month" id="month" 
                       value="{{ $currentMonth }}"
                       onchange="this.form.submit()"
                       class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </form>
        </div>

        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
            <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {{ $day }}
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-7">
                @for($i = 0; $i < $startOfWeek; $i++)
                    <div class="h-32 border-b border-r border-gray-100 bg-gray-50/50"></div>
                @endfor

                @foreach($calendar as $date => $leaves)
                    @php 
                        $isToday = $date == date('Y-m-d');
                    @endphp
                    <div class="h-32 border-b border-r border-gray-100 p-2 transition-colors hover:bg-gray-50 relative {{ $isToday ? 'bg-indigo-50/30' : '' }}">
                        <span class="text-sm font-semibold {{ $isToday ? 'text-indigo-600 bg-indigo-100 rounded-full w-6 h-6 flex items-center justify-center' : 'text-gray-400' }}">
                            {{ \Carbon\Carbon::parse($date)->day }}
                        </span>

                        <div class="mt-2 space-y-1 overflow-y-auto max-h-20 custom-scrollbar">
                            @foreach($leaves as $leave)
                                <div class="px-2 py-1 text-[10px] leading-tight rounded border truncate
                                    @if($leave->leave_type_id == '1') bg-red-50 border-red-200 text-red-700
                                    @elseif($leave->leave_type_id == '3') bg-green-50 border-green-200 text-green-700
                                    @else bg-blue-50 border-blue-200 text-blue-700 @endif"
                                    title="{{ $leave->employee->name }}: {{ $leave->reason }}">
                                    <span class="font-bold">{{ $leave->employee->last_name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex items-center gap-4 text-xs font-medium text-gray-600 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-400 rounded-full"></span> Sick Leave</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-400 rounded-full"></span> Vacation</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-blue-400 rounded-full"></span> SIL</span>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
</style>
@endsection