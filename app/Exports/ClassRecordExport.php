<?php

namespace App\Exports;

use App\Models\CourseBlock;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassRecordExport
{
    public function __construct(
        private array $students,
        private array $grades,
        private array $remarks,
        private CourseBlock $block,
    ) {
    }

    public function filename(): string
    {
        $courseCode = preg_replace('/[^A-Za-z0-9_-]/', '', $this->block->course?->code ?? 'class');

        return "class_record_{$courseCode}.xlsx";
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
        $sheet->setTitle('Class Record');

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

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'CLASS RECORD');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $meta = [
            ['Course', ($this->block->course?->code ?? '') . ' - ' . ($this->block->course?->name ?? '')],
            ['Schedule', $this->block->schedule_string ?? ''],
            ['Room', $this->block->room_name ?? ''],
            ['School Year / Semester', ($this->block->academicYear?->label ?? '') . ' / ' . ($this->block->semester ?? '')],
            ['Faculty', $facultyName],
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
        $headers = ['#', 'Student ID Number', 'Student Name', 'Section', 'Grade', 'Remarks'];
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($headerFill);
        }
        $row++;

        foreach ($this->students as $index => $student) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $student['student_number']);
            $sheet->setCellValue("C{$row}", $student['name']);
            $sheet->setCellValue("D{$row}", $student['section'] ?? '');
            $sheet->setCellValue("E{$row}", $this->grades[$student['id']] ?? '');
            $sheet->setCellValue("F{$row}", $this->remarks[$student['id']] ?? '');

            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($borderAll);

            $row++;
        }

        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $i => $col) {
            $widths = [5, 16, 36, 18, 10, 30];
            $sheet->getColumnDimension($col)->setWidth($widths[$i]);
        }

        $sheet->freezePane("A{$headerRow}");

        return $spreadsheet;
    }
}
