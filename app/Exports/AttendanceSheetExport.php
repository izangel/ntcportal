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

class AttendanceSheetExport
{
    public function __construct(
        private array $rows,
        private array $dates,
        private CourseBlock $block,
        private string $from,
        private string $to,
    ) {
    }

    public function filename(): string
    {
        $courseCode = preg_replace('/[^A-Za-z0-9_-]/', '', $this->block->course?->code ?? 'class');

        return "attendance_sheet_{$courseCode}_{$this->from}_to_{$this->to}.xlsx";
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
        $totalColumns = 3 + count($this->dates) + 5;
        $lastColumn = Coordinate::stringFromColumnIndex($totalColumns);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Sheet');

        $headerFill = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $dateFill = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $borderAll = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ];

        $faculty = $this->block->faculty;
        $facultyName = $faculty ? trim($faculty->last_name . ', ' . $faculty->first_name) : '';

        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A1', 'ATTENDANCE SHEET');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $meta = [
            ['Course', ($this->block->course?->code ?? '') . ' - ' . ($this->block->course?->name ?? '')],
            ['Schedule', $this->block->schedule_string ?? ''],
            ['Room', $this->block->room_name ?? ''],
            ['School Year / Semester', ($this->block->academicYear?->label ?? '') . ' / ' . ($this->block->semester ?? '')],
            ['Faculty', $facultyName],
            ['Date Range', Carbon::parse($this->from)->format('M j, Y') . ' to ' . Carbon::parse($this->to)->format('M j, Y')],
        ];

        $row = 3;
        foreach ($meta as [$label, $value]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->setCellValue("B{$row}", $value);
            $sheet->mergeCells("B{$row}:{$lastColumn}{$row}");
            $row++;
        }

        $row += 1;
        $headerRow = $row;

        $sheet->setCellValue("A{$row}", '#');
        $sheet->getStyle("A{$row}")->applyFromArray($headerFill);
        $sheet->setCellValue("B{$row}", 'Student Name');
        $sheet->getStyle("B{$row}")->applyFromArray($headerFill);
        $sheet->setCellValue("C{$row}", 'ID Number');
        $sheet->getStyle("C{$row}")->applyFromArray($headerFill);

        $col = 4;
        foreach ($this->dates as $date) {
            $cell = Coordinate::stringFromColumnIndex($col) . $row;
            $sheet->setCellValue($cell, Carbon::parse($date)->format('n/j'));
            $sheet->getStyle($cell)->applyFromArray($dateFill);
            $col++;
        }

        foreach (['P', 'L', 'A', 'E', 'Rate %'] as $label) {
            $cell = Coordinate::stringFromColumnIndex($col) . $row;
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->applyFromArray($headerFill);
            $col++;
        }

        $row++;

        $statusColors = [
            'present' => 'DCFCE7',
            'late' => 'FEF3C7',
            'absent' => 'FEE2E2',
            'excused' => 'E5E7EB',
        ];

        $statusLetters = [
            'present' => 'P',
            'late' => 'L',
            'absent' => 'A',
            'excused' => 'E',
        ];

        foreach ($this->rows as $index => $student) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $student['name']);
            $sheet->setCellValue("C{$row}", $student['student_number']);

            $col = 4;
            foreach ($this->dates as $date) {
                $status = $student['per_date'][$date] ?? null;
                $cell = Coordinate::stringFromColumnIndex($col) . $row;
                $sheet->setCellValue($cell, $status ? ($statusLetters[$status] ?? '?') : '');
                if ($status && isset($statusColors[$status])) {
                    $sheet->getStyle($cell)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB($statusColors[$status]);
                }
                $col++;
            }

            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . $row, $student['present']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1) . $row, $student['late']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 2) . $row, $student['absent']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 3) . $row, $student['excused']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 4) . $row, $student['rate'] ?? '');

            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray($borderAll);

            if (($student['rate'] ?? 100) < 80) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FEF3C7');
            }

            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(14);
        $dateCol = 4;
        foreach ($this->dates as $date) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($dateCol))->setWidth(7);
            $dateCol++;
        }
        foreach (['P', 'L', 'A', 'E', 'Rate %'] as $i => $label) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($dateCol + $i))->setWidth(7);
        }
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($dateCol + 4))->setWidth(10);

        $sheet->freezePane("D{$headerRow}");

        return $spreadsheet;
    }
}
