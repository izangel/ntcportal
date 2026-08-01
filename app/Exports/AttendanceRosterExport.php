<?php

namespace App\Exports;

use App\Models\CourseBlock;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceRosterExport
{
    public function __construct(
        private array $roster,
        private CourseBlock $block,
        private string $date,
    ) {
    }

    public function filename(): string
    {
        $courseCode = preg_replace('/[^A-Za-z0-9_-]/', '', $this->block->course?->code ?? 'class');

        return "attendance_{$courseCode}_{$this->date}.xlsx";
    }

    public function download(): StreamedResponse
    {
        $spreadsheet = $this->buildSpreadsheet();
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $this->filename());
    }

    public function buildSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance');

        $headerFill = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $borderAll = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ];

        $faculty = $this->block->faculty;
        $facultyName = $faculty
            ? trim($faculty->last_name . ', ' . $faculty->first_name)
            : '';

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'ATTENDANCE REPORT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $meta = [
            ['Course', ($this->block->course?->code ?? '') . ' - ' . ($this->block->course?->name ?? '')],
            ['Schedule', $this->block->schedule_string ?? ''],
            ['Room', $this->block->room_name ?? ''],
            ['School Year / Semester', ($this->block->academicYear?->label ?? '') . ' / ' . ($this->block->semester ?? '')],
            ['Faculty', $facultyName],
            ['Date', Carbon::parse($this->date)->format('F j, Y')],
        ];

        $row = 3;
        foreach ($meta as [$label, $value]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->setCellValue("B{$row}", $value);
            $sheet->mergeCells("B{$row}:F{$row}");
            $row++;
        }

        $row += 1;
        $headerRow = $row;
        $headers = ['#', 'Student Name', 'ID Number', 'Status', 'Checked In At', 'Remarks'];
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($headerFill);
        }
        $row++;

        $statusColors = [
            'present' => 'DCFCE7',
            'late' => 'FEF3C7',
            'absent' => 'FEE2E2',
            'excused' => 'E5E7EB',
        ];

        $statusLabels = [
            'present' => 'Present',
            'late' => 'Late',
            'absent' => 'Absent',
            'excused' => 'Excused',
        ];

        foreach ($this->roster as $index => $student) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $student['name']);
            $sheet->setCellValue("C{$row}", $student['student_number']);
            $sheet->setCellValue("D{$row}", $statusLabels[$student['status'] ?? ''] ?? 'Not recorded');
            $sheet->setCellValue("E{$row}", $student['checked_in_at'] ? Carbon::parse($student['checked_in_at'])->format('Y-m-d h:i A') : '');
            $sheet->setCellValue("F{$row}", $student['remarks'] ?? '');

            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($borderAll);

            if (isset($student['status'], $statusColors[$student['status']])) {
                $sheet->getStyle("D{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($statusColors[$student['status']]);
            }

            $row++;
        }

        $present = count(array_filter($this->roster, fn ($r) => $r['status'] === 'present'));
        $late = count(array_filter($this->roster, fn ($r) => $r['status'] === 'late'));
        $absent = count(array_filter($this->roster, fn ($r) => $r['status'] === 'absent'));
        $excused = count(array_filter($this->roster, fn ($r) => $r['status'] === 'excused'));
        $noRecord = count($this->roster) - $present - $late - $absent - $excused;

        $row += 1;
        $sheet->setCellValue("A{$row}", 'SUMMARY');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->setCellValue(
            "B{$row}",
            "Present: {$present}   Late: {$late}   Absent: {$absent}   Excused: {$excused}   No record: {$noRecord}"
        );
        $sheet->mergeCells("B{$row}:F{$row}");

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(38);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(28);

        $sheet->freezePane("A{$headerRow}");

        return $spreadsheet;
    }
}
