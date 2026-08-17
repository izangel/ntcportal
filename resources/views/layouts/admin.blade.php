<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-800">
        <x-banner />

        <div class="min-h-screen flex">
            {{-- Sidebar --}}
<aside class="w-64 bg-gray-900 text-white shadow-lg flex-shrink-0" style="min-height: calc(100vh);">
    <div class="p-6 flex items-center justify-center border-b border-gray-700">
        <h2 class="text-2xl font-bold tracking-tight">
            @php
                $hasStudent = Auth::user()->student;
                $isFaculty = Auth::user()->hasRole('teacher') || Auth::user()->hasRole('faculty') || Auth::user()->hasRole('staff');
            @endphp
            {{ $hasStudent ? 'Student Portal' : ($isFaculty ? 'Faculty Portal' : 'Admin Panel') }}
        </h2>
    </div>
    <nav class="p-4 space-y-2">
        
        {{-- Standard Links (Accessible by All) --}}
        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
            <i class="fas fa-home mr-3 text-lg"></i>
            {{ __('Dashboard') }}
        </x-nav-link>
        <x-nav-link href="{{ route('announcements.index') }}" :active="request()->routeIs('announcements.index')">
            <i class="fas fa-bullhorn mr-3 text-lg"></i>
            {{ __('School Announcements') }}
        </x-nav-link>
        <x-nav-link href="{{ route('important_dates.index') }}" :active="request()->routeIs('important_dates.index')">
            <i class="fas fa-calendar-days mr-3 text-lg"></i>
            {{ __('Important Dates') }}
        </x-nav-link>
        <x-nav-link href="{{ route('system-updates.index') }}" :active="request()->routeIs('system-updates.index')">
            <i class="fas fa-circle-info mr-3 text-lg"></i>
            {{ __('System Updates') }}
        </x-nav-link>

        {{-- My Profile (COLLAPSIBLE) --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">My Profile</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('profile.personal-information') }}" :active="request()->routeIs('profile.personal-information')">
                        <i class="fas fa-address-card mr-3 text-lg"></i>
                        {{ __('Personal Information') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('password.edit') }}" :active="request()->routeIs('password.*')">
                        <i class="fas fa-key mr-3 text-lg"></i>
                        {{ __('Change Password') }}
                    </x-nav-link>
                    
                </div>
            </div>

        {{-- LIBRARY RESOURCES — NOT IMPLEMENTED YET, DISABLED
        <div class="mt-4 space-y-1" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                <h3 class="text-left">Library Resources</h3>
                <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
            </button>
            
            <div x-show="open" x-collapse.duration.300ms>
                <x-nav-link href="#">
                    <i class="fas fa-magnifying-glass mr-3 text-lg"></i>
                    Search Library Catalog
                </x-nav-link>
                <x-nav-link href="#">
                    <i class="fas fa-book mr-3 text-lg"></i>
                    Request Books
                </x-nav-link>
            </div>
        </div>
        --}}

        @if(Auth::user()->student)
             {{-- My Classes & Schedule (COLLAPSIBLE) - Student Only --}}
             <div class="mt-4 space-y-1" x-data="{ open: true }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">MY CLASSES</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>

                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('student.course-blocks') }}" :active="request()->routeIs('student.course-blocks')">
                        <i class="fas fa-calendar-day mr-3 text-lg"></i>
                        {{ __('My Schedule') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('student.courses') }}" :active="request()->routeIs('student.courses')">
                        <i class="fas fa-book-open mr-3 text-lg"></i>
                        {{ __('My Courses') }}
                    </x-nav-link>
                </div>
            </div>

             <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">COURSE EVALUATION</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('student.evaluations.index') }}" :active="request()->routeIs('student.evaluations.index')">
                        <i class="fas fa-clipboard-check mr-3 text-lg"></i>
                        {{ __('Course Evaluation') }}
                    </x-nav-link>

                   
                </div>
            </div>

            {{-- My Attendance (COLLAPSIBLE) - Student Only --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">MY ATTENDANCE</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>

                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('student.attendance.my') }}" :active="request()->routeIs('student.attendance.my')">
                        <i class="fas fa-calendar-check mr-3 text-lg"></i>
                        {{ __('My Attendance') }}
                    </x-nav-link>
                </div>
            </div>

            {{-- MY COURSE MATERIALS — NOT IMPLEMENTED YET, DISABLED
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">MY COURSE MATERIALS</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>

                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('student.course-materials') }}" :active="request()->routeIs('student.course-materials')">
                        <i class="fas fa-folder-open mr-3 text-lg"></i>
                        {{ __('Course Materials') }}
                    </x-nav-link>
                </div>
            </div>
            --}}

            {{-- APPLY FOR CANDIDACY — NOT IMPLEMENTED YET, DISABLED
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Apply for Candidacy</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('student.candidacy.index') }}" :active="request()->routeIs('student.candidacy.index')">
                        <i class="fas fa-file-alt mr-3 text-lg"></i>
                        {{ __('Application Form') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('student.candidacy.status') }}" :active="request()->routeIs('student.candidacy.status')">
                        <i class="fas fa-clipboard-list mr-3 text-lg"></i>
                        {{ __('Application Status') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('student.candidacy.requirements') }}" :active="request()->routeIs('student.candidacy.requirements')">
                        <i class="fas fa-info-circle mr-3 text-lg"></i>
                        {{ __('Requirements') }}
                    </x-nav-link>
                </div>
            </div>
            --}}

            {{-- SSG VOTING — NOT IMPLEMENTED YET, DISABLED
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">SSG Voting</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>

                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('student.voting.index') }}" :active="request()->routeIs('student.voting.index')">
                        <i class="fas fa-vote-yea mr-3 text-lg"></i>
                        {{ __('Cast Vote') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('student.voting.results') }}" :active="request()->routeIs('student.voting.results')">
                        <i class="fas fa-poll mr-3 text-lg"></i>
                        {{ __('View Results') }}
                    </x-nav-link>
                </div>
            </div>
            --}}

        @endif
            
        @if(Auth::user()->hasRole('teacher') || Auth::user()->hasRole('staff') || Auth::user()->hasRole('faculty') || Auth::user()->hasRole('academic_head') || Auth::user()->hasRole('hr') || Auth::user()->hasRole('admin') || Auth::user()->hasRole('registrar') || Auth::user()->hasRole('guidance') || Auth::user()->hasRole('program_head') || Auth::user()->hasRole('program_head_college') || Auth::user()->hasRole('program_head_shs'))
            
            {{-- NEW: ROLE SEPARATOR FOR TEACHERS/STAFF --}}
            <div class="mt-6 pt-3 border-t border-gray-700">
                <h3 class="text-sm font-bold uppercase text-blue-400 px-3 py-1 bg-gray-800 rounded">
                    Faculty/Staff Tools
                </h3>
            </div>

            {{-- Communication and Resources (COLLAPSIBLE) --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Communication and Resources</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="#">
                        <i class="fas fa-bell mr-3 text-lg"></i>
                        {{ __('Notifications') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-file-invoice mr-3 text-lg"></i>
                        {{ __('Memos and Advisories') }}
                    </x-nav-link>
                </div>
            </div>

            

            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">HR Concerns</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    
                    <x-nav-link href="{{ route('leave_applications.index') }}" :active="request()->routeIs('leave_applications.*')">
                        <i class="fas fa-plane-departure mr-3 text-lg"></i>
                        {{ __('My Leave Applications') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-money-check-dollar mr-3 text-lg"></i>
                        {{ __('My Salary / Payslip') }}
                    </x-nav-link>
                </div>
            </div>

            {{-- Class & Student Management (COLLAPSIBLE) --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Class & Student Management</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('faculty.course-load') }}" :active="request()->routeIs('faculty.course-load')">
                        <i class="fas fa-layer-group mr-3 text-lg"></i>
                        {{ __('My Course Load') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('attendance.index') }}" :active="request()->routeIs('attendance.index')">
                        <i class="fas fa-qrcode mr-3 text-lg"></i>
                        {{ __('Attendance (QR)') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('attendance.reports') }}" :active="request()->routeIs('attendance.reports')">
                        <i class="fas fa-chart-column mr-3 text-lg"></i>
                        {{ __('Attendance Reports') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.schedule') }}" :active="request()->routeIs('faculty.schedule')">
                        <i class="fas fa-clock mr-3 text-lg"></i>
                        {{ __('My Class Schedule') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.students') }}" :active="request()->routeIs('faculty.students')">
                        <i class="fas fa-users mr-3 text-lg"></i>
                        {{ __('My Students / Class Details') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.class-record') }}" :active="request()->routeIs('faculty.class-record')">
                        <i class="fas fa-clipboard-list mr-3 text-lg"></i>
                        {{ __('Class Record / Grade Entry') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.class-analytics') }}" :active="request()->routeIs('faculty.class-analytics')">
                        <i class="fas fa-chart-simple mr-3 text-lg"></i>
                        {{ __('Class Analytics') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.contact-sheet') }}" :active="request()->routeIs('faculty.contact-sheet')">
                        <i class="fas fa-address-book mr-3 text-lg"></i>
                        {{ __('Student Contact Sheet') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('course-materials.index') }}" :active="request()->routeIs('course-materials.index')">
                        <i class="fas fa-folder-open mr-3 text-lg"></i>
                        {{ __('Course Materials') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-clipboard-question mr-3 text-lg"></i>
                        {{ __('Exams / Question Bank') }}
                    </x-nav-link>
                </div>
            </div>

            {{-- Evaluation and Grading (COLLAPSIBLE) --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Grading</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('faculty.course-blocks') }}" :active="request()->routeIs('faculty.course-blocks')">
                        <i class="fas fa-file-import mr-3 text-lg"></i>
                        {{ __('Grade Submission') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-comment-dots mr-3 text-lg"></i>
                        {{ __('Course Evaluation') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-clipboard-user mr-3 text-lg"></i>
                        {{ __('Student Self-assessment') }}
                    </x-nav-link>
                    
                   
                </div>
            </div>

            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Performance Evaluation</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('faculty.peer-evaluations.index') }}" :active="request()->routeIs('faculty.peer-evaluations.index')">
                        <i class="fas fa-user-group mr-3 text-lg"></i>
                        {{ __('Peer Evaluation') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.self-evaluations.index') }}" :active="request()->routeIs('faculty.self-evaluations.index')">
                        <i class="fas fa-user-check mr-3 text-lg"></i>
                        {{ __('Self Evaluation') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('supervisor.evaluations.index') }}" :active="request()->routeIs('supervisor.evaluations.index')">
                        <i class="fas fa-user-tie mr-3 text-lg"></i>
                        {{ __('Department Head Evaluation') }}
                    </x-nav-link>
                     <x-nav-link href="{{ route('teacher.evaluations.index') }}" :active="request()->routeIs('teacher.evaluations.index')">
                        <i class="fas fa-chart-simple mr-3 text-lg"></i>
                        {{ __('PES Result') }}
                    </x-nav-link>
                                    
                </div>
            </div>

            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Reports</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('academic_head') || Auth::user()->hasRole('program_head') || Auth::user()->hasRole('program_head_shs') || Auth::user()->hasRole('program_head_college') || Auth::user()->hasRole('registrar'))
                        <x-nav-link href="{{ route('admin.analytics') }}" :active="request()->routeIs('admin.analytics')">
                            <i class="fas fa-chart-pie mr-3 text-lg"></i>
                            {{ __('Institutional Analytics') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link href="{{ route('admin.faculty.courses') }}" :active="request()->routeIs('admin.faculty.courses')">
                        <i class="fas fa-magnifying-glass-chart mr-3 text-lg"></i>
                        {{ __('Grade Submission Tracking') }}
                    </x-nav-link>

                    <x-nav-link href="{{ route('faculty.pes-clearance') }}" :active="request()->routeIs('faculty.pes-clearance')">
                        <i class="fas fa-file-circle-check mr-3 text-lg"></i>
                        {{ __('My PES Submission') }}
                    </x-nav-link>

                   
                    
                    
                    

                    
                </div>
            </div>

            {{-- OSA SECTION --}}
            <div class="mt-6 pt-3 border-t border-gray-700">
                <h3 class="text-sm font-bold uppercase text-blue-400 px-3 py-1 bg-gray-800 rounded">
                    OSA
                </h3>
            </div>

            {{-- SSG Election (COLLAPSIBLE) --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">SSG Election</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('admin.candidacy.index') }}" :active="request()->routeIs('admin.candidacy.index')">
                        <i class="fas fa-user-pen mr-3 text-lg"></i>
                        {{ __('Candidacy Applications') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('admin.candidacy.candidates') }}" :active="request()->routeIs('admin.candidacy.candidates')">
                        <i class="fas fa-users mr-3 text-lg"></i>
                        {{ __('Manage Candidates') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.election.results') }}" :active="request()->routeIs('faculty.election.results')">
                        <i class="fas fa-poll mr-3 text-lg"></i>
                        {{ __('Election Results') }}
                    </x-nav-link>
                </div>
            </div>

            {{-- GUIDANCE SECTION --}}
            <div class="mt-6 pt-3 border-t border-gray-700">
                <h3 class="text-sm font-bold uppercase text-blue-400 px-3 py-1 bg-gray-800 rounded">
                    GUIDANCE OFFICE
                </h3>
            </div>

            
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Teachers Evaluation</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('evaluation.tracker') }}" :active="request()->routeIs('evaluation.tracker')">
                        <i class="fas fa-clipboard-check mr-3 text-lg"></i>
                        {{ __('Start Evaluation') }}
                    </x-nav-link>
                    <x-nav-link href="#" :active="request()->routeIs('admin.candidacy.index')">
                        <i class="fas fa-chart-bar mr-3 text-lg"></i>
                        {{ __('Evaluation Results') }}
                    </x-nav-link>
                   
                </div>
            </div>


            


        @endif


        @if(Auth::user()->hasRole('academic_head') || Auth::user()->hasRole('registrar') || Auth::user()->hasRole('hr') || Auth::user()->hasRole('admin'))
            {{-- NEW: ROLE SEPARATOR FOR ACADEMIC/REGISTRAR --}}
            <div class="mt-6 pt-3 border-t border-gray-700">
                <h3 class="text-sm font-bold uppercase text-blue-400 px-3 py-1 bg-gray-800 rounded">
                    Academic/Registrar Tools
                </h3>
            </div>

            {{-- Enrollment Module (COLLAPSIBLE) --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Enrollment Module</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('course_blocks.index') }}" :active="request()->routeIs('course_blocks.index')">
                        <i class="fas fa-cubes mr-3 text-lg"></i>
                        {{ __('Course Blocks') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('assign.courseblocks') }}" :active="request()->routeIs('assign.courseblocks')">
                        <i class="fas fa-list-check mr-3 text-lg"></i>
                        {{ __('Section Load Manager') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.course-blocks') }}" :active="request()->routeIs('faculty.course-blocks')">
                        <i class="fas fa-chalkboard-user mr-3 text-lg"></i>
                        {{ __('Faculty Course Blocks') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('course-blocks.bulk-uploader') }}" :active="request()->routeIs('course-blocks.bulk-uploader')">
                        <i class="fas fa-cloud-arrow-up mr-3 text-lg"></i>
                        {{ __('Course Blocks Bulk Uploader') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('coursetosections.index') }}" :active="request()->routeIs('coursetosections.*')">
                        <i class="fas fa-diagram-project mr-3 text-lg"></i>
                        {{ __('Course To Sections') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('assign.courses') }}" :active="request()->routeIs('assign.courses')">
                        <i class="fas fa-people-arrows mr-3 text-lg"></i>
                        {{ __('Students To Course Per Section') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('assign.individual') }}" :active="request()->routeIs('assign.individual')">
                        <i class="fas fa-user-plus mr-3 text-lg"></i>
                        {{ __('Students To Course (Individual)') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('students.index') }}" :active="request()->routeIs('students.index')">
                        <i class="fas fa-users-gear mr-3 text-lg"></i>
                        {{ __('Manage Students') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('students.upload.form') }}" :active="request()->routeIs('students.upload.form')">
                        <i class="fas fa-file-csv mr-3 text-lg"></i>
                        {{ __('Upload Students (CSV)') }}
                    </x-nav-link>
                     <x-nav-link href="{{ route('students.studentportal') }}" :active="request()->routeIs('students.studentportal')">
                        <i class="fas fa-people-group mr-3 text-lg"></i>
                        {{ __('Manage Student Sections') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('courses.index') }}" :active="request()->routeIs('courses.*')">
                        <i class="fas fa-book-journal-whills mr-3 text-lg"></i>
                        {{ __('Manage Courses') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('enrollments.index') }}" :active="request()->routeIs('enrollments.*')">
                        <i class="fas fa-file-signature mr-3 text-lg"></i>
                        {{ __('Manage Enrollments') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('programs.index') }}" :active="request()->routeIs('programs.*')">
                        <i class="fas fa-landmark mr-3 text-lg"></i>
                        {{ __('Manage Programs') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('sections.index') }}" :active="request()->routeIs('sections.*')">
                        <i class="fas fa-table-columns mr-3 text-lg"></i>
                        {{ __('Manage Sections') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('academic_years.index') }}" :active="request()->routeIs('academic_years.*')">
                        <i class="fas fa-calendar-check mr-3 text-lg"></i>
                        {{ __('Manage Academic Years') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('semesters.index') }}" :active="request()->routeIs('semesters.*')">
                        <i class="fas fa-timeline mr-3 text-lg"></i>
                        {{ __('Manage Semesters') }}
                    </x-nav-link>
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        <i class="fas fa-chart-line mr-3 text-lg"></i>
                        {{ __('Reports') }}
                    </x-nav-link>
                    
                </div>
            </div>
            
        @endif

        @if(Auth::user()->hasRole('academic_head')|| Auth::user()->hasRole('hr') || Auth::user()->hasRole('admin'))
            {{-- NEW: ROLE SEPARATOR FOR HR/ADMIN --}}
            <div class="mt-6 pt-3 border-t border-gray-700">
               
                <h3 class="text-sm font-bold uppercase text-blue-400 px-3 py-1 bg-gray-800 rounded">
                    HR & Administration
                </h3>
            </div>

            {{-- HR & Leaves (COLLAPSIBLE) --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">HR & Leaves</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('employees.index') }}" :active="request()->routeIs('employees.*')">
                        <i class="fas fa-briefcase mr-3 text-lg"></i>
                        {{ __('Manage Employees') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('roles.index') }}" :active="request()->routeIs('employees.*')">
                        <i class="fas fa-user-tag mr-3 text-lg"></i>
                        {{ __('Employee Roles') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('program-heads.index') }}" :active="request()->routeIs('program-heads.*')">
                        <i class="fas fa-users-gear mr-3 text-lg"></i>
                        {{ __('Assign Program Heads') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('admin.system-updates') }}" :active="request()->routeIs('admin.system-updates')">
                        <i class="fas fa-circle-info mr-3 text-lg"></i>
                        {{ __('Manage System Updates') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('leave_applications.index') }}" :active="request()->routeIs('leave_applications.*')">
                        <i class="fas fa-plane-departure mr-3 text-lg"></i>
                        {{ __('My Leave Applications') }}
                    </x-nav-link>
                     <x-nav-link href="{{ route('leave_credits.summary') }}" :active="request()->routeIs('leave_credits.summary')">
                            <i class="fas fa-coins mr-3 text-lg"></i>
                            {{ __('All Remaining Credits') }}
                        </x-nav-link>
                    @if(Auth::user()->hasRole('academic_head'))
                        <x-nav-link href="{{ route('ah.leave_applications.index') }}" :active="request()->routeIs('ah.leave_applications.index')">
                            <i class="fas fa-hourglass-half mr-3 text-lg"></i>
                            {{ __('Pending Leave Applications') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('hr.leave_applications.all') }}" :active="request()->routeIs('hr.leave_applications.all')">
                            <i class="fas fa-list-ul mr-3 text-lg"></i>
                            {{ __('All Leave Applications (HR)') }}
                        </x-nav-link>
                        
                       
                    @elseif(Auth::user()->hasRole('hr'))
                        <x-nav-link href="{{ route('hr.leave_applications.index') }}" :active="request()->routeIs('hr.leave_applications.index')">
                            <i class="fas fa-hourglass-half mr-3 text-lg"></i>
                            {{ __('Pending Leave Applications') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('hr.leave_applications.create_retroactive') }}" :active="request()->routeIs('hr.leave_applications.create_retroactive')">
                            <i class="fas fa-file-upload mr-3 text-lg"></i>
                            {{ __('Unfiled Leave Applications') }}
                        </x-nav-link>
                        
                        <x-nav-link href="{{ route('hr.leave_applications.all') }}" :active="request()->routeIs('hr.leave_applications.all')">
                            <i class="fas fa-list-ul mr-3 text-lg"></i>
                            {{ __('All Leave Applications') }}
                        </x-nav-link>
                       
                    @elseif(Auth::user()->hasRole('admin'))
                        <x-nav-link href="{{ route('admin.leave_applications.index') }}" :active="request()->routeIs('admin.leave_applications.index')">
                            <i class="fas fa-hourglass-half mr-3 text-lg"></i>
                            {{ __('Pending Leave Applications') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('hr.leave_applications.all') }}" :active="request()->routeIs('hr.leave_applications.all')">
                            <i class="fas fa-list-ul mr-3 text-lg"></i>
                            {{ __('All Leave Applications (HR)') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link href="{{ route('faculty-loadings.index') }}" :active="request()->routeIs('faculty-loadings.*')">
                        <i class="fas fa-chalkboard-teacher mr-3 text-lg"></i>
                        {{ __('Faculty Loading') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('admin.leave.summary') }}" :active="request()->routeIs('admin.leave.summary')">
                        <i class="fas fa-calendar-day mr-3 text-lg"></i>
                        {{ __('Leave Summary') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('admin.leave.analytics') }}" :active="request()->routeIs('admin.leave.analytics')">
                        <i class="fas fa-chart-column mr-3 text-lg"></i>
                        {{ __('Leave Analytics') }}
                    </x-nav-link>
                </div>
            </div>
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Performance Evaluation Settings</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('hr.peer-assignments.index') }}" :active="request()->routeIs('hr.peer-assignments.index')">
                        <i class="fas fa-handshake mr-3 text-lg"></i>
                        {{ __('Peer Assignment') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('hr.supervisor-assignments.index') }}" :active="request()->routeIs('hr.supervisor-assignments.index')">
                        <i class="fas fa-user-gear mr-3 text-lg"></i>
                        {{ __('Department Head Assignment') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.reports.summary') }}" :active="request()->routeIs('faculty.reports.summary')">
                        <i class="fas fa-chart-simple mr-3 text-lg"></i>
                        {{ __('PES Result') }}
                    </x-nav-link>

                     <x-nav-link href="{{ route('admin.pes-tracker') }}" :active="request()->routeIs('admin.pes-tracker')">
                        <i class="fas fa-magnifying-glass-chart mr-3 text-lg"></i>
                        {{ __('PES Submission Tracker') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('pes-tracker.settings') }}" :active="request()->routeIs('pes-tracker.settings')">
                        <i class="fas fa-gear mr-3 text-lg"></i>
                        {{ __('PES Tracker Default Period') }}
                    </x-nav-link>

                   
                   
                </div>
            </div>
        @endif

        @if(Auth::user()->hasRole('hr') || Auth::user()->hasRole('admin'))
            {{-- HR Admin (COLLAPSIBLE) --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">HR Admin</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('leave-credits.index') }}" :active="request()->routeIs('leave-credits.index')">
                        <i class="fas fa-calendar-plus mr-3 text-lg"></i>
                        {{ __('Set Leave Credits') }}
                    </x-nav-link>
                </div>
            </div>
        @endif

            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">Outcomes-based Education</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    @php
                        $isHead = Auth::user()->hasRole('academic_head') || Auth::user()->hasRole('registrar') || Auth::user()->hasRole('hr') || Auth::user()->hasRole('admin') || Auth::user()->hasRole('program_head_shs');
                        $isPh   = Auth::user()->hasRole('program_head') || Auth::user()->hasRole('program_head_college') || Auth::user()->hasRole('program_head_shs');
                    @endphp
                    @if(!Auth::user()->student)

                        {{-- ============ PROGRAM HEADS & ADMIN ============ --}}
                        @if($isHead)
                            <p class="mt-3 px-3 text-[10px] font-bold text-indigo-300/80 uppercase tracking-wider border-b border-gray-700/60 pb-1">Program Heads &amp; Admin</p>
                            <x-nav-link href="{{ route('admin.obe.setup') }}" :active="request()->routeIs('admin.obe.setup')">
                                <i class="fas fa-sliders mr-3 text-lg"></i>
                                {{ __('1-OBE Configuration') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('admin.obe.program-courses') }}" :active="request()->routeIs('admin.obe.program-courses')">
                                <i class="fas fa-book-open mr-3 text-lg"></i>
                                {{ __('2-Program Course Manager') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('admin.obe.program-report') }}" :active="request()->routeIs('admin.obe.program-report')">
                                <i class="fas fa-file-lines mr-3 text-lg"></i>
                                {{ __('3-OBE Program Report') }}
                            </x-nav-link>
                            @if($isPh)
                            <x-nav-link href="{{ route('faculty.syllabus.reviews') }}" :active="request()->routeIs('faculty.syllabus.reviews')">
                                <i class="fas fa-clipboard-check mr-3 text-lg"></i>
                                {{ __('Syllabus Reviews (PH)') }}
                            </x-nav-link>
                            @endif
                            @if(Auth::user()->hasRole('academic_head'))
                            <x-nav-link href="{{ route('faculty.syllabus.approvals') }}" :active="request()->routeIs('faculty.syllabus.approvals')">
                                <i class="fas fa-stamp mr-3 text-lg"></i>
                                {{ __('Syllabus Approvals (AH)') }}
                            </x-nav-link>
                            @endif
                            <x-nav-link href="{{ route('admin.obe.course-dashboard') }}" :active="request()->routeIs('admin.obe.course-dashboard')">
                                <i class="fas fa-gauge-high mr-3 text-lg"></i>
                                {{ __('6-OBE Course Dashboard') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('admin.obe.reminders') }}" :active="request()->routeIs('admin.obe.reminders')">
                                <i class="fas fa-bell mr-3 text-lg"></i>
                                {{ __('7-OBE Data Reminders') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('admin.obe.submissions') }}" :active="request()->routeIs('admin.obe.submissions')">
                                <i class="fas fa-clipboard-list mr-3 text-lg"></i>
                                {{ __('8-OBE Submission Overview') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('attainment.admin') }}" :active="request()->routeIs('attainment.admin')">
                                <i class="fas fa-bullseye mr-3 text-lg"></i>
                                {{ __('9-Course Attainment') }}
                            </x-nav-link>
                        @endif

                        {{-- ============ FACULTY ============ --}}
                        <p class="mt-3 px-3 text-[10px] font-bold text-indigo-300/80 uppercase tracking-wider border-b border-gray-700/60 pb-1">Faculty</p>
                        <x-nav-link href="{{ route('faculty.obe.program-report') }}" :active="request()->routeIs('faculty.obe.program-report')">
                            <i class="fas fa-file-lines mr-3 text-lg"></i>
                            {{ __('3-OBE Program Report') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('faculty.assessment-tasks') }}" :active="request()->routeIs('faculty.assessment-tasks')">
                            <i class="fas fa-list-check mr-3 text-lg"></i>
                            {{ __('4-Assessment Setup') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('faculty.syllabus.index') }}" :active="request()->routeIs('faculty.syllabus.index') || request()->routeIs('faculty.syllabus.edit')">
                            <i class="fas fa-file-lines mr-3 text-lg"></i>
                            {{ __('Course Syllabus') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('faculty.assessment-scores') }}" :active="request()->routeIs('faculty.assessment-scores')">
                            <i class="fas fa-pen-to-square mr-3 text-lg"></i>
                            {{ __('5-Assessment Scores') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('faculty.obe.course-dashboard') }}" :active="request()->routeIs('faculty.obe.course-dashboard')">
                            <i class="fas fa-gauge-high mr-3 text-lg"></i>
                            {{ __('6-OBE Course Dashboard') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('faculty.obe.reminders') }}" :active="request()->routeIs('faculty.obe.reminders')">
                            <i class="fas fa-bell mr-3 text-lg"></i>
                            {{ __('7-OBE Data Reminders') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('faculty.obe.submissions') }}" :active="request()->routeIs('faculty.obe.submissions')">
                            <i class="fas fa-clipboard-list mr-3 text-lg"></i>
                            {{ __('8-OBE Submission Overview') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('attainment.index') }}" :active="request()->routeIs('attainment.index') || request()->routeIs('faculty.course-attainment.report')">
                            <i class="fas fa-bullseye mr-3 text-lg"></i>
                            {{ __('9-Course Attainment') }}
                        </x-nav-link>
                    @endif
                                    
                </div>
            </div>
    </nav>
</aside>

            <div class="flex-1 flex flex-col">
                {{-- Navigation Menu (Top Bar) --}}
                <header class="bg-white shadow-sm sticky top-0 z-50">
                    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                        @livewire('navigation-menu')
                    </div>
                </header>

                @if (isset($header))
                    <header class="bg-white shadow-sm border-b border-gray-200">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                                {{ $header }}
                            </h2>
                        </div>
                    </header>
                @endif

{{-- Main Content Area --}}
<main class="flex-1 p-6 sm:p-8">

   
    
                    @yield('content')

                    
                </main>
            </div>
        </div>

        @stack('modals')
        @livewire('flash-toast')
        @livewireScripts
        @stack('scripts')
    </body>
</html>
