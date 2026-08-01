<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Roster</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; padding: 24px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin-bottom: 2px; }
        .header h2 { font-size: 14px; font-weight: normal; margin-bottom: 2px; }
        .meta { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 14px; border: 1px solid #999; padding: 8px 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #555; padding: 6px 8px; text-align: left; }
        th { background: #eee; }
        .signature { margin-top: 40px; font-size: 12px; }
        .signature .line { border-top: 1px solid #111; width: 260px; padding-top: 4px; text-align: center; }
        .badge { font-weight: bold; }
        .status-present { color: #15803d; }
        .status-late { color: #b45309; }
        .status-absent { color: #b91c1c; }
        .status-excused { color: #4b5563; }
        .print-btn { position: fixed; top: 12px; right: 12px; padding: 8px 16px; background: #1d4ed8; color: #fff; border: 0; border-radius: 4px; cursor: pointer; font-size: 13px; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

    <div class="header">
        <h1>ATTENDANCE ROSTER</h1>
        <h2>{{ $block->course->code ?? '' }} - {{ $block->course->name ?? '' }}</h2>
        <h2>{{ $block->schedule_string }} | {{ $block->room_name ?: 'Room: TBA' }}</h2>
        @if($block->academicYear)
            <h2>AY {{ $block->academicYear->start_year }} - {{ $block->academicYear->end_year }} | {{ $block->semester }}</h2>
        @endif
    </div>

    <div class="meta">
        <span><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</span>
        <span><strong>Instructor:</strong> {{ $block->faculty ? trim($block->faculty->first_name . ' ' . $block->faculty->last_name) : 'TBA' }}</span>
        <span><strong>Total Students:</strong> {{ $roster->count() }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="width: 60px;">ID No.</th>
                <th>Student Name</th>
                <th style="width: 90px;">Status</th>
                <th style="width: 100px;">Check-in Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roster as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student['student_number'] }}</td>
                    <td>{{ $student['name'] }}</td>
                    <td>
                        @if($student['status'])
                            <span class="badge status-{{ $student['status'] }}">{{ strtoupper($student['status']) }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $student['checked_in_at'] ? \Carbon\Carbon::parse($student['checked_in_at'])->format('h:i A') : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center; color:#666; padding:16px;">No students enrolled in this class.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature" style="display: flex; justify-content: space-between;">
        <div>
            <p><strong>Instructor's Signature</strong></p>
            <div class="line" style="margin-top: 48px;"></div>
        </div>
        <div>
            <p><strong>Verified by / Date</strong></p>
            <div class="line" style="margin-top: 48px;"></div>
        </div>
    </div>
</body>
</html>
