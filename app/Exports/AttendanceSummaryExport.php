<?php

namespace App\Exports;

use App\Models\CourseBlock;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceSummaryExport
{
    public function __construct(
        private array $rows,
        private CourseBlock $block,
        private float $threshold = 80.0,
    ) {
    }

    public function filename(): string
    {
        $courseCode = preg_replace('/[^A-Za-z0-9_-]/', '', $this->block->course?->code ?? 'class');

        return "attendance_summary_{$courseCode}.xlsx";
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
        $sheet->setTitle('Attendance Summary');

        $headerFill = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $borderAll = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ];

        $faculty = $this->block->faculty;
        $facultyName = $faculty ? trim($faculty->last_name . ', ' . $faculty->first_name) : '';

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'ATTENDANCE SUMMARY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $meta = [
            ['Course', ($this->block->course?->code ?? '') . ' - ' . ($this->block->course?->name ?? '')],
            ['Schedule', $this->block->schedule_string ?? ''],
            ['Room', $this->block->room_name ?? ''],
            ['School Year / Semester', ($this->block->academicYear?->label ?? '') . ' / ' . ($this->block->semester ?? '')],
            ['Faculty', $facultyName],
            ['Attendance Rate Threshold', "Below {$this->threshold}% flagged"],
        ];

        $row = 3;
        foreach ($meta as [$label, $value]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->setCellValue("B{$row}", $value);
            $sheet->mergeCells("B{$row}:H{$row}");
            $row++;
        }

        $row += 1;
        $headerRow = $row;
        $headers = ['#', 'Student Name', 'ID Number', 'Present', 'Late', 'Absent', 'Excused', 'Attendance Rate %'];
        foreach ($headers as $col => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($headerFill);
        }
        $row++;

        foreach ($this->rows as $index => $student) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $student['name']);
            $sheet->setCellValue("C{$row}", $student['student_number']);
            $sheet->setCellValue("D{$row}", $student['present']);
            $sheet->setCellValue("E{$row}", $student['late']);
            $sheet->setCellValue("F{$row}", $student['absent']);
            $sheet->setCellValue("G{$row}", $student['excused']);
            $sheet->setCellValue("H{$row}", $student['rate'] ?? '');

            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($borderAll);

            if (($student['rate'] ?? 100) < $this->threshold) {
                $sheet->getStyle("A{$row}:H{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FEF3C7');
            }

            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(38);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(9);
        $sheet->getColumnDimension('E')->setWidth(7);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(9);
        $sheet->getColumnDimension('H')->setWidth(18);

        $sheet->freezePane("A{$headerRow}");

        return $spreadsheet;
    }
}
