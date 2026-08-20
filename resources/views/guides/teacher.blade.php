@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- HEADER --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700 uppercase tracking-wider">
                <i class="fas fa-chalkboard-user mr-1"></i> Teachers' Corner
            </span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Teacher Guides</h1>
        <p class="mt-2 text-sm text-gray-600">
            Everything you need to prepare your classes, manage assessments, track attainment, and submit your records —
            with step-by-step guides for each tool.
        </p>
    </div>

    {{-- GUIDE CARDS --}}
    @php
        $manualBase = route('guides.teacher.manual');
        $guideGroups = [
            [
                'title' => 'Syllabus',
                'icon' => 'fa-book-open',
                'color' => 'bg-indigo-50 border-indigo-100 text-indigo-700',
                'guides' => [
                    ['title' => 'Prepare & Submit Your Course Syllabus', 'desc' => 'Full step-by-step manual: fill in the sections, build the grading system and 18-week learning plan, submit, and handle revisions.', 'manual' => "$manualBase#syllabus", 'href' => route('faculty.syllabus.help'), 'icon' => 'fa-file-lines'],
                    ['title' => 'My Syllabi', 'desc' => 'See every course block assigned to you, its sections and programs, and open each syllabus to prepare or edit it.', 'manual' => "$manualBase#syllabus", 'href' => route('faculty.syllabus.index'), 'icon' => 'fa-list'],
                ],
            ],
            [
                'title' => 'Assessments & Grades',
                'icon' => 'fa-pen-to-square',
                'color' => 'bg-blue-50 border-blue-100 text-blue-700',
                'guides' => [
                    ['title' => 'Assessment Tasks Setup', 'desc' => 'Define the assessment tasks and items linked to each course outcome so they show up in the syllabus and score entry.', 'manual' => "$manualBase#assessment-tasks", 'href' => route('faculty.assessment-tasks'), 'icon' => 'fa-list-check'],
                    ['title' => 'Assessment Scores', 'desc' => 'Enter student scores for each assessment task as the term progresses.', 'manual' => "$manualBase#assessment-scores", 'href' => route('faculty.assessment-scores'), 'icon' => 'fa-table'],
                ],
            ],
            [
                'title' => 'Outcomes-Based Education (OBE)',
                'icon' => 'fa-bullseye',
                'color' => 'bg-emerald-50 border-emerald-100 text-emerald-700',
                'guides' => [
                    ['title' => 'OBE Course Dashboard', 'desc' => 'A dashboard of your classes showing program outcomes, course outcomes, and assessment coverage.', 'manual' => "$manualBase#obe-dashboard", 'href' => route('faculty.obe.course-dashboard'), 'icon' => 'fa-gauge-high'],
                    ['title' => 'OBE Program Report', 'desc' => 'View program-level outcomes attainment computed from your assessment results.', 'manual' => "$manualBase#obe-program-report", 'href' => route('faculty.obe.program-report'), 'icon' => 'fa-file-lines'],
                    ['title' => 'OBE Data Reminders', 'desc' => 'See which of your classes still need syllabus, assessment, score, or attainment data completed.', 'manual' => "$manualBase#obe-reminders", 'href' => route('faculty.obe.reminders'), 'icon' => 'fa-bell'],
                    ['title' => 'OBE Submission Overview', 'desc' => 'Check the submission status of your syllabi and attainment reports across your classes.', 'manual' => "$manualBase#obe-submissions", 'href' => route('faculty.obe.submissions'), 'icon' => 'fa-clipboard-list'],
                    ['title' => 'Course Attainment', 'desc' => 'Generate and submit the course attainment report that shows how well students met the course outcomes.', 'manual' => "$manualBase#attainment", 'href' => route('attainment.index'), 'icon' => 'fa-bullseye'],
                ],
            ],
            [
                'title' => 'Classroom & Records',
                'icon' => 'fa-clipboard-user',
                'color' => 'bg-amber-50 border-amber-100 text-amber-700',
                'guides' => [
                    ['title' => 'Attendance', 'desc' => 'Take and manage attendance for each of your class meetings, including QR-code check-in.', 'manual' => "$manualBase#attendance", 'href' => route('attendance.index'), 'icon' => 'fa-user-check'],
                    ['title' => 'Course Load & Faculty Loading', 'desc' => 'Review your assigned course load, schedule, sections, and official loading records for the term.', 'manual' => "$manualBase#course-load", 'href' => route('faculty.course-load'), 'icon' => 'fa-calendar-days'],
                ],
            ],
        ];
    @endphp

    <div class="space-y-8">
        @foreach($guideGroups as $group)
            <section>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg {{ $group['color'] }}">
                        <i class="fas {{ $group['icon'] }}"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $group['title'] }}</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($group['guides'] as $guide)
                        <div class="group bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:border-indigo-300 hover:shadow-md transition">
                            <div class="flex items-center justify-between mb-2">
                                <i class="fas {{ $guide['icon'] }} text-indigo-600 text-lg"></i>
                                <span class="inline-flex items-center gap-1 text-[10px] font-black bg-amber-50 text-amber-600 border border-amber-200 rounded-full px-2 py-0.5 uppercase tracking-wider">
                                    <i class="fas fa-book-open"></i> Manual
                                </span>
                            </div>
                            <a href="{{ $guide['manual'] }}" class="block">
                                <h3 class="text-sm font-bold text-gray-800 group-hover:text-indigo-700 transition">{{ $guide['title'] }}</h3>
                            </a>
                            <p class="mt-1 text-xs text-gray-500 leading-relaxed">{{ $guide['desc'] }}</p>
                            <div class="mt-3 flex items-center gap-3">
                                <a href="{{ $guide['manual'] }}" class="inline-flex items-center gap-1 text-[10px] font-black text-amber-500 uppercase tracking-wider hover:text-amber-700">
                                    <i class="fas fa-book-open"></i> Read the manual
                                </a>
                                <a href="{{ $guide['href'] }}" class="inline-flex items-center gap-1 text-[10px] font-black text-gray-400 uppercase tracking-wider hover:text-indigo-600">
                                    <i class="fas fa-arrow-up-right-from-square"></i> Open feature
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection