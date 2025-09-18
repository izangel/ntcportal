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
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        <i class="fas fa-home mr-3 text-lg"></i>
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(Auth::user()->hasRole('academic_head') || Auth::user()->hasRole('registrar'))
                        <div class="mt-4 space-y-1">
                            <h3 class="text-xs font-semibold uppercase text-gray-400 mb-2 px-3">Academic Management</h3>
                            <x-nav-link href="{{ route('students.index') }}" :active="request()->routeIs('students.*')">
                                <i class="fas fa-user-graduate mr-3 text-lg"></i>
                                {{ __('Manage Students') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('courses.index') }}" :active="request()->routeIs('courses.*')">
                                <i class="fas fa-book mr-3 text-lg"></i>
                                {{ __('Manage Courses') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('enrollments.index') }}" :active="request()->routeIs('enrollments.*')">
                                <i class="fas fa-file-invoice mr-3 text-lg"></i>
                                {{ __('Manage Enrollments') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('programs.index') }}" :active="request()->routeIs('programs.*')">
                                <i class="fas fa-graduation-cap mr-3 text-lg"></i>
                                {{ __('Manage Programs') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('sections.index') }}" :active="request()->routeIs('sections.*')">
                                <i class="fas fa-users-class mr-3 text-lg"></i>
                                {{ __('Manage Sections') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('academic_years.index') }}" :active="request()->routeIs('academic_years.*')">
                                <i class="fas fa-calendar-alt mr-3 text-lg"></i>
                                {{ __('Manage Academic Years') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('semesters.index') }}" :active="request()->routeIs('semesters.*')">
                                <i class="fas fa-calendar-check mr-3 text-lg"></i>
                                {{ __('Manage Semesters') }}
                            </x-nav-link>
                             <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                                <i class="fas fa-chart-line mr-3 text-lg"></i>
                                {{ __('Reports') }}
                            </x-nav-link>
                        </div>
                    @endif

                    @if(Auth::user()->hasRole('academic_head')|| Auth::user()->hasRole('hr') || Auth::user()->hasRole('admin'))
                        <div class="mt-4 space-y-1">
                            <h3 class="text-xs font-semibold uppercase text-gray-400 mb-2 px-3">HR & Leaves</h3>
                            <x-nav-link href="{{ route('employees.index') }}" :active="request()->routeIs('employees.*')">
                                <i class="fas fa-user-tie mr-3 text-lg"></i>
                                {{ __('Manage Employees') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('leave_applications.index') }}" :active="request()->routeIs('leave_applications.*')">
                                <i class="fas fa-calendar-minus mr-3 text-lg"></i>
                                {{ __('My Leave Applications') }}
                            </x-nav-link>
                             <x-nav-link href="{{ route('hr.leave_applications.index') }}" :active="request()->routeIs('hr.leave_applications.index')">
                                <i class="fas fa-hourglass-half mr-3 text-lg"></i>
                                {{ __('Pending Leave Applications') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('hr.leave_applications.all') }}" :active="request()->routeIs('hr.leave_applications.all')">
                                <i class="fas fa-list-ul mr-3 text-lg"></i>
                                {{ __('All Leave Applications') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('hr.leave_credits.all') }}" :active="request()->routeIs('hr.leave_credits.all')">
                                <i class="fas fa-credit-card mr-3 text-lg"></i>
                                {{ __('View All Remaining Leave Credits') }}
                            </x-nav-link>
                        </div>
                    @endif

                    @if(Auth::user()->hasRole('hr') || Auth::user()->hasRole('admin'))
                        <div class="mt-4 space-y-1">
                            <h3 class="text-xs font-semibold uppercase text-gray-400 mb-2 px-3">HR Admin</h3>
                            <x-nav-link href="{{ route('leave-credits.index') }}" :active="request()->routeIs('leave-credits.index')">
                                <i class="fas fa-calendar-plus mr-3 text-lg"></i>
                                {{ __('Set Leave Credits') }}
                            </x-nav-link>
                        </div>
                    @endif

                    @if(Auth::user()->hasRole('teacher') || Auth::user()->hasRole('staff'))
                        <div class="mt-4 space-y-1">
                            <h3 class="text-xs font-semibold uppercase text-gray-400 mb-2 px-3">My Leaves</h3>
                            <x-nav-link href="{{ route('leave_applications.index') }}" :active="request()->routeIs('leave_applications.*')">
                                <i class="fas fa-calendar-minus mr-3 text-lg"></i>
                                {{ __('My Leave Applications') }}
                            </x-nav-link>
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