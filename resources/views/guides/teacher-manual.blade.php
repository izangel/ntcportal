@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- HEADER --}}
    <div class="mb-8">
        <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700 uppercase tracking-wider mb-2">
            <i class="fas fa-book-open mr-1"></i> Teacher User Manual
        </span>
        <h1 class="text-3xl font-bold text-gray-900">Complete Guide for Teachers</h1>
        <p class="mt-2 text-sm text-gray-600 max-w-2xl">
            How to prepare your syllabus, set up assessment tasks, enter scores, track outcomes-based education
            (OBE) data, record attendance, and manage your teaching load.
        </p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('guides.teacher') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50">
                <i class="fas fa-compass"></i> Back to Guides
            </a>
            <a href="{{ route('faculty.syllabus.help') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 border border-amber-200 text-amber-700 rounded-lg text-sm font-bold hover:bg-amber-100">
                <i class="fas fa-file-lines"></i> Syllabus Quick Manual
            </a>
        </div>
    </div>

    {{-- TABLE OF CONTENTS --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 lg:w-72">
        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">On this page</h2>
        <ol class="space-y-1.5 text-sm text-gray-600">
            <li><a href="#getting-started" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">1.</span>Getting Started</a></li>
            <li><a href="#syllabus" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">2.</span>Course Syllabus</a></li>
            <li><a href="#assessment-tasks" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">3.</span>Assessment Tasks Setup</a></li>
            <li><a href="#assessment-scores" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">4.</span>Assessment Scores</a></li>
            <li><a href="#obe-dashboard" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">5.</span>OBE Course Dashboard</a></li>
            <li><a href="#obe-program-report" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">6.</span>OBE Program Report</a></li>
            <li><a href="#obe-reminders" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">7.</span>OBE Data Reminders</a></li>
            <li><a href="#obe-submissions" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">8.</span>OBE Submission Overview</a></li>
            <li><a href="#attainment" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">9.</span>Course Attainment Report</a></li>
            <li><a href="#attendance" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">10.</span>Attendance</a></li>
            <li><a href="#course-load" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">11.</span>Course Load &amp; Faculty Loading</a></li>
            <li><a href="#faq" class="hover:text-indigo-600"><span class="font-black text-indigo-600 mr-1">12.</span>Frequently Asked Questions</a></li>
        </ol>
    </div>

    {{-- SECTION TEMPLATE --}}
    @php
        $step = fn (int $n, string $text) => '<div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 border border-gray-200">
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-black shrink-0">'.$n.'</span>
                <p class="text-sm text-gray-700 leading-relaxed">'.$text.'</p>
            </div>';

        $tip = fn (string $text) => '<div class="mt-2 p-3 rounded-lg bg-blue-50 border border-blue-100">
                <p class="text-xs text-blue-800 leading-relaxed"><i class="fas fa-lightbulb mr-1"></i>'.$text.'</p>
            </div>';
    @endphp

    {{-- 1. GETTING STARTED --}}
    <section id="getting-started" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">1. Getting Started</h2>
        <p class="text-sm text-gray-600 mb-4">Most teaching tools share the same pattern. Once you understand it, every module below is easy.</p>
        <div class="space-y-2">
            {!! $step(1, '<strong>Open the tool from the Faculty section of the sidebar.</strong> Every tool needs you to be linked to your employee profile — if a page says you have no assigned blocks, tell your HR or registrar to link your employee account first.') !!}
            {!! $step(2, '<strong>Set the School Year and Semester</strong> (1st, 2nd, or Summer). Tools such as Syllabus, Assessment Tasks, and Assessment Scores remember your choice while you work.') !!}
            {!! $step(3, '<strong>Select your course block.</strong> A block is one of your assigned classes (course + sections + schedule). Most pages group multiple sections of the same course+schedule into one card.') !!}
            {!! $tip('Only your own blocks appear — the system uses your employee profile to decide what to show.') !!}
        </div>
    </section>

    {{-- 2. SYLLABUS --}}
    <section id="syllabus" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">2. Course Syllabus</h2>
        <p class="text-sm text-gray-600 mb-4">The syllabus is the official plan for each of your classes, made up of course information, grading, and an 18-week learning plan.</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>Course Syllabus</strong>. Pick the School Year and Semester, then find your course block and its program.') !!}
            {!! $step(2, 'Click <strong>Prepare Syllabus</strong> (or <strong>Edit Syllabus</strong> if one exists). A class that serves several programs needs one syllabus per program.') !!}
            {!! $step(3, 'Fill in Course Outcomes &amp; CO-PO Mapping, Assessment Tasks, Grading System (must total 100%), Textbooks and References, Course Requirements, and Classroom Policies.') !!}
            {!! $step(4, 'Complete the <strong>Learning Plan</strong> — one row per teaching week (learning outcomes, topics &amp; readings, activities, assessment tools). Examination weeks are pre-marked.') !!}
            {!! $step(5, 'Use <strong>Save Draft</strong> to keep progress at any time, then <strong>Submit</strong> and <strong>Confirm &amp; Submit</strong> when complete. Submitted syllabi are locked.') !!}
            {!! $step(6, 'If your Program Head returns it for revision, open it again (now editable), read the remarks, fix, and resubmit.') !!}
        </div>
        <div class="mt-4">
            <a href="{{ route('faculty.syllabus.help') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700">
                <i class="fas fa-book-open"></i> Read the full Syllabus Manual
            </a>
        </div>
    </section>

    {{-- 3. ASSESSMENT TASKS --}}
    <section id="assessment-tasks" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">3. Assessment Tasks Setup</h2>
        <p class="text-sm text-gray-600 mb-4">Define the assessment tasks (and their items) for each course and batch, and link each item to a Course Learning Outcome (CLO).</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>Assessment Setup</strong>. Choose School Year and Semester, then select your course block.') !!}
            {!! $step(2, 'Add a task: give it a <strong>title</strong>, choose a <strong>type</strong> (Exam, Quiz, Assignment, Project, or Practical), set its <strong>weight percentage</strong> and <strong>total marks</strong>. Save it.') !!}
            {!! $step(3, 'Under that task, add <strong>assessment items</strong> — give each a name, its maximum marks, and map it to the relevant <strong>CLO</strong>.') !!}
            {!! $step(4, 'Edit or delete tasks anytime. Deleting a task also removes its mapped items.') !!}
            {!! $tip('Tasks are stored per course and batch year. Check that the batch shown matches the term you are preparing.') !!}
        </div>
    </section>

    {{-- 4. ASSESSMENT SCORES --}}
    <section id="assessment-scores" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">4. Assessment Scores</h2>
        <p class="text-sm text-gray-600 mb-4">Record the marks each student received on the assessment items you set up.</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>Assessment Scores</strong>. Choose School Year, Semester, and your course block.') !!}
            {!! $step(2, 'Pick an <strong>assessment task</strong>. The class roster appears with one column per assessment item.') !!}
            {!! $step(3, 'Enter each student\'s marks. Values must be between 0 and the item\'s maximum marks.') !!}
            {!! $step(4, 'Click <strong>Save Scores</strong>. Scores already saved are loaded automatically when you return to the task.') !!}
            {!! $tip('Scores drive the OBE course and program reports, so enter them after every graded activity instead of waiting until the end of the term.') !!}
        </div>
    </section>

    {{-- 5. OBE COURSE DASHBOARD --}}
    <section id="obe-dashboard" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">5. OBE Course Dashboard</h2>
        <p class="text-sm text-gray-600 mb-4">A monitoring view of your classes that shows the course outcomes, how they map to program outcomes, and how students are performing against them.</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>OBE Course Dashboard</strong> and select the School Year, Semester, and course block you want to inspect.') !!}
            {!! $step(2, 'Review each CLO\'s coverage and the mapped program outcomes (the CO-PO matrix).') !!}
            {!! $step(3, 'Use the performance figures to spot outcomes where students need attention, then plan your teaching accordingly.') !!}
        </div>
    </section>

    {{-- 6. OBE PROGRAM REPORT --}}
    <section id="obe-program-report" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">6. OBE Program Report</h2>
        <p class="text-sm text-gray-600 mb-4">Shows how well a program\'s outcomes are being attained by batch, aggregated from the assessment results of all classes in that program.</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>OBE Program Report</strong>. Pick a program, batch, and academic period.') !!}
            {!! $step(2, 'Review the attainment levels for each program outcome and the classes contributing to each result.') !!}
            {!! $step(3, 'Use this to see the big picture; your <strong>Course Attainment Report</strong> covers your own class in detail.') !!}
        </div>
    </section>

    {{-- 7. OBE DATA REMINDERS --}}
    <section id="obe-reminders" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">7. OBE Data Reminders</h2>
        <p class="text-sm text-gray-600 mb-4">See at a glance which of your blocks are missing syllabus, assessment, score, or attainment data for the submission period.</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>OBE Data Reminders</strong>. Only your blocks are listed for teachers.') !!}
            {!! $step(2, 'Each row lists what is missing (e.g., assessment setup, scores, attainment) with a direct action link to the right tool.') !!}
            {!! $step(3, 'Click the action link, complete the missing data, and return — the block should turn complete.') !!}
            {!! $tip('This is the fastest way to know exactly what to finish before the submission deadline.') !!}
        </div>
    </section>

    {{-- 8. OBE SUBMISSION OVERVIEW --}}
    <section id="obe-submissions" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">8. OBE Submission Overview</h2>
        <p class="text-sm text-gray-600 mb-4">Tracks what you still need to submit and shows the submission status of all faculty.</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>OBE Submission Overview</strong>. The top section highlights <strong>your own</strong> blocks and how many are complete.') !!}
            {!! $step(2, 'Use the <strong>Accomplish</strong> links next to any incomplete block to jump straight to the missing work.') !!}
            {!! $step(3, 'The faculty list shows the overall picture — yours is marked so colleagues\' status is not confused with your own.') !!}
        </div>
    </section>

    {{-- 9. COURSE ATTAINMENT --}}
    <section id="attainment" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">9. Course Attainment Report</h2>
        <p class="text-sm text-gray-600 mb-4">The report that shows how well your students met the course learning outcomes, plus the action plans for improvement.</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>Course Attainment</strong> and choose your course block. The report calculates attainment from the scores already entered.') !!}
            {!! $step(2, 'Review the attainment figures per CLO. Add <strong>action plans</strong> — issue, action to take, and a target date — for outcomes that need improvement.') !!}
            {!! $step(3, 'Use <strong>Save Draft</strong> to keep progress, then <strong>Submit Report</strong> to hand it in for review.') !!}
            {!! $tip('Enter assessment scores first; the attainment report is computed from them.') !!}
        </div>
    </section>

    {{-- 10. ATTENDANCE --}}
    <section id="attendance" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">10. Attendance</h2>
        <p class="text-sm text-gray-600 mb-4">Manage attendance for each class meeting — manually or with a QR-code check-in.</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>Attendance</strong>, pick the School Year, Semester, and your course block.') !!}
            {!! $step(2, 'Set the <strong>date</strong> for the session. Choose a date and the roster loads for that day.') !!}
            {!! $step(3, 'Mark each student as <strong>Present, Late, Absent, or Excused</strong>, or clear a mistaken entry. The summary counts update automatically.') !!}
            {!! $step(4, 'To use the QR check-in, <strong>Start Session</strong> to generate a QR code — students scan it at the door and are marked present. The code refreshes every 90 seconds.') !!}
            {!! $tip('A new session can be generated any time; existing entries for the day are kept.') !!}
        </div>
    </section>

    {{-- 11. COURSE LOAD --}}
    <section id="course-load" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-1">11. Course Load &amp; Faculty Loading</h2>
        <p class="text-sm text-gray-600 mb-4">View the classes assigned to you for the term and the official faculty loading records.</p>
        <div class="space-y-2">
            {!! $step(1, 'Open <strong>My Course Load</strong> to see your assigned course blocks for the selected School Year and Semester — course code, sections, and schedule — and whether each block has been finalized.') !!}
            {!! $step(2, 'If a load record is incorrect, contact your registrar or academic head. Assigning and editing official loadings is done on their side.') !!}
        </div>
    </section>

    {{-- 12. FAQ --}}
    <section id="faq" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-24">
        <h2 class="text-lg font-bold text-gray-900 mb-4">12. Frequently Asked Questions</h2>
        <div class="divide-y divide-gray-100">
            <div class="py-3">
                <p class="text-sm font-bold text-gray-800">Why don\'t I see my classes in a tool?</p>
                <p class="text-xs text-gray-600 mt-1">Choose the correct School Year and Semester first. If they are correct and you still see nothing, you are not linked to an employee profile, or no course blocks are assigned to you — contact HR or your registrar.</p>
            </div>
            <div class="py-3">
                <p class="text-sm font-bold text-gray-800">Can I add a student to my attendance roster?</p>
                <p class="text-xs text-gray-600 mt-1">Yes — the attendance page lets you search for a student and add them to the selected class, in case someone is enrolled late.</p>
            </div>
            <div class="py-3">
                <p class="text-sm font-bold text-gray-800">Can I change a score after saving?</p>
                <p class="text-xs text-gray-600 mt-1">Yes. Re-open the same task, correct the cell, and save again — the new value replaces the old one.</p>
            </div>
            <div class="py-3">
                <p class="text-sm font-bold text-gray-800">What is the difference between the OBE screens?</p>
                <p class="text-xs text-gray-600 mt-1"><strong>Dashboard</strong> = your classes\' outcomes and coverage. <strong>Reminders</strong> = what data is missing. <strong>Submission Overview</strong> = what you have (or haven\'t) submitted. <strong>Program Report</strong> = outcomes attainment for the whole program. <strong>Course Attainment</strong> = your class\'s attainment report with action plans.</p>
            </div>
            <div class="py-3">
                <p class="text-sm font-bold text-gray-800">Where do I submit everything?</p>
                <p class="text-xs text-gray-600 mt-1">The syllabus is submitted from the Syllabus editor; the attainment report is submitted from Course Attainment. The OBE Submission Overview and Reminders pages show you anything you still owe.</p>
            </div>
        </div>
    </section>
</div>
@endsection