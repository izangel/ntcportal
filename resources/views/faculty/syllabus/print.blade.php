<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data->block()->course->code }} - Course Syllabus</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; padding: 24px; font-size: 12px; }
        .print-btn { position: fixed; top: 12px; right: 12px; padding: 8px 16px; background: #1d4ed8; color: #fff; border: 0; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .header { text-align: center; margin-bottom: 8px; }
        .header h1 { font-size: 15px; margin-bottom: 2px; letter-spacing: 1px; }
        .header .course-title { font-size: 13px; margin-bottom: 2px; }
        .header .dept { font-size: 12px; margin-bottom: 2px; }
        .header .sub { font-size: 11px; color: #444; }
        .coursemeta { border: 1px solid #555; margin: 12px 0; }
        .coursemeta table { width: 100%; border-collapse: collapse; }
        .coursemeta td { border: 1px solid #999; padding: 5px 8px; font-size: 11px; vertical-align: top; }
        .coursemeta .label { font-weight: bold; width: 160px; background: #f4f4f4; }
        .coursemeta .full { width: 100%; }
        h2.section { font-size: 13px; margin: 14px 0 4px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1.5px solid #111; padding-bottom: 3px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.data th, table.data td { border: 1px solid #555; padding: 5px 8px; text-align: left; font-size: 11px; vertical-align: top; }
        table.data th { background: #eee; }
        ul { margin: 4px 0 4px 18px; }
        ul li { margin-bottom: 3px; }
        .mapping-cell { text-align: center; font-weight: bold; }
        .peo-po-item { margin-bottom: 4px; }
        .peo-po-item .code { font-weight: bold; }
        .textblock { border: 1px solid #555; min-height: 48px; padding: 8px; margin-top: 4px; white-space: pre-wrap; }
        .plan-table th { font-size: 10px; text-transform: uppercase; }
        .plan-table td { font-size: 10.5px; }
        .footer { margin-top: 30px; display: flex; justify-content: space-between; font-size: 11px; }
        .footer .line { border-top: 1px solid #111; width: 260px; padding-top: 4px; text-align: center; margin-top: 40px; }
        .empty { color: #888; font-style: italic; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
        @page { size: letter; margin: 16mm; }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

    @php
        $block = $data->block();
        $course = $data->block()->course;
        $program = $data->program();
        $faculty = $block->faculty;
        $teacherName = $faculty ? trim($faculty->first_name . ' ' . ($faculty->mid_name ? substr($faculty->mid_name, 0, 1) . '. ' : '') . $faculty->last_name) : '';

        $peos = $data->peos();
        $pos = $data->programOutcomes();
        $clos = $data->courseLearningOutcomes();
        $tasks = $data->assessmentTasks();
        $items = $syllabus?->learningPlanItems ?? collect();
    @endphp

    <div class="header">
        <h1>COURSE SYLLABUS</h1>
        <div class="course-title"><strong>{{ $course->code }}</strong> — {{ $course->name }}</div>
        <div class="dept">{{ $program->name ?? '' }}</div>
        <div class="sub">{{ $faculty ? $teacherName : '' }} | {{ optional($block->academicYear)->label }} | {{ $block->semester }} Semester</div>
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

    <h2 class="section">Program Educational Objectives (PEO)</h2>
    <ul>
        @forelse($peos as $peo)
            <li class="peo-po-item"><span class="code">{{ $peo->code }}</span> — {{ $peo->description }}</li>
        @empty
            <li class="empty">No PEOs configured.</li>
        @endforelse
    </ul>

    <h2 class="section">Program Outcomes (PO)</h2>
    <ul>
        @forelse($pos as $po)
            <li class="peo-po-item"><span class="code">{{ $po->code }}</span> — {{ $po->description }}</li>
        @empty
            <li class="empty">No Program Outcomes configured.</li>
        @endforelse
    </ul>

    <h2 class="section">Course Outcomes (CO), CO-PO Mapping and Assessment Tasks</h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 70px;">CO</th>
                <th>Course Outcome Description</th>
                @foreach($pos as $po)
                    <th class="mapping-cell" style="width: 42px;">{{ $po->code }}</th>
                @endforeach
                <th>Assessment Task</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clos as $clo)
                <tr>
                    <td><strong>{{ $clo->code }}</strong></td>
                    <td>
                        {{ $clo->description }}
                        @if($clo->bloomsTaxonomy)
                            <div style="font-size:10px; color:#666; margin-top:2px;">{{ $clo->bloomsTaxonomy->code }}: {{ $clo->bloomsTaxonomy->level }}</div>
                        @endif
                    </td>
                    @foreach($pos as $po)
                        @php
                            $level = $data->coPoLevel($clo, $po);
                            $label = match ($level) { 'I' => 'I', 'G' => 'E', 'A' => 'D', default => '' };
                        @endphp
                        <td class="mapping-cell">{{ $label }}</td>
                    @endforeach
                    <td>
                        @php
                            $cloTasks = $data->tasksForClo($clo);
                        @endphp
                        @forelse($cloTasks as $task)
                            <div>{{ $task->title }} ({{ $task->weight_percentage }}%)</div>
                        @empty
                            <div class="empty">No assessment mapped</div>
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ 3 + $pos->count() }}" class="empty">No Course Outcomes configured.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="font-size:10px; color:#555; margin-top:3px;">I — Introduced, E — Enabling, D — Demonstrating</div>

    <h2 class="section">Grading System</h2>
    <div class="textblock">{{ $syllabus?->grading_system ?: '—' }}</div>

    <h2 class="section">Textbooks and References</h2>
    <div class="textblock">{{ $syllabus?->textbooks_references ?: '—' }}</div>

    <h2 class="section">Classroom Policies and Procedures</h2>
    <div class="textblock">{{ $syllabus?->classroom_policies ?: '—' }}</div>

    <h2 class="section">Learning Plan</h2>
    <table class="data plan-table">
        <thead>
            <tr>
                <th style="width: 22%;">Learning Outcomes</th>
                <th style="width: 24%;">Topics &amp; Readings</th>
                <th style="width: 12%;">Schedule</th>
                <th style="width: 22%;">Learning Activities</th>
                <th style="width: 20%;">Assessment Tools</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->learning_outcomes }}</td>
                    <td>{{ $item->topics_readings }}</td>
                    <td>{{ $item->schedule }}</td>
                    <td>{{ $item->learning_activities }}</td>
                    <td>{{ $item->assessment_tools }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No learning plan rows entered.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div>
            <p><strong>Prepared by:</strong></p>
            <div class="line">{{ $teacherName }}</div>
        </div>
        <div>
            <p><strong>Noted by:</strong></p>
            <div class="line">Academic Head / Dean</div>
        </div>
    </div>
</body>
</html>
