@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
            @php
                $roleColors = [
                    'student' => 'text-blue-500',
                    'academic_head' => 'text-gray-500',
                    'hr' => 'text-gray-500',
                    'admin' => 'text-red-500',
                    'teacher' => 'text-indigo-500'
                ];
                $roleNames = [
                    'student' => 'Student',
                    'academic_head' => 'Academic Head',
                    'hr' => 'HR Manager',
                    'admin' => 'Administrator',
                    'teacher' => 'Teacher'
                ];
            @endphp
            @foreach($roleNames as $role => $name)
                @if(Auth::user()->hasRole($role) || (Auth::user()->employee && Auth::user()->employee->role === $role))
                    <span class="text-sm {{ $roleColors[$role] ?? 'text-gray-500' }}"> ({{ $name }})</span>
                @endif
            @endforeach
        </h2>
        <span class="text-sm text-gray-500 font-medium">{{ now()->format('l, F j, Y') }}</span>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        {{-- 1. Welcome & Notifications Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 h-full flex flex-col justify-center">
                    <h3 class="text-3xl font-extrabold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h3>
                    <p class="mt-2 text-gray-600">Here is what's happening in the portal today.</p>
                    
                    @if(!Auth::user()->hasRole('student'))
                        <div class="mt-6 flex gap-4">
                            @php
                                if(Auth::user()->hasRole('admin')) $route = 'admin.leave_applications.index';
                                elseif(Auth::user()->hasRole('hr')) $route = 'hr.leave_applications.index';
                                elseif(Auth::user()->hasRole('academic_head')) $route = 'ah.leave_applications.all';
                                else $route = null;
                            @endphp
                            
                            @if($route)
                                <a href="{{ route($route) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-bold uppercase tracking-widest rounded-md hover:bg-indigo-700 transition">
                                    Review Pending Leaves
                                </a>
                            @endif

                          

                            @if(Auth::user()->employee && Auth::user()->employee->role === 'teacher')
                                
                            <a href="{{ route('faculty.course-load') }}" class="inline-flex items-center px-4 py-2 bg-white border border-indigo-600 text-indigo-600 text-xs font-bold uppercase tracking-widest rounded-md hover:bg-indigo-50 transition">
                                    View Detailed Load
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full">
                    <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <h4 class="font-bold text-gray-800 text-sm uppercase tracking-wider">🔔 Notifications</h4>
                        <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                            {{ Auth::user()->unreadNotifications->count() }} New
                        </span>
                    </div>
                    <div class="p-4 max-h-48 overflow-y-auto">
                        @forelse(Auth::user()->unreadNotifications->take(3) as $notification)
                            <div class="mb-3 last:mb-0 p-3 rounded-lg bg-gray-50 border border-gray-100 relative group">
                                <p class="text-xs font-bold text-gray-900 truncate pr-4">{{ $notification->data['title'] ?? 'Update' }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ $notification->data['message'] ?? '' }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4 italic">All caught up!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

          {{-- PES Submission Tracker Notice Block --}}
                @if(Auth::user()->employee && in_array(Auth::user()->employee->role, ['teacher', 'admin', 'hr', 'academic_head']))
                 <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="mb-8">
                        <h4 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                            <span class="inline-block w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                            ⚠️ Important Administrative Clearance Notice
                        </h4>
                        
                        @if(Auth::user()->hasRole('admin'))
                            <!-- If Admin: Embed the control list layout tracking tool directly -->
                            @livewire('admin.pes-tracker')
                        @else
                            <!-- If Faculty/Staff/Head: Embed the read-only peer scoreboard tracking tool -->
                            @livewire('faculty.pes-dashboard')
                        @endif
                    </div>
</div>
                @endif

        {{-- 2. Important Dates Widget --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-bold text-gray-800 flex items-center">
                    <span class="p-2 bg-indigo-100 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </span>
                    School Calendar & Events
                </h4>
                <a href="{{ route('important_dates.index') }}" class="text-xs font-bold text-indigo-600 hover:underline uppercase">View Full Schedule</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @forelse($recentDates as $date)
                    @php
                        $today = now()->startOfDay();
                        $isOngoing = $today->between($date->start_date->startOfDay(), ($date->end_date ?? $date->start_date)->endOfDay());
                    @endphp
                    <div class="relative p-4 rounded-xl border {{ $isOngoing ? 'bg-indigo-50 border-indigo-200 ring-1 ring-indigo-200' : 'bg-white border-gray-100' }} transition-all duration-300 hover:shadow-md">
                        <div class="flex justify-between items-start mb-3">
                            <div class="text-center">
                                <p class="text-[10px] font-bold uppercase {{ $isOngoing ? 'text-indigo-600' : 'text-gray-400' }}">{{ $date->start_date->format('M') }}</p>
                                <p class="text-xl font-black {{ $isOngoing ? 'text-indigo-700' : 'text-gray-800' }}">{{ $date->start_date->format('d') }}</p>
                            </div>
                            @if($isOngoing)
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[9px] font-black rounded-full animate-pulse">ONGOING</span>
                            @endif
                        </div>
                        <h5 class="text-sm font-bold text-gray-900 leading-tight line-clamp-2 mb-2">{{ $date->title }}</h5>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-400 italic">No scheduled events found.</div>
                @endforelse
            </div>
        </div>

        {{-- 3. Role-Specific Main Content --}}
        @if(Auth::user()->hasRole('student') && $user->student)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-bold text-gray-800">🗓️ Academic Schedule</h4>
                            <span class="text-[10px] font-black px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full uppercase tracking-widest">
                                {{ $semesterName }} Semester
                            </span>
                        </div>
                        
                        <div class="divide-y divide-gray-100">
                            @forelse($upcomingSchedule as $block)
                                <div class="py-4 flex justify-between items-center hover:bg-gray-50 transition px-2 rounded-lg">
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $block->course->name }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">
                                            {{ $block->course->code }} • {{ $block->faculty->first_name }} {{ $block->faculty->last_name }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-indigo-600 block">{{ $block->schedule_string }}</span>
                                        <span class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">{{ $block->room_name }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <p class="text-gray-400 italic text-sm">No courses found for the current semester.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                
                {{-- GPA Sidebar --}}
                <div class="space-y-6">
                    <div class="bg-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <p class="text-sm font-medium opacity-80 uppercase tracking-widest">Cumulative GPA</p>
                        <h2 class="text-5xl font-black mt-1">{{ number_format($currentGPA ?? 0, 2) }}</h2>
                        <div class="mt-4 pt-4 border-t border-blue-400 flex justify-between text-xs">
                            <span>Active: <strong>{{ $upcomingSchedule->count() }} Classes</strong></span>
                            <span>Total Credits: <strong>{{ $totalCredits ?? 0 }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>


        @else
            {{-- STAFF / ADMIN / TEACHER VIEW --}}
            
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                {{-- 🔑 My Course Load Table (Half Width) --}}
                @if(isset($myCourses) && count($myCourses) > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                            <h4 class="text-sm font-black text-gray-700 uppercase tracking-widest flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                My Course Load
                            </h4>
                            
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Course</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Schedule</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Grade Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-50">
                                    @foreach($myCourses as $course)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3">
                                                <div class="text-xs font-bold text-gray-900">{{ $course['code'] }}</div>
                                                <div class="text-[10px] text-gray-500 truncate w-32">{{ $course['name'] }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-[10px] text-gray-600 italic">
                                                {{ $course['schedule'] }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if($course['finalized'])
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-green-100 text-green-700">Submitted</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-700">In Progress</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Leave Management / Quick Actions (The other Half) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-center text-center">
                    <h4 class="text-lg font-bold text-gray-800 mb-2">Leave Management</h4>
                    <p class="text-sm text-gray-500 mb-6">Review balances and submit applications.</p>
                    <a href="{{ route('leaveapplicationstatus') }}" class="w-full py-3 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-700 text-sm font-bold hover:bg-indigo-100 transition">
                        Open Leave Portal
                    </a>
                </div>

                
            </div>

            {{-- Full-Width Work Week Leave Summary (Mon-Fri) --}}
            @if(!Auth::user()->hasRole('student'))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-bold text-gray-800 flex items-center">
                        <span class="p-2 bg-indigo-100 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </span>
                        Leave Summary This Week
                    </h4>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                        {{ now()->startOfWeek()->format('M d') }} — {{ now()->startOfWeek()->addDays(4)->format('M d') }}
                    </span>
                </div>

                {{-- 5-Column Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach($daysOfWeek as $day)
                        @php 
                            $dateStr = $day->toDateString();
                            $isToday = $day->isToday();
                            $dailyLeaves = $leavesByDay[$dateStr];
                        @endphp
                        <div class="flex flex-col min-h-[180px] rounded-xl border {{ $isToday ? 'bg-indigo-50/50 border-indigo-200 ring-2 ring-indigo-50' : 'bg-gray-50/30 border-gray-100' }}">
                            
                            <div class="p-3 text-center border-b {{ $isToday ? 'border-indigo-100 bg-indigo-100/30' : 'border-gray-100 bg-gray-50/50' }} rounded-t-xl">
                                <p class="text-[10px] font-black uppercase tracking-tighter {{ $isToday ? 'text-indigo-600' : 'text-gray-400' }}">
                                    {{ $day->format('l') }}
                                </p>
                                <p class="text-xl font-black {{ $isToday ? 'text-indigo-700' : 'text-gray-800' }}">
                                    {{ $day->format('d') }}
                                </p>
                            </div>

                            <div class="p-3 space-y-2 flex-grow">
                                @forelse($dailyLeaves as $leave)
                                    <div class="bg-white p-2 rounded-lg border {{ $leave->approval_status === 'pending' ? 'border-amber-200' : 'border-gray-100' }} shadow-sm">
                                        <p class="text-[11px] font-bold text-gray-900 leading-tight">
                                            {{ $leave->employee->last_name }},  {{ $leave->employee->first_name }}
                                        </p>
                                        <div class="flex items-center mt-1">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $leave->approval_status === 'pending' ? 'bg-amber-400' : 'bg-green-500' }} mr-1.5"></span>
                                            <span class="text-[8px] font-black uppercase {{ $leave->approval_status === 'pending' ? 'text-amber-600' : 'text-green-600' }}">
                                                {{ $leave->approval_status === 'pending' ? 'Pending' : 'Approved' }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="h-full flex items-center justify-center py-8 opacity-20">
                                        <p class="text-[10px] italic text-gray-400 font-bold uppercase tracking-widest">No Leave</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Enrollment Analytics --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-bold text-gray-800 flex items-center">
                        <span class="p-2 bg-indigo-100 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </span>
                        Enrollment Analytics
                    </h4>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    @php
                        $enrollTiles = [
                            ['label' => 'Total Enrollments', 'val' => number_format($enrollmentTotals['enrollments']), 'color' => 'bg-indigo-500'],
                            ['label' => 'Enrolled Students', 'val' => number_format($enrollmentTotals['students']), 'color' => 'bg-blue-500'],
                            ['label' => 'Classes', 'val' => number_format($enrollmentTotals['classes']), 'color' => 'bg-purple-500'],
                            ['label' => 'Programs', 'val' => number_format($enrollmentTotals['programs']), 'color' => 'bg-green-500'],
                        ];
                    @endphp
                    @foreach($enrollTiles as $tile)
                        <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 text-center">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $tile['label'] }}</p>
                            <p class="text-2xl font-black text-gray-900 mt-1">{{ $tile['val'] }}</p>
                            <div class="h-1 w-8 {{ $tile['color'] }} mx-auto mt-2 rounded-full"></div>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-5">
                        <h5 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-6">Enrollments per School Year</h5>
                        <div class="flex items-end justify-around gap-4 h-44">
                            @php $semColors = ['1st' => 'bg-indigo-500', '2nd' => 'bg-blue-500', 'Summer' => 'bg-green-500']; @endphp
                            @forelse($enrollmentsByAY as $ay)
                                <div class="flex flex-col items-center justify-end h-full flex-1">
                                    <span class="text-[10px] font-black text-gray-500 mb-1">{{ number_format($ay['total']) }}</span>
                                    <div class="w-full max-w-[52px] flex flex-col justify-end rounded-t-lg overflow-hidden" style="height: {{ round($ay['total'] / $enrollmentMaxAY * 100) }}%">
                                        @foreach($ay['semesters'] as $sem)
                                            <div class="w-full {{ $semColors[$sem['semester']] ?? 'bg-indigo-500' }}" style="height: {{ round($sem['total'] / $ay['total'] * 100) }}%"></div>
                                        @endforeach
                                    </div>
                                    <span class="mt-2 text-[10px] font-bold text-gray-600">{{ $ay['label'] }}</span>
                                    <span class="text-center text-[9px] text-gray-400 leading-tight">
                                        @foreach($ay['semesters'] as $sem)
                                            {{ $sem['semester'] }}: {{ number_format($sem['total']) }}{{ $loop->last ? '' : ' · ' }}
                                        @endforeach
                                    </span>
                                </div>
                            @empty
                                <p class="text-gray-400 italic text-sm">No enrollment data yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-5">
                        <h5 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-6">Enrollments per Program</h5>
                        <div class="space-y-3 max-h-56 overflow-y-auto pr-1">
                            @forelse($enrollmentsByProgram as $prog)
                                <div>
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="font-bold text-gray-700 truncate pr-2">{{ $prog['name'] }}</span>
                                        <span class="font-black text-indigo-600">{{ number_format($prog['enrollments']) }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ round($prog['enrollments'] / $enrollmentMaxProgram * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-400 italic text-sm">No program data yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                    <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-5">
                        <h5 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-6">Students per Program</h5>
                        <div class="space-y-3 max-h-56 overflow-y-auto pr-1">
                            @forelse($enrollmentsByProgram as $prog)
                                <div>
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="font-bold text-gray-700 truncate pr-2">{{ $prog['name'] }}</span>
                                        <span class="font-black text-blue-600">{{ number_format($prog['students']) }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ round($prog['students'] / $enrollmentMaxStudents * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-400 italic text-sm">No student data yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-5">
                        <h5 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-6">Faculty Teaching Load</h5>
                        <div class="space-y-3 max-h-56 overflow-y-auto pr-1">
                            @forelse($facultyLoad as $fac)
                                <div>
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="font-bold text-gray-700 truncate pr-2">{{ $fac['name'] }}</span>
                                        <span class="font-black text-purple-600">{{ $fac['classes'] }} classes · {{ $fac['students'] }} students</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                                        <div class="h-full bg-purple-500 rounded-full" style="width: {{ round($fac['classes'] / $facultyMaxClasses * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-400 italic text-sm">No faculty data yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-8 bg-gray-50/50 border border-gray-100 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="text-sm font-black text-gray-700 uppercase tracking-widest">Grade Submission Status</h5>
                        @php
                            $gsTotal = $gradeSubmission['finalized'] + $gradeSubmission['inProgress'];
                            $gsPct = $gsTotal > 0 ? round($gradeSubmission['finalized'] / $gsTotal * 100) : 0;
                        @endphp
                        <span class="text-[10px] font-bold text-gray-500">{{ $gradeSubmission['finalized'] }} of {{ $gsTotal }} classes finalized</span>
                    </div>
                    <div class="h-3 rounded-full bg-gray-200 overflow-hidden mb-3">
                        <div class="h-full bg-green-500 rounded-full transition-all" style="width: {{ $gsPct }}%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] font-bold">
                        <span class="text-green-600">{{ $gsPct }}% Submitted</span>
                        <span class="text-amber-600">{{ $gradeSubmission['inProgress'] }} In Progress</span>
                    </div>
                </div>

                <div class="mt-8">
                    <h5 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-4">Program Breakdown</h5>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Program</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Enrollments</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Students</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Courses</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Sections</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Share</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                @foreach($enrollmentsByProgram as $prog)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 text-xs font-bold text-gray-900">{{ $prog['name'] }}</td>
                                        <td class="px-4 py-3 text-center text-xs font-bold text-gray-700">{{ number_format($prog['enrollments']) }}</td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ number_format($prog['students']) }}</td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ number_format($prog['courses']) }}</td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ number_format($prog['sections']) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <div class="w-24 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                                    <div class="h-full bg-green-500 rounded-full" style="width: {{ $prog['share'] }}%"></div>
                                                </div>
                                                <span class="text-[10px] font-bold text-gray-500">{{ $prog['share'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- 7. Recent System Updates --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <h4 class="text-lg font-bold text-gray-800 flex items-center">
                    <span class="p-2 bg-sky-100 rounded-lg mr-3">
                        <i class="fas fa-circle-info text-sky-600"></i>
                    </span>
                    Recent System Updates
                </h4>
                <a href="{{ route('system-updates.index') }}" class="inline-flex items-center text-xs font-bold text-sky-600 hover:text-sky-700 transition">
                    View All Updates <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentUpdates as $update)
                    <a href="{{ route('system-updates.index') }}" class="block rounded-lg border border-gray-100 bg-gray-50/50 p-4 hover:border-sky-200 hover:bg-sky-50/50 transition">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $update->category === 'Bug Fix' ? 'bg-rose-50 text-rose-700' : ($update->category === 'Improvement' ? 'bg-sky-50 text-sky-700' : 'bg-emerald-50 text-emerald-700') }}">
                                <i class="fas {{ $update->category === 'Bug Fix' ? 'fa-bug' : ($update->category === 'Improvement' ? 'fa-arrows-rotate' : 'fa-star') }}"></i>
                                {{ $update->category }}
                            </span>
                            <span class="text-[10px] font-medium text-gray-400">{{ $update->release_date?->format('M d, Y') }}</span>
                        </div>
                        <p class="text-sm font-bold text-gray-900">{{ $update->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $update->description }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 italic py-4 text-center">No system updates yet.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection