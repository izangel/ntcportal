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
                    'teacher' => 'text-indigo-500',
                ];
                $roleNames = [
                    'student' => 'Student',
                    'academic_head' => 'Academic Head',
                    'hr' => 'HR Manager',
                    'admin' => 'Administrator',
                    'teacher' => 'Teacher',
                ];
            @endphp
            @foreach ($roleNames as $role => $name)
                @if (Auth::user()->hasRole($role) || (Auth::user()->employee && Auth::user()->employee->role === $role))
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
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Welcome back, {{ Auth::user()->name }}!</h1>

            <div class="flex flex-wrap items-center gap-4 mt-2">
                <div class="flex items-center gap-2 bg-cyan-50 px-3 py-1 rounded-full border border-cyan-100">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-cyan-500 text-[10px] font-bold text-white">
                        {{ $activeAYCount ?? 0 }}
                    </span>
                    <span class="text-sm font-semibold text-cyan-700">Active A.Y.: {{ $currentAYName ?? 'N/A' }}</span>
                </div>

                <div class="flex items-center gap-2 bg-rose-50 px-3 py-1 rounded-full border border-rose-100">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white">
                        {{ $activeSemesterCount ?? 0 }}
                    </span>
                    <span class="text-sm font-semibold text-rose-700">Active Sem: {{ $currentSemName ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

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

                            @if (Auth::user()->employee && Auth::user()->employee->role === 'teacher')
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
                                <p class="text-xs font-bold text-gray-900 truncate pr-4">
                                    {{ $notification->data['title'] ?? 'Update' }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ $notification->data['message'] ?? '' }}
                                </p>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4 italic">All caught up!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Important Dates Widget --}}
        @php
            $selectedMonthStr = request('calendar_month', now()->format('Y-m'));
            try {
                $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonthStr)->startOfMonth();
            } catch (\Exception $e) {
                $selectedDate = now()->startOfMonth();
            }

            $firstDay = $selectedDate->copy()->firstOfMonth();
            $startOfWeek = $firstDay->dayOfWeek;
            $daysInMonth = $selectedDate->daysInMonth;
        @endphp

        <div class="mb-8 mt-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-4 px-1">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 tracking-tight">{{ $selectedDate->format('F Y') }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-sm text-gray-500">School Calendar & Events</p>
                        <span class="text-gray-300">|</span>
                        <a href="{{ route('important_dates.index') }}" class="text-[11px] font-bold text-indigo-600 hover:underline uppercase tracking-wider">
                            View Full Schedule
                        </a>
                    </div>
                </div>

                <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
                    <label for="calendar_month" class="text-sm font-medium text-gray-700">Select Month:</label>
                    <input type="month" name="calendar_month" id="calendar_month"
                           value="{{ $selectedDate->format('Y-m') }}"
                           onchange="this.form.submit()"
                           class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                        <div class="py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ $day }}
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7">
                    @for($i = 0; $i < $startOfWeek; $i++)
                        <div class="min-h-[120px] border-b border-r border-gray-100 bg-gray-50/50"></div>
                    @endfor

                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $currentDate = $selectedDate->copy()->day($day)->startOfDay();
                            $isToday = $currentDate->isToday();
                            $dayEvents = isset($recentDates) ? $recentDates->filter(function($event) use ($currentDate) {
                                $start = $event->start_date->startOfDay();
                                $end = ($event->end_date ?? $event->start_date)->endOfDay();
                                return $currentDate->between($start, $end);
                            }) : collect();
                        @endphp

                        <div class="min-h-[120px] border-b border-r border-gray-100 p-2 transition-colors hover:bg-gray-50 relative {{ $isToday ? 'bg-indigo-50/30' : '' }}">
                            <span class="text-sm font-semibold {{ $isToday ? 'text-indigo-600 bg-indigo-100 rounded-full w-6 h-6 flex items-center justify-center' : 'text-gray-400' }}">
                                {{ $day }}
                            </span>

                            <div class="mt-2 space-y-2">
                                @foreach($dayEvents as $event)
                                    <div class="px-2 py-1.5 text-[10px] leading-tight rounded-md border bg-indigo-50 border-indigo-200 text-indigo-700 text-center flex items-center justify-center w-full" style="word-break: break-word; white-space: normal;" title="{{ $event->title }}">
                                        <span class="font-bold">{{ $event->title }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endfor

                    @php
                        $totalCells = $startOfWeek + $daysInMonth;
                        $remainingCells = ceil($totalCells / 7) * 7 - $totalCells;
                    @endphp
                    @for($i = 0; $i < $remainingCells; $i++)
                         <div class="min-h-[120px] border-b border-r border-gray-100 bg-gray-50/50"></div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- 3. Role-Specific Main Content --}}
        @if (Auth::user()->hasRole('student'))
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-bold text-gray-800">🗓️ Academic Schedule</h4>
                            <span class="text-[10px] font-black px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full uppercase tracking-widest">
                                {{ $semesterName ?? 'N/A' }} Semester
                            </span>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse($upcomingSchedule ?? [] as $block)
                                <div class="py-4 flex justify-between items-center hover:bg-gray-50 transition px-2 rounded-lg">
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $block->course->name ?? 'N/A' }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">
                                            {{ $block->course->code ?? 'N/A' }} • {{ $block->faculty->first_name ?? '' }} {{ $block->faculty->last_name ?? '' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-indigo-600 block">{{ $block->schedule_string ?? '' }}</span>
                                        <span class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">{{ $block->room_name ?? '' }}</span>
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
                            <span>Active: <strong>{{ collect($upcomingSchedule ?? [])->count() }} Classes</strong></span>
                            <span>Total Credits: <strong>{{ $totalCredits ?? 0 }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- STAFF / ADMIN / TEACHER VIEW --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                {{-- 🔑 My Course Load Table --}}
                @if (isset($myCourses) && count($myCourses) > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                            <h4 class="text-sm font-black text-gray-700 uppercase tracking-widest flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
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
                                    @foreach ($myCourses as $course)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3">
                                                <div class="text-xs font-bold text-gray-900">{{ $course['code'] }}</div>
                                                <div class="text-[10px] text-gray-500 truncate w-32">{{ $course['name'] }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-[10px] text-gray-600 italic">
                                                {{ $course['schedule'] }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($course['finalized'])
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

                {{-- Leave Management / Quick Actions --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-center text-center">
                    <h4 class="text-lg font-bold text-gray-800 mb-2">Leave Management</h4>
                    <p class="text-sm text-gray-500 mb-6">Review balances and submit applications.</p>
                    <a href="{{ route('leaveapplicationstatus') }}" class="w-full py-3 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-700 text-sm font-bold hover:bg-indigo-100 transition">
                        Open Leave Portal
                    </a>
                </div>
            </div>

            {{-- Full-Width Work Week Leave Summary (Mon-Fri) --}}
            @if (!Auth::user()->hasRole('student'))
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
                        @foreach ($daysOfWeek ?? [] as $day)
                            @php
                                $dateStr = $day->toDateString();
                                $isToday = $day->isToday();
                                $dailyLeaves = $leavesByDay[$dateStr] ?? collect();
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
                                                {{ $leave->employee->last_name ?? '' }}, {{ $leave->employee->first_name ?? '' }}
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

            {{-- General Statistics --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                @php
                    $stats = [
                        ['label' => 'Students', 'val' => $totalStudents ?? 0, 'color' => 'bg-blue-500'],
                        ['label' => 'Teachers', 'val' => $totalTeachers ?? 0, 'color' => 'bg-green-500'],
                        ['label' => 'Courses', 'val' => $totalCourses ?? 0, 'color' => 'bg-purple-500'],
                        ['label' => 'Programs', 'val' => $totalPrograms ?? 0, 'color' => 'bg-yellow-500'],
                        ['label' => 'Enrollments', 'val' => $totalEnrollments ?? 0, 'color' => 'bg-indigo-500'],
                        ['label' => 'Users', 'val' => $totalUsers ?? 0, 'color' => 'bg-gray-800'],
                    ];
                @endphp
                @foreach ($stats as $stat)
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 text-center">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $stat['label'] }}</p>
                        <p class="text-2xl font-black text-gray-900 mt-1">{{ $stat['val'] }}</p>
                        <div class="h-1 w-8 {{ $stat['color'] }} mx-auto mt-2 rounded-full"></div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-lg font-bold text-gray-800 mb-4">Recent System Updates</h4>
                    <div class="space-y-3">
                        @forelse($recentUpdates ?? [] as $update)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg gap-3">
                                <div class="flex items-center flex-1 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs mr-3 flex-shrink-0">
                                        <i class="fas fa-wrench"></i>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 break-words line-clamp-2">
                                        {{ $update->title }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-gray-400 text-right flex-shrink-0">
                                    {{ $update->created_at->format('M d, Y') }} <br>
                                    {{ $update->created_at->diffForHumans() }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-4 bg-gray-50 rounded-lg">
                                <p class="text-[10px] text-gray-400 font-medium">No recent updates.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-lg font-bold text-gray-800 mb-4">Latest Courses</h4>
                    <div class="space-y-3">
                        @forelse ($recentCourses ?? [] as $course)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-bold text-gray-700">{{ $course->code }}</span>
                                <span class="text-[10px] text-gray-400">{{ $course->credits }} Credits</span>
                            </div>
                        @empty
                            <div class="text-center py-4 bg-gray-50 rounded-lg">
                                <p class="text-[10px] text-gray-400 font-medium">No recent courses.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
