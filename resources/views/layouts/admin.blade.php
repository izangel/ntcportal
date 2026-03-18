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
        <h2 class="text-2xl font-bold tracking-tight">Admin Panel</h2>
    </div>
    <nav class="p-4 space-y-2">
        
        {{-- Standard Links (Accessible by All) --}}
        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
            <i class="fas fa-home mr-3 text-lg"></i>
            {{ __('Dashboard') }}
        </x-nav-link>
        <x-nav-link href="{{ route('announcements.index') }}" :active="request()->routeIs('announcements.index')">
            <i class="fa-solid fa-bullhorn mr-3 text-lg"></i>
            {{ __('School Announcements') }}
        </x-nav-link>
        <x-nav-link href="{{ route('important_dates.index') }}" :active="request()->routeIs('important_dates.index')">
            <i class="fa-solid fa-calendar-days mr-3 text-lg"></i>
            {{ __('Important Dates') }}
        </x-nav-link>

        {{-- My Profile (COLLAPSIBLE) --}}
            <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">My Profile</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('profile.personal-information') }}" :active="request()->routeIs('profile.personal-information')">
                        <i class="fas fa-user-graduate mr-3 text-lg"></i>
                        {{ __('Personal Information') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('password.edit') }}" :active="request()->routeIs('password.*')">
                        <i class="fas fa-key mr-3 text-lg"></i>
                        {{ __('Change Password') }}
                    </x-nav-link>
                    
                </div>
            </div>

        {{-- Library Resources (COLLAPSIBLE) --}}
        <div class="mt-4 space-y-1" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                <h3 class="text-left">Library Resources</h3>
                <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
            </button>
            
            <div x-show="open" x-collapse.duration.300ms>
                <x-nav-link href="#">
                    <i class="fas fa-solid fa-book-open /> mr-3 text-lg"></i>
                    {{ __('Search Library Catalog') }}
                </x-nav-link>
                <x-nav-link href="#">
                    <i class="fas fa-book mr-3 text-lg"></i>
                    {{ __('Request Books') }}
                </x-nav-link>
            </div>
        </div>

        @if(Auth::user()->student)
             <div class="mt-4 space-y-1" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-xs font-semibold uppercase text-gray-400 px-3 py-2 hover:bg-gray-700/50 rounded-md transition duration-150 ease-in-out focus:outline-none">
                    <h3 class="text-left">COURSE EVALUATION</h3>
                    <i class="fas fa-chevron-down text-xs transform transition duration-200" :class="{'rotate-180': open, 'rotate-0': !open}"></i>
                </button>
                
                <div x-show="open" x-collapse.duration.300ms>
                    <x-nav-link href="{{ route('student.evaluations.index') }}" :active="request()->routeIs('student.evaluations.index')">
                        <i class="fas fa-solid  fa-clipboard-check mr-3 text-lg"></i>
                        {{ __('Course Evaluation') }}
                    </x-nav-link>

                   
                </div>
            </div>

            {{-- Apply for Candidacy (COLLAPSIBLE) - Student Only --}}
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

        @endif
            
        @if(Auth::user()->hasRole('teacher') || Auth::user()->hasRole('staff') || Auth::user()->hasRole('academic_head') || Auth::user()->hasRole('hr') || Auth::user()->hasRole('admin'))
            
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
                        <i class="fas fa-solid fa-file-invoice mr-3 text-lg"></i>
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
                        <i class="fas fa-solid fa-calendar-minus mr-3 text-lg"></i>
                        {{ __('My Leave Applications') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-solid fa-money-check-dollar mr-3 text-lg"></i>
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
                        <i class="fas fa-solid fa-layer-group mr-3 text-lg"></i>
                        {{ __('My Course Load') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-solid fa-clock mr-3 text-lg"></i>
                        {{ __('My Class Schedule') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-solid fa-users mr-3 text-lg"></i>
                        {{ __('My Students / Class Details') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-solid fa-folder-open mr-3 text-lg"></i>
                        {{ __('Course Materials') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-solid fa-list-check mr-3 text-lg"></i>
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
                        <i class="fas fa-solid fa-file-import mr-3 text-lg"></i>
                        {{ __('Grade Submission') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-solid fa-comment-dots mr-3 text-lg"></i>
                        {{ __('Course Evaluation') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-solid fa-clipboard-user mr-3 text-lg"></i>
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
                        <i class="fas fa-solid fa-users-viewfinder mr-3 text-lg"></i>
                        {{ __('Peer Evaluation') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.self-evaluations.index') }}" :active="request()->routeIs('faculty.self-evaluations.index')">
                        <i class="fas fa-solid fa-user-check mr-3 text-lg"></i>
                        {{ __('Self Evaluation') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('supervisor.evaluations.index') }}" :active="request()->routeIs('supervisor.evaluations.index')">
                        <i class="fas fa-solid fa-user-tie mr-3 text-lg"></i>
                        {{ __('Department Head Evaluation') }}
                    </x-nav-link>
                     <x-nav-link href="{{ route('teacher.evaluations.index') }}" :active="request()->routeIs('teacher.evaluations.index')">
                        <i class="fas fa-solid fa-chart-simple mr-3 text-lg"></i>
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
                    <x-nav-link href="{{ route('admin.faculty.courses') }}" :active="request()->routeIs('admin.faculty.courses')">
                        <i class="fas fa-solid fa-magnifying-glass-chart mr-3 text-lg"></i>
                        {{ __('Grade Submission Tracking') }}
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
                        <i class="fas fa-vote-yea mr-3 text-lg"></i>
                        {{ __('Candidacy Applications') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('admin.candidacy.candidates') }}" :active="request()->routeIs('admin.candidacy.candidates')">
                        <i class="fas fa-users mr-3 text-lg"></i>
                        {{ __('Manage Candidates') }}
                    </x-nav-link>
                    <x-nav-link href="#">
                        <i class="fas fa-poll mr-3 text-lg"></i>
                        {{ __('Election Results') }}
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
                        <i class="fas fa-solid fa-cubes mr-3 text-lg"></i>
                        {{ __('Course Blocks') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('assign.courseblocks') }}" :active="request()->routeIs('assign.courseblocks')">
                        <i class="fas fa-solid fa-cubes mr-3 text-lg"></i>
                        {{ __('Section Load Manager') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.course-blocks') }}" :active="request()->routeIs('faculty.course-blocks')">
                        <i class="fas fa-solid fa-chalkboard-user mr-3 text-lg"></i>
                        {{ __('Faculty Course Blocks') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('course-blocks.bulk-uploader') }}" :active="request()->routeIs('course-blocks.bulk-uploader')">
                        <i class="fas fa-solid fa-cloud-arrow-up mr-3 text-lg"></i>
                        {{ __('Course Blocks Bulk Uploader') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('coursetosections.index') }}" :active="request()->routeIs('coursetosections.*')">
                        <i class="fas fa-solid fa-diagram-project mr-3 text-lg"></i>
                        {{ __('Course To Sections') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('assign.courses') }}" :active="request()->routeIs('assign.courses')">
                        <i class="fas fa-solid fa-people-arrows mr-3 text-lg"></i>
                        {{ __('Students To Course Per Section') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('assign.individual') }}" :active="request()->routeIs('assign.individual')">
                        <i class="fas fa-solid fa-user-plus mr-3 text-lg"></i>
                        {{ __('Students To Course (Individual)') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('students.index') }}" :active="request()->routeIs('students.index')">
                        <i class="fas fa-solid fa-users-gear mr-3 text-lg"></i>
                        {{ __('Manage Students') }}
                    </x-nav-link>
                     <x-nav-link href="{{ route('students.studentportal') }}" :active="request()->routeIs('students.studentportal')">
                        <i class="fas fa-solid fa-users-gear mr-3 text-lg"></i>
                        {{ __('Manage Student Sections') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('courses.index') }}" :active="request()->routeIs('courses.*')">
                        <i class="fas fa-solid fa-book-journal-whills mr-3 text-lg"></i>
                        {{ __('Manage Courses') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('enrollments.index') }}" :active="request()->routeIs('enrollments.*')">
                        <i class="fas fa-solid fa-file-signature mr-3 text-lg"></i>
                        {{ __('Manage Enrollments') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('programs.index') }}" :active="request()->routeIs('programs.*')">
                        <i class="fas fa-solid fa-landmark   mr-3 text-lg"></i>
                        {{ __('Manage Programs') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('sections.index') }}" :active="request()->routeIs('sections.*')">
                        <i class="fas fa-solid fa-table-columns mr-3 text-lg"></i>
                        {{ __('Manage Sections') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('academic_years.index') }}" :active="request()->routeIs('academic_years.*')">
                        <i class="fas fa-solid fa-calendar-check mr-3 text-lg"></i>
                        {{ __('Manage Academic Years') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('semesters.index') }}" :active="request()->routeIs('semesters.*')">
                        <i class="fas fa-solid fa-timeline mr-3 text-lg"></i>
                        {{ __('Manage Semesters') }}
                    </x-nav-link>
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        <i class="fas fa-solid fa-chart-line mr-3 text-lg"></i>
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
                        <i class="fas fa-user-tie mr-3 text-lg"></i>
                        {{ __('Manage Employees') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('leave_applications.index') }}" :active="request()->routeIs('leave_applications.*')">
                        <i class="fas fa-calendar-minus mr-3 text-lg"></i>
                        {{ __('My Leave Applications') }}
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
                        <x-nav-link href="{{ route('hr.leave_applications.retroactive_form') }}" :active="request()->routeIs('hr.leave_applications.retroactive_form')">
                            <i class="fas fa-file-upload mr-3 text-lg"></i>
                            {{ __('Unfiled Leave Applications') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('hr.leave_applications.all') }}" :active="request()->routeIs('hr.leave_applications.all')">
                            <i class="fas fa-list-ul mr-3 text-lg"></i>
                            {{ __('All Leave Applications') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('hr.leave_credits.all') }}" :active="request()->routeIs('hr.leave_credits.all')">
                            <i class="fas fa-credit-card mr-3 text-lg"></i>
                            {{ __('View All Remaining Leave Credits') }}
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
                        <i class="fas fa-calendar-check mr-3 text-lg"></i>
                        {{ __('Leave Summary') }}
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
                        <i class="fas fa-user-tie mr-3 text-lg"></i>
                        {{ __('Peer Assignment') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('hr.supervisor-assignments.index') }}" :active="request()->routeIs('hr.supervisor-assignments.index')">
                        <i class="fas fa-calendar-minus mr-3 text-lg"></i>
                        {{ __('Department Head Assignment') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('faculty.reports.summary') }}" :active="request()->routeIs('faculty.reports.summary')">
                        <i class="fas fa-calendar-minus mr-3 text-lg"></i>
                        {{ __('PES Result') }}
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
        @livewireScripts
        @stack('scripts')
    </body>
</html>
