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
                    'admin' => 'text-red-500'
                ];
                $roleNames = [
                    'student' => 'Student',
                    'academic_head' => 'Academic Head',
                    'hr' => 'HR Manager',
                    'admin' => 'Administrator'
                ];
            @endphp
            @foreach($roleNames as $role => $name)
                @if(Auth::user()->hasRole($role))
                    <span class="text-sm {{ $roleColors[$role] }}"> ({{ $name }})</span>
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
                                $route = Auth::user()->hasRole('admin') ? 'admin.leave_applications.index' : 
                                        (Auth::user()->hasRole('hr') ? 'hr.leave_applications.index' : 'ah.leave_applications.all');
                            @endphp
                            <a href="{{ route($route) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-bold uppercase tracking-widest rounded-md hover:bg-indigo-700 transition">
                                Review Pending Leaves
                            </a>
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
                                <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 text-gray-400 hover:text-indigo-600 transition">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4 italic">All caught up!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Important Dates Widget (The Calendar Strip) --}}
        
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
                        <div class="flex flex-wrap gap-1">
                            @foreach($date->categories->take(2) as $cat)
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-white border border-gray-200 text-gray-500 font-medium">{{ $cat->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-400 italic">No scheduled events found.</div>
                @endforelse
            </div>
        </div>

        {{-- 3. Role-Specific Main Content --}}
        @if(Auth::user()->hasRole('student'))
            {{-- STUDENT VIEW --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h4 class="text-lg font-bold text-gray-800 mb-4">🗓️ Academic Schedule</h4>
                        <div class="divide-y divide-gray-100">
                            @forelse($upcomingSchedule ?? [] as $item)
                                <div class="py-4 flex justify-between items-center hover:bg-gray-50 transition px-2 rounded-lg">
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $item->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->course_name }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-indigo-600">{{ $item->time_display }}</span>
                                </div>
                            @empty
                                <p class="text-center text-gray-400 py-6 italic text-sm">No classes today. Enjoy your break!</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <div class="bg-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <p class="text-sm font-medium opacity-80 uppercase tracking-widest">Current GPA</p>
                        <h2 class="text-5xl font-black mt-1">{{ number_format($currentGPA ?? 0, 2) }}</h2>
                        <div class="mt-4 pt-4 border-t border-blue-400 flex justify-between text-xs">
                            <span>Enrolled: <strong>{{ $enrolledCourses->count() ?? 0 }} Courses</strong></span>
                            <span>Credits: <strong>{{ $totalCredits ?? 0 }}</strong></span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h4 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-widest">Quick Actions</h4>
                        <div class="grid grid-cols-1 gap-3">
                            <a href="#" class="p-3 rounded-lg bg-gray-50 hover:bg-indigo-50 border border-gray-100 text-sm font-bold text-gray-700 hover:text-indigo-600 transition text-center">My Grades</a>
                            <a href="#" class="p-3 rounded-lg bg-gray-50 hover:bg-indigo-50 border border-gray-100 text-sm font-bold text-gray-700 hover:text-indigo-600 transition text-center">Enrollment Info</a>
                        </div>
                    </div>
                </div>
            </div>

        @else
            {{-- STAFF / ADMIN VIEW --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                @php
                    $stats = [
                        ['label' => 'Students', 'val' => $totalStudents, 'color' => 'bg-blue-500'],
                        ['label' => 'Teachers', 'val' => $totalTeachers, 'color' => 'bg-green-500'],
                        ['label' => 'Courses', 'val' => $totalCourses, 'color' => 'bg-purple-500'],
                        ['label' => 'Programs', 'val' => $totalPrograms, 'color' => 'bg-yellow-500'],
                        ['label' => 'Enrollments', 'val' => $totalEnrollments, 'color' => 'bg-indigo-500'],
                        ['label' => 'Users', 'val' => $totalUsers, 'color' => 'bg-gray-800'],
                    ];
                @endphp
                @foreach($stats as $stat)
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 text-center">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $stat['label'] }}</p>
                        <p class="text-2xl font-black text-gray-900 mt-1">{{ $stat['val'] }}</p>
                        <div class="h-1 w-8 {{ $stat['color'] }} mx-auto mt-2 rounded-full"></div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-lg font-bold text-gray-800 mb-4">Recently Enrolled Students</h4>
                    <div class="space-y-3">
                        @foreach($recentStudents as $student)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs mr-3">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-bold text-gray-700">{{ $student->name }}</span>
                                </div>
                                <span class="text-[10px] text-gray-400">{{ $student->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center flex flex-col justify-center">
                    <h4 class="text-lg font-bold text-gray-800 mb-2">Leave Management</h4>
                    <p class="text-sm text-gray-500 mb-6">Manage staff leave requests and view balances.</p>
                    <a href="{{ route('leaveapplicationstatus') }}" class="w-full py-4 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-700 font-bold hover:bg-indigo-100 transition">
                        Open Leave Portal
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection