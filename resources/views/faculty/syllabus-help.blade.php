@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- HEADER --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700 uppercase tracking-wider">
                <i class="fas fa-book-open mr-1"></i> Teacher's Guide
            </span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Preparing &amp; Submitting Your Course Syllabus</h1>
        <p class="mt-2 text-sm text-gray-600">
            A step-by-step guide on how to prepare, save, and submit a course syllabus for each of your assigned
            course blocks in the portal.
        </p>
        <a href="{{ route('faculty.syllabus.index') }}"
           class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700">
            <i class="fas fa-arrow-left"></i> Back to My Syllabi
        </a>
    </div>

    {{-- WORKFLOW OVERVIEW --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-base font-bold text-gray-800 mb-4">How the workflow works</h2>
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200">Draft</span>
            <i class="fas fa-arrow-right text-gray-300"></i>
            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 border border-amber-200">Submitted</span>
            <i class="fas fa-arrow-right text-gray-300"></i>
            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 border border-blue-200">Program Head Review</span>
            <i class="fas fa-arrow-right text-gray-300"></i>
            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">Approved</span>
        </div>
        <p class="mt-4 text-xs text-gray-500 leading-relaxed">
            Your Program Head reviews each submitted syllabus. If changes are needed, it is <strong>returned for
            revision</strong> — you will see the remarks and the syllabus becomes editable again so you can fix it and
            resubmit. Once your Program Head and the Academic Head approve it, the syllabus is final.
        </p>
    </div>

    {{-- STEP 1 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">1</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">Open the Course Syllabus page</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    In the menu, go to <strong>Course Syllabus</strong> (under your faculty tools). The page lists every
                    course block assigned to you. It is grouped by course and meeting schedule, and shows the sections
                    and programs for each block.
                </p>
                <div class="mt-3 p-4 rounded-lg bg-gray-50 border border-gray-200">
                    <p class="text-xs font-bold text-gray-700 mb-2">Tips</p>
                    <ul class="text-xs text-gray-600 space-y-1.5 list-disc pl-4">
                        <li>Select the correct <strong>School Year</strong> and <strong>Semester</strong> (1st, 2nd, or Summer) at the top of the page before looking for your class.</li>
                        <li>Each block shows one badge per program. <span class="font-semibold text-emerald-600">Prepared</span> means a completed syllabus already exists; <span class="font-semibold text-amber-600">Incomplete</span> or <span class="font-semibold text-amber-600">Not yet prepared</span> means you still need to work on it.</li>
                        <li>If a class serves more than one program, prepare a syllabus <strong>for each program</strong> using its own button.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 2 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">2</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">Fill in the syllabus sections</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    Click <strong>Prepare Syllabus</strong> (or <strong>Edit Syllabus</strong>) to open the editor.
                    Complete the following sections:
                </p>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    <li class="flex items-start gap-2"><i class="fas fa-angle-right text-indigo-400 mt-1"></i><span><strong class="text-gray-800">Course Outcomes &amp; CO-PO Mapping with Assessment Tasks</strong> — the course outcomes, their mapping to program outcomes, and connection to assessment tasks. This is refreshed automatically as you work.</span></li>
                    <li class="flex items-start gap-2"><i class="fas fa-angle-right text-indigo-400 mt-1"></i><span><strong class="text-gray-800">Assessment Tasks Setup</strong> — define the assessments linked to the course outcomes.</span></li>
                    <li class="flex items-start gap-2"><i class="fas fa-angle-right text-indigo-400 mt-1"></i><span><strong class="text-gray-800">Grading System</strong> — add your grading components (assessment type and percentage). Use the available presets for lecture or laboratory courses, or add your own rows.</span></li>
                    <li class="flex items-start gap-2"><i class="fas fa-angle-right text-indigo-400 mt-1"></i><span><strong class="text-gray-800">Textbooks and References</strong> — the book list and reference materials for the course.</span></li>
                    <li class="flex items-start gap-2"><i class="fas fa-angle-right text-indigo-400 mt-1"></i><span><strong class="text-gray-800">Course Requirements</strong> — the major requirements students must complete.</span></li>
                    <li class="flex items-start gap-2"><i class="fas fa-angle-right text-indigo-400 mt-1"></i><span><strong class="text-gray-800">Classroom Policies and Procedures</strong> — attendance, late submissions, academic integrity, and similar policies. A sample policy set can be loaded with one click and then edited.</span></li>
                </ul>
                <div class="mt-3 p-4 rounded-lg bg-blue-50 border border-blue-100">
                    <p class="text-xs text-blue-800 leading-relaxed">
                        <i class="fas fa-circle-exclamation mr-1"></i>
                        Your <strong>grading percentages must add up to exactly 100%</strong>, and every component needs
                        a type and a percentage greater than zero. The system checks this when you submit.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 3 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">3</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">Complete the 18-week Learning Plan</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    The <strong>Learning Plan</strong> is a fixed grid of 18 weeks. For each teaching week, fill in:
                </p>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-600">
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-bullseye text-indigo-500 mr-1"></i> <strong>Learning Outcomes</strong></div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-book text-indigo-500 mr-1"></i> <strong>Topics &amp; Readings</strong></div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-chalkboard text-indigo-500 mr-1"></i> <strong>Learning Activities</strong></div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-clipboard-check text-indigo-500 mr-1"></i> <strong>Assessment Tools</strong></div>
                </div>
                <div class="mt-3 p-4 rounded-lg bg-blue-50 border border-blue-100">
                    <p class="text-xs text-blue-800 leading-relaxed">
                        <i class="fas fa-circle-exclamation mr-1"></i>
                        Examination weeks (first, second, third, and final examinations) are pre-marked and do not need
                        learning-plan content. <strong>All other weeks must be filled in completely</strong> before you
                        can submit.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 4 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">4</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">Save drafts as you work</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    Use <strong>Save Draft</strong> at any time to save your progress. Drafts can be incomplete, so you
                    can stop and continue later without losing your work. Your badge on the syllabus page will update
                    once a draft exists.
                </p>
            </div>
        </div>
    </div>

    {{-- STEP 5 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">5</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">Submit the syllabus</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    When everything is complete, click <strong>Submit</strong>. The system runs a completeness check:
                </p>
                <ul class="mt-3 space-y-1.5 text-xs text-gray-600 list-disc pl-5">
                    <li>Grading components are filled in and total exactly 100%.</li>
                    <li>All required course-outcome / program-outcome rules are satisfied.</li>
                    <li>Every non-examination week of the learning plan is complete.</li>
                </ul>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                    If anything is missing, the system tells you exactly which items to fix. Once everything passes,
                    you will see a confirmation dialog — press <strong>Confirm &amp; Submit</strong> to finish.
                </p>
                <div class="mt-3 p-4 rounded-lg bg-amber-50 border border-amber-200">
                    <p class="text-xs text-amber-800 leading-relaxed">
                        <i class="fas fa-lock mr-1"></i>
                        After submission the syllabus is <strong>locked</strong>. The schedule, learning plan, and
                        assessment tasks are final and can no longer be edited. Submit only when you are sure the
                        content is complete and correct.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 6 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">6</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">After submission: review &amp; revisions</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    Your Program Head reviews the submitted syllabus. Two outcomes are possible:
                </p>
                <div class="mt-3 space-y-3">
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-blue-50 border border-blue-100">
                        <i class="fas fa-arrows-rotate text-blue-600 mt-1"></i>
                        <p class="text-xs text-blue-800 leading-relaxed">
                            <strong>Returned for revision</strong> — the syllabus is unlocked with the reviewer's
                            remarks shown on the editor page. Make the requested changes, save, and submit again.
                        </p>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                        <i class="fas fa-circle-check text-emerald-600 mt-1"></i>
                        <p class="text-xs text-emerald-800 leading-relaxed">
                            <strong>Approved</strong> — the syllabus is final. You can still open it to view or print
                            the official copy.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 7 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">7</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">View or print your syllabus</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    Once a syllabus exists (draft or submitted), use the prepared view to see it formatted as the
                    official class syllabus and print it for your records or for distribution.
                </p>
            </div>
        </div>
    </div>

    {{-- FREQUENTLY ASKED QUESTIONS --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-base font-bold text-gray-800 mb-4">Frequently asked questions</h2>
        <div class="divide-y divide-gray-100">
            <div class="py-4">
                <p class="text-sm font-bold text-gray-800">I forgot to submit. Can I still submit my draft?</p>
                <p class="mt-1 text-xs text-gray-600 leading-relaxed">Yes. A draft stays editable until you press Submit and Confirm &amp; Submit. Just complete the remaining parts and submit.</p>
            </div>
            <div class="py-4">
                <p class="text-sm font-bold text-gray-800">My Program Head returned my syllabus. What do I do?</p>
                <p class="mt-1 text-xs text-gray-600 leading-relaxed">Open the syllabus again — it is now editable. Read the revision remarks at the top of the editor, make the requested changes, then save and submit again.</p>
            </div>
            <div class="py-4">
                <p class="text-sm font-bold text-gray-800">Why can't I submit?</p>
                <p class="mt-1 text-xs text-gray-600 leading-relaxed">The grading total is not 100% or empty, an assessment-tool rule is unmet, or at least one teaching week in the learning plan is incomplete. The error notices tell you exactly what to fix.</p>
            </div>
            <div class="py-4">
                <p class="text-sm font-bold text-gray-800">One class serves two programs. Do I need two syllabi?</p>
                <p class="mt-1 text-xs text-gray-600 leading-relaxed">Yes. Open and submit a syllabus for each program badge shown on the class card. They are saved separately.</p>
            </div>
        </div>
    </div>
</div>
@endsection