@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- HEADER --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700 uppercase tracking-wider">
                <i class="fas fa-list-check mr-1"></i> Teacher's Guide
            </span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Completing Assessment Tasks by CLO &amp; Assessment Item</h1>
        <p class="mt-2 text-sm text-gray-600">
            How to create your assessment tasks, map each assessment item to a Course Learning Outcome (CLO), and
            check that the mapping appears correctly in your syllabus and score entry.
        </p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('faculty.syllabus.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700">
                <i class="fas fa-arrow-left"></i> Back to My Syllabi
            </a>
            <a href="{{ route('faculty.assessment-tasks') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50">
                <i class="fas fa-arrow-up-right-from-square"></i> Open Assessment Setup
            </a>
        </div>
    </div>

    {{-- OVERVIEW --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-base font-bold text-gray-800 mb-3">What you are building</h2>
        <p class="text-sm text-gray-600 leading-relaxed mb-4">
            An <strong>assessment task</strong> is one graded activity (a quiz, an exam, an assignment…).
            An <strong>assessment item</strong> is a single part of that task (for example, "Question 1" or "Lab
            Report 2"), and each item is mapped to <strong>one CLO</strong>. The whole structure looks like this:
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-2 font-bold">Assessment Task</th>
                        <th class="px-4 py-2 font-bold">Assessment Item</th>
                        <th class="px-4 py-2 font-bold">Mapped CLO</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <tr>
                        <td class="px-4 py-2" rowspan="2"><i class="fas fa-clipboard text-indigo-500 mr-1"></i>Long Quiz 1 (Quiz, 10%, 50 marks)</td>
                        <td class="px-4 py-2">Question 1 <span class="text-gray-400">/ 20 marks</span></td>
                        <td class="px-4 py-2"><span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 1</span> Design database schemas…</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2">Question 2 <span class="text-gray-400">/ 30 marks</span></td>
                        <td class="px-4 py-2"><span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 2</span> Implement SQL queries…</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-6 p-4 rounded-lg bg-amber-50 border border-amber-200">
            <p class="text-xs text-amber-900 font-bold mb-2"><i class="fas fa-book-open mr-1"></i>Here's how a whole course could look</p>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 text-[10px] uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2 font-bold w-1/4">Assessment Task</th>
                            <th class="px-3 py-2 font-bold">Assessment Items (marks)</th>
                            <th class="px-3 py-2 font-bold">Mapped CLO</th>
                            <th class="px-3 py-2 font-bold">Weight</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr>
                            <td class="px-3 py-2 align-top font-semibold" rowspan="3"><i class="fas fa-clipboard-check text-indigo-500 mr-1"></i>Quizzes<br><span class="text-[10px] font-normal text-gray-500">Quiz</span></td>
                            <td class="px-3 py-2">Quiz 1 <span class="text-gray-400">· 10 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 1</span></td>
                            <td class="px-3 py-2 font-semibold" rowspan="3">20%</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">Quiz 2 <span class="text-gray-400">· 15 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 2</span></td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">Quiz 3 <span class="text-gray-400">· 15 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 3</span></td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 align-top font-semibold" rowspan="2"><i class="fas fa-file-pen text-indigo-500 mr-1"></i>Prelim Exam<br><span class="text-[10px] font-normal text-gray-500">Exam · 100 marks</span></td>
                            <td class="px-3 py-2">Part I: Multiple Choice <span class="text-gray-400">· 40 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 1</span></td>
                            <td class="px-3 py-2 font-semibold" rowspan="2">20%</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">Part II: Problem Solving <span class="text-gray-400">· 60 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 2</span></td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 align-top font-semibold" rowspan="2"><i class="fas fa-flask text-indigo-500 mr-1"></i>Midterm Lab Exam<br><span class="text-[10px] font-normal text-gray-500">Exam · 100 marks · written + laboratory</span></td>
                            <td class="px-3 py-2">Written Part <span class="text-gray-400">· 50 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 3</span></td>
                            <td class="px-3 py-2 font-semibold" rowspan="2">20%</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">Laboratory / Practical Part <span class="text-gray-400">· 50 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 4</span></td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 align-top font-semibold" rowspan="2"><i class="fas fa-file-pen text-indigo-500 mr-1"></i>Prefinal Exam<br><span class="text-[10px] font-normal text-gray-500">Exam · 100 marks</span></td>
                            <td class="px-3 py-2">Part I: Multiple Choice <span class="text-gray-400">· 40 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 3</span></td>
                            <td class="px-3 py-2 font-semibold" rowspan="2">15%</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">Part II: Essay / Analysis <span class="text-gray-400">· 60 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 4</span></td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 align-top font-semibold" rowspan="3"><i class="fas fa-file-signature text-indigo-500 mr-1"></i>Final Exam<br><span class="text-[10px] font-normal text-gray-500">Exam · 100 marks · written + laboratory</span></td>
                            <td class="px-3 py-2">Part I: Multiple Choice <span class="text-gray-400">· 40 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 1</span></td>
                            <td class="px-3 py-2 font-semibold" rowspan="3">25%</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">Part II: Problem Solving <span class="text-gray-400">· 40 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 2</span></td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">Part III: Laboratory / Practical Part <span class="text-gray-400">· 20 marks</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">CLO 4</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-[11px] text-amber-900">
                <i class="fas fa-lightbulb mr-1"></i>
                Weights total 100%, each task's marks are the sum of its items, and every item maps to one CLO.
                Four exams (Prelim, Midterm, Prefinal, Final) plus a "Quizzes" task (20%). An exam may include a
                <strong>laboratory part</strong> — the Midterm Lab Exam and Final Exam have both written and laboratory
                items. Decide the quiz count from your course plan — usually one quiz per topic — and enter those items
                <em>before</em> submitting, because assessment tasks and items are locked once the syllabus is finalized.
                Plan realistically: a quiz you plan but don't conduct will still pull attainment down.
            </p>
        </div>
        <div class="mt-4 p-4 rounded-lg bg-blue-50 border border-blue-100">
            <p class="text-xs text-blue-800 leading-relaxed">
                <i class="fas fa-lightbulb mr-1"></i>
                The CO-PO mapping section of your syllabus is populated automatically from these CLO mappings, and
                the <strong>Assessment Tasks Setup</strong> box inside the syllabus editor is the same tool — anything
                you save there is used when you submit your syllabus.
            </p>
        </div>
    </div>

    {{-- MANDATORY RULES --}}
    <div class="bg-white rounded-lg shadow-sm border-2 border-indigo-100 p-6 mb-8">
        <div class="flex items-start gap-3 mb-4">
            <span class="flex items-center justify-center w-9 h-9 rounded-full bg-indigo-600 text-white text-sm font-black shrink-0"><i class="fas fa-list-check"></i></span>
            <div>
                <h2 class="text-base font-bold text-gray-900">Mandatory rules — read before you submit</h2>
                <p class="text-xs text-gray-600">The syllabus cannot be submitted until <strong>all</strong> of these rules are satisfied. The syllabus editor shows live pass/fail status for each one.</p>
            </div>
        </div>
        <div class="space-y-3">
            <div class="flex items-start gap-3 rounded-lg border border-rose-200 bg-rose-50 p-3">
                <i class="fas fa-circle-check text-emerald-600 mt-1"></i>
                <div>
                    <p class="text-sm font-bold text-gray-800">Assessment task weights must total 100%</p>
                    <p class="text-xs text-gray-600">The sum of <strong>weight_percentage</strong> across <em>all</em> assessment tasks for the course &amp; batch must equal exactly 100%. Having some non-assessment content doesn't change this — every task's weight counts toward the total.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 rounded-lg border border-rose-200 bg-rose-50 p-3">
                <i class="fas fa-circle-check text-emerald-600 mt-1"></i>
                <div>
                    <p class="text-sm font-bold text-gray-800">Every CLO must be assessed</p>
                    <p class="text-xs text-gray-600">Each <strong>active</strong> course learning outcome must be mapped to a Program Outcome <em>in the syllabus program</em> and covered by <strong>at least one assessment item</strong>. A CLO with no item produces "{CODE} has no mapped assessment item." and blocks submission.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 rounded-lg border border-rose-200 bg-rose-50 p-3">
                <i class="fas fa-circle-check text-emerald-600 mt-1"></i>
                <div>
                    <p class="text-sm font-bold text-gray-800">Every assessment task must map to a CLO</p>
                    <p class="text-xs text-gray-600">Each assessment task must have <strong>at least one item mapped to a CLO</strong>, so every assessment is relevant to a course learning outcome. A task with no items produces "{Task} has no mapped assessment item; every assessment task must map to a CLO." and blocks submission.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                <i class="fas fa-wand-magic-sparkles text-indigo-500 mt-1"></i>
                <div>
                    <p class="text-sm font-bold text-gray-800">Good to know: totals are automatic</p>
                    <p class="text-xs text-gray-600">You never enter a task's total marks — it is computed from the items you map. Planning quizzes? Create <em>one</em> "Quizzes" task (e.g., 20%) and add one item per quiz you plan to give <em>before</em> submitting, since tasks and items lock once the syllabus is finalized. The Grading System groups your tasks' weights by type automatically.</p>
                </div>
            </div>
        </div>
        <p class="mt-3 text-[11px] text-gray-500">
            <i class="fas fa-lightbulb mr-1"></i>
            An item can map to only <strong>one CLO</strong>. If a task assesses several outcomes, add separate items (e.g., "Question 1a", "Question 1b") and map each to its own CLO — that is how every CLO gets covered.
        </p>
    </div>

    {{-- STEP 1 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">1</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">Open Assessment Setup and pick your class</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    Open <strong>Assessment Setup</strong> (or the <strong>Assessment Tasks Setup</strong> section of
                    your syllabus editor). Then choose:
                </p>
                <ul class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm text-gray-600">
                    <li class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-calendar text-indigo-500 mr-1"></i> <strong>Academic Year</strong></li>
                    <li class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-clock text-indigo-500 mr-1"></i> <strong>Semester</strong> (1st / 2nd / Summer)</li>
                    <li class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-users text-indigo-500 mr-1"></i> <strong>Assigned Course Block</strong></li>
                </ul>
                <div class="mt-3 p-4 rounded-lg bg-gray-50 border border-gray-200">
                    <p class="text-xs text-gray-600 leading-relaxed">
                        <i class="fas fa-circle-info text-indigo-500 mr-1"></i>
                        Only your own assigned blocks appear. The banner shows the course, <strong>batch</strong>,
                        semester, and section you are working on — tasks are stored per course and batch, so double-check
                        it matches the term you are preparing.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 2 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">2</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">Create the assessment task</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    In the <strong>Create Assessment Task</strong> box, fill in and press
                    <strong>Create Task</strong>:
                </p>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-600">
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-heading text-indigo-500 mr-1"></i> <strong>Task title</strong> — e.g., "Long Quiz 1", or just "Quizzes" for all quizzes combined</div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-tag text-indigo-500 mr-1"></i> <strong>Type</strong> — Exam, Quiz, Assignment, Project, or Practical</div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-percent text-indigo-500 mr-1"></i> <strong>Weight %</strong> — share of the final grade (0.01–100)</div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200"><i class="fas fa-calculator text-indigo-500 mr-1"></i> <strong>Total marks</strong> — computed automatically from the items you map; nothing to type</div>
                </div>
                <div class="mt-3 p-4 rounded-lg bg-blue-50 border border-blue-100">
                    <p class="text-xs text-blue-800 leading-relaxed">
                            <i class="fas fa-circle-info mr-1"></i>
                            Not sure how many quizzes a task will have? Decide the count from your course plan —
                            usually one quiz per topic — and add one item per planned quiz <em>before</em>
                            submitting, because tasks and items are locked once the syllabus is finalized. To change a task
                            before submission, press <strong>Edit</strong> beside it, adjust the fields, then
                            <strong>Update Task</strong>. <strong>Delete</strong> removes the task and all of its mapped
                            items. Plan realistically — a quiz you plan but don't conduct still pulls attainment down.
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
                <h2 class="text-base font-bold text-gray-800">Map each assessment item to a CLO</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    In the <strong>Map Assessment Item to CLO</strong> box, repeat for every item and press
                    <strong>Map Item to CLO</strong>:
                </p>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    <li class="flex items-start gap-2"><i class="fas fa-check-circle text-emerald-500 mt-1"></i><span>Pick the <strong>assessment task</strong> this item belongs to (from your created tasks).</span></li>
                    <li class="flex items-start gap-2"><i class="fas fa-check-circle text-emerald-500 mt-1"></i><span>Type the <strong>item name</strong> — e.g., "Question 1", "Lab Report 2".</span></li>
                    <li class="flex items-start gap-2"><i class="fas fa-check-circle text-emerald-500 mt-1"></i><span>Select the <strong>CLO</strong> this item assesses (shown as <code class="text-xs bg-gray-100 px-1 rounded">CLO code — description</code>).</span></li>
                    <li class="flex items-start gap-2"><i class="fas fa-check-circle text-emerald-500 mt-1"></i><span>Enter the <strong>maximum marks</strong> this item is worth (must be greater than 0).</span></li>
                </ul>
                <div class="mt-3 p-4 rounded-lg bg-amber-50 border border-amber-200">
                    <p class="text-xs text-amber-800 leading-relaxed">
                        <i class="fas fa-circle-exclamation mr-1"></i>
                        An item can be mapped to only <strong>one CLO</strong>. If a single question covers several
                        outcomes, split it into separate items (e.g., "Question 1a", "Question 1b"), each mapped to its
                        own CLO.
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
                <h2 class="text-base font-bold text-gray-800">Review the Tasks and CLO Items list</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    Under <strong>Tasks and CLO Items</strong>, every task shows its type, weight, and total marks,
                    followed by its mapped items with the CLO each one is linked to. Confirm:
                </p>
                <ul class="mt-3 space-y-1.5 text-xs text-gray-600 list-disc pl-5">
                    <li>Every task has at least one item mapped — tasks with <em>No CLO items mapped yet</em> do not contribute to the CO-PO matrix or attainment.</li>
                    <li>The CLO codes beside each item match what you intend to assess.</li>
                    <li>All planned graded activities for the term are present.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- STEP 5 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-lg shrink-0">5</div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">Save and link to your syllabus</h2>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed">
                    Tasks and items save immediately as you create or map them. When you are inside the syllabus
                    editor, the <strong>Course Outcomes &amp; CO-PO Mapping</strong> table refreshes automatically to
                    include your assessment tasks, and you can continue with the Grading summary, Learning Plan, and
                    submission. Open <strong>Assessment Scores</strong> later to enter student marks per item — the
                    columns are generated from these exact items and CLO mappings.
                </p>
                <div class="mt-3 p-4 rounded-lg bg-blue-50 border border-blue-100">
                    <p class="text-xs text-blue-800 leading-relaxed">
                        <i class="fas fa-lightbulb mr-1"></i>
                        The tasks you create here are the same ones your assessment scores and OBE reports read from,
                        so finish the mapping before submitting your syllabus.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- FAQ --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-base font-bold text-gray-800 mb-4">Frequently asked questions</h2>
        <div class="divide-y divide-gray-100">
            <div class="py-4">
                <p class="text-sm font-bold text-gray-800">Why is my CO-PO mapping still empty?</p>
                <p class="mt-1 text-xs text-gray-600 leading-relaxed">The mapping table is built from your assessment items and their CLOs. Create at least one task with items mapped to CLOs, and the matrix will populate.</p>
            </div>
            <div class="py-4">
                <p class="text-sm font-bold text-gray-800">Can one item point to more than one CLO?</p>
                <p class="mt-1 text-xs text-gray-600 leading-relaxed">No. Each item maps to a single CLO. If a question assesses several outcomes, add separate items (e.g., "Question 1a" and "Question 1b") mapped to the respective CLOs.</p>
            </div>
            <div class="py-4">
                <p class="text-sm font-bold text-gray-800">How do I fix the marks or the CLO I chose?</p>
                <p class="mt-1 text-xs text-gray-600 leading-relaxed">Assessment items cannot be edited directly. Delete the item's task and re-create it with the correct values, or delete the individual item if the option is shown on the Tasks and CLO Items list.</p>
            </div>
            <div class="py-4">
                <p class="text-sm font-bold text-gray-800">Do tasks need to sum to 100%?</p>
                <p class="mt-1 text-xs text-gray-600 leading-relaxed">Yes. Each task's <strong>weight percentage</strong> is its share of the final grade, and all tasks combined must total exactly 100% before the syllabus can be submitted. The Grading System section follows automatically from these weights.</p>
            </div>
            <div class="py-4">
                <p class="text-sm font-bold text-gray-800">I submitted my syllabus. Can I still change the tasks?</p>
                <p class="mt-1 text-xs text-gray-600 leading-relaxed">Once submitted, assessment tasks are locked together with the rest of the syllabus. If you need changes, request a revision from your Program Head.</p>
            </div>
        </div>
    </div>
</div>
@endsection