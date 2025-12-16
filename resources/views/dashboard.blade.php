@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard') }}
        
        {{-- Displaying role in header based on custom hasRole method --}}
        @if(Auth::user()->hasRole('student'))
            <span class="text-sm text-blue-500"> (Student)</span>
        @elseif(Auth::user()->hasRole('academic_head'))
            <span class="text-sm text-gray-500"> (Academic Head)</span>
        @elseif(Auth::user()->hasRole('hr'))
            <span class="text-sm text-gray-500"> (HR Manager)</span>
        @elseif(Auth::user()->hasRole('admin'))
            <span class="text-sm text-red-500"> (Administrator)</span>
        @endif
    </h2>
@endsection

@php use Illuminate\Support\Facades\URL; @endphp


@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Welcome, {{ Auth::user()->name }}!</h3>

                {{-- --- NOTIFICATIONS SECTION (Visible to ALL roles) --- --}}
                @if(Auth::check() && Auth::user()->unreadNotifications->isNotEmpty())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="font-semibold text-xl text-gray-800">🔔 {{ __('New Notifications') }}</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                @foreach(Auth::user()->unreadNotifications as $notification)
                                    @php
                                        $data = $notification->data;
                                        $notificationId = $notification->id;
                                    @endphp
                                    <li class="relative p-4 rounded-lg shadow-sm
                                        @if(($data['type'] ?? '') === 'substitute_assignment') bg-blue-50 border border-blue-200
                                        @elseif(in_array(($data['type'] ?? ''), ['ah_leave_review', 'hr_leave_review', 'admin_leave_review'])) bg-yellow-50 border border-yellow-200
                                        @elseif(($data['type'] ?? '') === 'leave_decision' && ($data['decision'] ?? '') === 'approved') bg-green-50 border border-green-200
                                        @elseif(($data['type'] ?? '') === 'leave_decision' && ($data['decision'] ?? '') === 'rejected') bg-red-50 border border-red-200
                                        @elseif(($data['type'] ?? '') === 'grade_posted') bg-purple-50 border border-purple-200
                                        @else bg-gray-50 border border-gray-200 @endif
                                        ">

                                        <form method="POST" action="{{ route('notifications.markAsRead', $notificationId) }}" class="absolute top-2 right-2 z-20">
                                            @csrf
                                            <button type="submit" class="text-gray-400 hover:text-gray-600 transition duration-150 ease-in-out p-1 rounded-full hover:bg-gray-200">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>

                                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pr-10">
                                            <div class="flex-1 min-w-0 mb-2 sm:mb-0">
                                                <p class="font-medium text-lg text-gray-800 break-words">
                                                    {{ $data['title'] ?? 'General Notification' }}
                                                </p>
                                                <p class="text-sm text-gray-700 mt-1 break-words">{{ $data['message'] ?? '' }}</p>
                                            </div>

                                            <div class="flex-shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                                                {{-- Staff-specific links --}}
                                                @if(in_array(($data['type'] ?? ''), ['substitute_assignment', 'ah_leave_review', 'hr_leave_review', 'admin_leave_review']))
                                                    @if(($data['type'] ?? '') === 'substitute_assignment')
                                                        <a href="{{ $data['acknowledgement_url'] ?? '#' }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 w-full justify-center">
                                                            {{ __('Review & Acknowledge') }}
                                                        </a>
                                                    @else
                                                        @php
                                                            $rolePrefix = Str::before($data['type'], '_leave_review');
                                                            $route = $rolePrefix . '.leave_applications.review';
                                                            $buttonText = 'Review Leave App (' . strtoupper($rolePrefix) . ')';
                                                        @endphp
                                                        <a href="{{ URL::signedRoute($route, ['leaveApplication' => $data['leave_application_id']]) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 w-full justify-center">
                                                            {{ __($buttonText) }}
                                                        </a>
                                                    @endif
                                                {{-- Links for both (Leave decision for staff, Grade posted for student) --}}
                                                @elseif(($data['type'] ?? '') === 'leave_decision' || ($data['type'] ?? '') === 'grade_posted')
                                                    @if(isset($data['view_application_url']) || isset($data['view_grade_url']))
                                                        <a href="{{ $data['view_application_url'] ?? $data['view_grade_url'] ?? '#' }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 w-full justify-center">
                                                            {{ __('View Details') }}
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <form method="POST" action="{{ route('notifications.markAllAsRead') }}" class="mt-4 text-center">
                                @csrf
                                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-900 font-semibold">Mark all as read</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 border-t border-gray-200 text-center text-gray-600">
                            {{ __('You have no new notifications at this time.') }}
                        </div>
                    </div>
                @endif
                {{-- --- END NOTIFICATIONS SECTION --- --}}

                
                {{-- ****************************************************** --}}
                {{-- ** START OF ROLE-SPECIFIC CONTENT (STUDENT VS STAFF) ** --}}
                {{-- ****************************************************** --}}

                @if(Auth::user()->hasRole('student'))
                    
                    {{-- Student Dashboard Content --}}
                    
                    <h4 class="text-xl font-bold text-blue-600 mb-4">📚 My Academic Overview</h4>
                    <hr class="mb-6">

                    {{-- 1. Student Statistics Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-100 border-l-4 border-blue-500 rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold text-blue-700">Current GPA</div>
                            <div class="text-3xl font-bold text-blue-900">{{ number_format($currentGPA ?? 0, 2) }}</div>
                        </div>
                        <div class="bg-green-100 border-l-4 border-green-500 rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold text-green-700">Enrolled Courses</div>
                            <div class="text-3xl font-bold text-green-900">{{ $enrolledCourses->count() ?? 0 }}</div>
                        </div>
                        <div class="bg-purple-100 border-l-4 border-purple-500 rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold text-purple-700">Total Credits</div>
                            <div class="text-3xl font-bold text-purple-900">{{ $totalCredits ?? 0 }}</div>
                        </div>
                    </div>

                    {{-- 2. Upcoming Schedule / Quick Links --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900 mb-4">🗓️ Upcoming Classes / Activities</h4>
                            <div class="bg-gray-50 rounded-lg shadow-lg p-4 h-64 overflow-y-auto border border-gray-200">
                                @forelse ($upcomingSchedule ?? [] as $item)
                                    <div class="flex justify-between items-center py-2 border-b last:border-b-0 border-gray-100 hover:bg-gray-100 rounded px-2 transition">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-800">{{ $item->title }}</span>
                                            <span class="text-xs text-gray-500">{{ $item->course_name }}</span>
                                        </div>
                                        <span class="text-sm text-blue-600 font-semibold">{{ $item->time_display }}</span>
                                    </div>
                                @empty
                                    <p class="text-gray-600 p-4 text-center">No upcoming schedule items. Check your full calendar.</p>
                                @endforelse
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900 mb-4">🔗 Quick Actions & Links</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <a href="#" class="block p-4 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 text-center font-medium transition duration-150">
                                    My Courses
                                </a>
                                <a href="#" class="block p-4 bg-orange-600 text-white rounded-lg shadow hover:bg-orange-700 text-center font-medium transition duration-150">
                                    View Grades
                                </a>
                                <a href="#" class="block p-4 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 text-center font-medium transition duration-150">
                                    Attendance Record
                                </a>
                                <a href="#" class="block p-4 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 text-center font-medium transition duration-150">
                                    Update Profile
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Recent Grades --}}
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-4">💯 Latest Grade Submissions</h4>
                        <div class="bg-gray-50 rounded-lg shadow-md p-4 mb-8 border border-gray-200">
                            @forelse ($recentGrades ?? [] as $grade)
                                <div class="flex justify-between items-center py-2 border-b last:border-b-0 border-gray-200">
                                    <span class="font-medium">{{ $grade->assessment_name ?? 'Assessment' }} in **{{ $grade->enrollment->course->name ?? 'N/A' }}**</span>
                                    <span class="text-lg font-bold 
                                        @if(($grade->score ?? 0) >= 90) text-green-600
                                        @elseif(($grade->score ?? 0) >= 70) text-yellow-600
                                        @else text-red-600
                                        @endif
                                    ">
                                        {{ $grade->score ?? 'N/A' }} / {{ $grade->max_score ?? 'N/A' }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-gray-600">No grades submitted recently.</p>
                            @endforelse
                        </div>
                    </div>

                @else
                
                {{-- Staff/Employee/Admin Dashboard Content (Hidden from Students) --}}

                    {{-- 1. Role-Specific Action Links (AH, HR, Admin) --}}
                    @if(Auth::user()->hasRole('academic_head')) 
                        <div class="mb-6">
                            <a href="{{ route('ah.leave_applications.all') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                {{ __('View All Pending Leave Applications (AH)') }}
                            </a>
                        </div>
                    @elseif(Auth::user()->hasRole('hr')) 
                        <div class="mb-6">
                            <a href="{{ route('hr.leave_applications.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                {{ __('View All Pending Leave Applications (HR)') }}
                            </a>
                        </div>
                    @elseif(Auth::user()->hasRole('admin'))
                        <div class="mb-6">
                            <a href="{{ route('admin.leave_applications.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                {{ __('View All Pending Leave Applications (Admin)') }}
                            </a>
                        </div>
                    @endif 

                    {{-- 2. Your Leave Applications Status (for the applicant - visible to all employees) --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="font-semibold text-xl text-gray-800">{{ __('Your Leave Applications') }}</h3>
                             <a href="{{ route('leaveapplicationstatus') }}" class="block mt-4 p-4 bg-blue-100 rounded-lg shadow hover:bg-blue-200 text-blue-800 text-center font-medium transition">
                                        View/Apply for Leave
                            </a>
                        </div>
                    </div>

                    {{-- 3. Overview Statistics Cards (Visible to staff roles) --}}
                    <h4 class="text-xl font-bold text-indigo-600 mb-4">📊 System Statistics (Staff View)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-blue-500 text-white rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold">Total Students</div>
                            <div class="text-3xl font-bold">{{ $totalStudents }}</div>
                        </div>
                        <div class="bg-green-500 text-white rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold">Total Courses</div>
                            <div class="text-3xl font-bold">{{ $totalCourses }}</div>
                        </div>
                        <div class="bg-purple-500 text-white rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold">Total Enrollments</div>
                            <div class="text-3xl font-bold">{{ $totalEnrollments }}</div>
                        </div>
                        <div class="bg-yellow-500 text-white rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold">Total Teachers</div>
                            <div class="text-3xl font-bold">{{ $totalTeachers }}</div>
                        </div>
                        <div class="bg-indigo-500 text-white rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold">Total Programs</div>
                            <div class="text-3xl font-bold">{{ $totalPrograms }}</div>
                        </div>
                        <div class="bg-pink-500 text-white rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold">Total Sections</div>
                            <div class="text-3xl font-bold">{{ $totalSections }}</div>
                        </div>
                        <div class="bg-gray-700 text-white rounded-lg shadow-md p-5">
                            <div class="text-sm uppercase font-bold">Total Users</div>
                            <div class="text-3xl font-bold">{{ $totalUsers }}</div>
                        </div>
                    </div>

                    {{-- 4. Recent Activity Section (Staff View) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900 mb-4">Recently Added Students</h4>
                            <div class="bg-gray-50 rounded-lg shadow-md p-4">
                                @forelse ($recentStudents as $student)
                                    <div class="flex justify-between items-center py-2 border-b last:border-b-0 border-gray-200">
                                        <span>{{ $student->name }} ({{ $student->email }})</span>
                                        <span class="text-sm text-gray-500">{{ $student->created_at->diffForHumans() }}</span>
                                    </div>
                                @empty
                                    <p class="text-gray-600">No recent students.</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-gray-900 mb-4">Recently Added Courses</h4>
                            <div class="bg-gray-50 rounded-lg shadow-md p-4">
                                @forelse ($recentCourses as $course)
                                    <div class="flex justify-between items-center py-2 border-b last:border-b-0 border-gray-200">
                                        <span>{{ $course->name }} ({{ $course->code }})</span>
                                        <span class="text-sm text-gray-500">{{ $course->created_at->diffForHumans() }}</span>
                                    </div>
                                @empty
                                    <p class="text-gray-600">No recent courses.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                @endif
                {{-- ****************************************************** --}}
                {{-- ** END OF ROLE-SPECIFIC CONTENT ** --}}
                {{-- ****************************************************** --}}
                
            </div>
        </div>
    </div>
@endsection