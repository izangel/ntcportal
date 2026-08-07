@php
    $block = $data->block();
    $course = $block->course;
    $program = $data->program();
    $faculty = $block->faculty;
    $teacherName = $faculty ? trim($faculty->first_name . ' ' . ($faculty->mid_name ? substr($faculty->mid_name, 0, 1) . '. ' : '') . $faculty->last_name) : '';

    $rawSemester = trim((string) $block->semester);
    $semesterLabel = $rawSemester === '' ? '' : (preg_match('/semester/i', $rawSemester) ? $rawSemester : $rawSemester . ' Semester');

    $peos = $data->peos();
    $pos = $data->programOutcomes();
    $clos = $data->courseLearningOutcomes();
    $tasks = $data->assessmentTasks();
    $items = $syllabus?->learningPlanItems ?? collect();

    $examWeeks = [
        5 => 'FIRST EXAMINATION',
        9 => 'SECOND EXAMINATION',
        14 => 'THIRD EXAMINATION',
        18 => 'FINAL EXAMINATION',
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->code }} - Course Syllabus</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; padding: 24px; font-size: 9.5px; }
        .print-btn { position: fixed; top: 12px; right: 12px; padding: 8px 16px; background: #1d4ed8; color: #fff; border: 0; border-radius: 4px; cursor: pointer; font-size: 13px; }

        /* Letterhead */
        .letterhead { display: flex; justify-content: space-between; align-items: center; gap: 16px; border-bottom: 2px solid #111; padding-bottom: 10px; }
        .school-block { display: flex; align-items: center; gap: 10px; }
        .school-block img { height: 64px; width: auto; }
        .school-name { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .school-tag { font-size: 9.5px; color: #444; margin-top: 2px; }
        .syllabus-box { border: 2px solid #111; padding: 10px 22px; text-align: center; width: 380px; }
        .syllabus-box .box-title { font-size: 15px; font-weight: bold; letter-spacing: 2px; }
        .syllabus-box .box-sub { font-size: 12px; font-weight: bold; margin-top: 4px; }

        /* Vision / Core Values / Mission */
        .vmc { display: table; width: 100%; border-collapse: collapse; margin: 12px 0; }
        .vmc-item { display: table-cell; width: 33.33%; border: 1px solid #555; padding: 6px; font-size: 9.5px; text-align: justify; vertical-align: top; }
        .vmc-label { font-weight: bold; display: block; text-align: center; margin-bottom: 3px; }

        .coursemeta { border: 1px solid #555; margin: 12px 0; }
        .coursemeta table { width: 100%; border-collapse: collapse; }
        .coursemeta td { border: 1px solid #999; padding: 5px 8px; font-size: 9.5px; vertical-align: top; }
        .coursemeta .label { font-weight: bold; width: 160px; background: #f4f4f4; }
        .coursemeta .full { width: 100%; }

        h2.section { font-size: 9.5px; margin: 12px 0 4px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1.5px solid #111; padding-bottom: 3px; }

        /* Two-column paired sections */
        .two-col { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .two-col > tbody > tr > td { vertical-align: top; }
        .two-col .left { width: 50%; padding-right: 8px; }
        .two-col .right { width: 50%; padding-left: 8px; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.data th, table.data td { border: 1px solid #555; padding: 5px 8px; text-align: left; font-size: 9.5px; vertical-align: top; }
        table.data th { background: #eee; }
        ul { margin: 4px 0 4px 18px; }
        ul li { margin-bottom: 3px; }
        .mapping-cell { text-align: center; font-weight: bold; }
        .peo-po-item { margin-bottom: 4px; }
        .peo-po-item .code { font-weight: bold; }
        .textblock { border: 1px solid #555; min-height: 48px; padding: 8px; margin-top: 4px; white-space: pre-wrap; }
        .plan-table th { font-size: 9.5px; text-transform: uppercase; }
        .plan-table td { font-size: 9.5px; }
        .plan-table td.week-cell { text-align: center; vertical-align: middle; font-weight: bold; width: 9%; }
        .plan-table td.exam-cell { text-align: center; vertical-align: middle; font-weight: bold; font-size: 9.5px; letter-spacing: 1px; }
        .footer { margin-top: 30px; display: flex; justify-content: space-between; gap: 16px; font-size: 9.5px; }
        .footer .col { flex: 1; text-align: center; }
        .footer .line { border-top: 1px solid #111; width: 80%; margin: 40px auto 0; padding-top: 4px; text-align: center; }
        .footer .sub { font-size: 8.5px; color: #444; }
        .empty { color: #888; font-style: italic; }
        .revision-note { border: 1px solid #b08a3e; background: #fdf6e3; padding: 8px; margin-top: 4px; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
        @page { size: legal portrait; margin: 16mm; }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

    {{-- Letterhead: school on the left, COURSE SYLLABUS box on the right --}}
    <div class="letterhead">
        <div class="school-block">
            <img src="{{ asset('images/ntc_logo.png') }}" alt="NTC Logo">
            
        </div>
        <div class="syllabus-box">
            <div class="box-title">COURSE SYLLABUS</div>
            <div class="box-sub">{{ $course->code }} {{ $course->name }}</div>
        </div>
    </div>

    {{-- Vision / Core Values / Mission in 3 columns --}}
    <div class="vmc">
        <div class="vmc-item"><span class="vmc-label">VISION</span>NTC will be a hallmark of high-quality education in Southern Mindanao.</div>
        <div class="vmc-item"><span class="vmc-label">MISSION</span>NTC shall provide an academic environment with high standard of instruction, research, and extension to produce globally competitive graduates instilled with excellence and 21st century skills geared towards nation-building.</div>
        <div class="vmc-item"><span class="vmc-label">CORE VALUES</span>Social Responsibility. Innovation, Integrity, Excellence.</div>
    </div>

    <div class="coursemeta">
        <table>
            <tr>
                <td class="label">Course Code</td><td>{{ $course->code }}</td>
                <td class="label">Credit Units</td><td>{{ $course->units ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Course Title</td><td>{{ $course->name }}</td>
                <td class="label">Pre-requisite</td><td>{{ $course->prerequisite ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">Class Schedule</td><td>{{ $block->schedule_string ?: '—' }}</td>
                <td class="label">Section(s)</td><td>{{ $data->sectionLabels() ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">School Year / Semester</td>
                <td colspan="3">{{ optional($block->academicYear)->label }} — {{ $semesterLabel }} | {{ $faculty ? $teacherName : '' }}</td>
            </tr>
        </table>
        @if($course->description)
            <table>
                <tr>
                    <td class="label">Course Description</td>
                    <td>{{ $course->description }}</td>
                </tr>
            </table>
        @endif
    </div>

    {{-- PEO left / PO right --}}
    <table class="two-col">
        <tbody>
            <tr>
                <td class="left">
                    <h2 class="section">Program Educational Objectives (PEO)</h2>
                    @forelse($peos as $peo)
                        <div class="peo-po-item"><span class="code">{{ $peo->code }}</span> — {{ $peo->description }}</div>
                    @empty
                        <div class="empty">No PEOs configured.</div>
                    @endforelse
                </td>
                <td class="right">
                    <h2 class="section">Program Outcomes (PO)</h2>
                    @forelse($pos as $po)
                        <div class="peo-po-item"><span class="code">{{ $po->code }}</span> — {{ $po->description }}</div>
                    @empty
                        <div class="empty">No Program Outcomes configured.</div>
                    @endforelse
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Course Learning Outcomes (CO) --}}
    <h2 class="section">Course Learning Outcomes (CLO)</h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 90px;">CO Code</th>
                <th>Learning Outcome</th>
                <th style="width: 180px;">Bloom Level</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clos as $clo)
                <tr>
                    <td><strong>{{ $clo->code }}</strong></td>
                    <td>{{ $clo->description }}</td>
                    <td>{{ $clo->bloomsTaxonomy?->level ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">No Course Learning Outcomes configured.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- CO-PO Mapping with Assessment Tasks (full width) --}}
    <h2 class="section">CO-PO Mapping with Assessment Tasks</h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 64px;">CO</th>
                @foreach($pos as $po)
                    <th class="mapping-cell" style="width: 30px;">{{ $po->code }}</th>
                @endforeach
                <th>Assessment Task</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clos as $clo)
                <tr>
                    <td><strong>{{ $clo->code }}</strong></td>
                    @foreach($pos as $po)
                        @php
                            $level = $data->coPoLevel($clo, $po);
                            $label = match ($level) { 'I' => 'I', 'G' => 'E', 'A' => 'D', default => '' };
                        @endphp
                        <td class="mapping-cell">{{ $label }}</td>
                    @endforeach
                    <td>
                        @php $cloTasks = $data->tasksForClo($clo); @endphp
                        @forelse($cloTasks as $task)
                            <div>{{ $task->title }} ({{ $task->weight_percentage }}%)</div>
                        @empty
                            <div class="empty">No assessment mapped</div>
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ 2 + $pos->count() }}" class="empty">No Course Outcomes configured.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="font-size:10px; color:#555; margin-top:3px;">I — Introduced, E — Enabling, D — Demonstrating</div>

    {{-- Grading System left / Textbooks + Course Requirements right --}}
    <table class="two-col">
        <tbody>
            <tr>
                <td class="left">
                    <h2 class="section">Grading System</h2>
                    @php
                        $gradingComponents = $syllabus?->gradingComponents ?? collect();
                    @endphp
                    @if($gradingComponents->isNotEmpty())
                        <table class="data">
                            <thead>
                                <tr>
                                    <th style="width: 70%;">Assessment / Requirement</th>
                                    <th style="width: 30%;">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gradingComponents as $component)
                                    <tr>
                                        <td>{{ $component->assessment_type }}</td>
                                        <td>{{ rtrim(rtrim(number_format($component->percentage, 2, '.', ''), '0'), '.') }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @elseif($syllabus?->grading_system)
                        <div class="textblock">{{ $syllabus->grading_system }}</div>
                    @else
                        <div class="textblock">—</div>
                    @endif
                </td>
                <td class="right">
                    <h2 class="section">Textbooks and References</h2>
                    <div class="textblock">{{ $syllabus?->textbooks_references ?: '—' }}</div>

                    <h2 class="section">Course Requirements</h2>
                    <div class="textblock">{{ $syllabus?->course_requirements ?: '—' }}</div>
                </td>
            </tr>
        </tbody>
    </table>

    <h2 class="section">Learning Plan</h2>
    <table class="data plan-table">
        <thead>
            <tr>
                <th style="width: 9%;">Schedule</th>
                <th style="width: 24%;">Learning Outcomes</th>
                <th style="width: 24%;">Topics &amp; Readings</th>
                <th style="width: 24%;">Learning Activities</th>
                <th style="width: 19%;">Assessment Tools</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                @php
                    $week = $index + 1;
                    $exam = $examWeeks[$week] ?? null;
                @endphp
                @if($exam)
                    <tr>
                        <td class="week-cell">{{ $item->schedule }}</td>
                        <td colspan="4" class="exam-cell">{{ $exam }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="week-cell">{{ $item->schedule }}</td>
                        <td class="cell-list"><x-bulleted-list :text="$item->learning_outcomes" /></td>
                        <td class="cell-list"><x-bulleted-list :text="$item->topics_readings" /></td>
                        <td class="cell-list"><x-bulleted-list :text="$item->learning_activities" /></td>
                        <td class="cell-list"><x-bulleted-list :text="$item->assessment_tools" /></td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="5" class="empty">No learning plan rows entered.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="section">Classroom Policies and Procedures</h2>
    <div class="textblock">{{ $syllabus?->classroom_policies ?: '—' }}</div>

    @if($syllabus?->revision_requested_at)
        <h2 class="section">Latest Revision Request</h2>
        <div class="revision-note">
            <p><strong>Requested by:</strong> {{ $syllabus->revision_requested_by_name }} on {{ $syllabus->revision_requested_at->format('M d, Y h:i A') }}</p>
            <p><strong>Remarks:</strong> {{ $syllabus->revision_remarks }}</p>
        </div>
    @endif

    <div class="footer">
        <div class="col">
            <p><strong>Prepared by:</strong></p>
            <div class="line">{{ $teacherName }}</div>
            <div class="sub">Faculty Member</div>
        </div>
        <div class="col">
            <p><strong>Checked &amp; Reviewed by:</strong></p>
            <div class="line">{{ $syllabus?->program_head_reviewed_by_name ?: '' }}</div>
            <div class="sub">Program Head</div>
        </div>
        <div class="col">
            <p><strong>Approved by:</strong></p>
            <div class="line">{{ $syllabus?->academic_head_approved_by_name ?: '' }}</div>
            <div class="sub">Academic Head</div>
        </div>
    </div>
</body>
</html>
