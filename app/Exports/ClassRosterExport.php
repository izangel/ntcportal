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

class ClassRosterExport
{
    public function __construct(
        private array $students,
        private CourseBlock $block,
    ) {
    }

    public function filename(): string
    {
        $courseCode = preg_replace('/[^A-Za-z0-9_-]/', '', $this->block->course?->code ?? 'class');

        return "class_roster_{$courseCode}.xlsx";
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
        $sheet->setTitle('Class Roster');

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

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'CLASS ROSTER');
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
            $sheet->mergeCells("B{$row}:I{$row}");
            $row++;
        }

        $row += 1;
        $headerRow = $row;
        $headers = ['#', 'Student ID Number', 'Student Name', 'Section', 'Gender', 'Email', 'Present', 'Late', 'Absent', 'Excused', 'Attendance Rate %'];
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
            $sheet->setCellValue("E{$row}", $student['gender'] ?? '');
            $sheet->setCellValue("F{$row}", $student['email'] ?? '');
            $sheet->setCellValue("G{$row}", $student['present']);
            $sheet->setCellValue("H{$row}", $student['late']);
            $sheet->setCellValue("I{$row}", $student['absent']);
            $sheet->setCellValue("J{$row}", $student['excused']);
            $sheet->setCellValue("K{$row}", $student['rate'] ?? '');

            $sheet->getStyle("A{$row}:K{$row}")->applyFromArray($borderAll);

            $row++;
        }

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $i => $col) {
            $widths = [5, 16, 36, 18, 10, 26, 8, 7, 8, 9, 16];
            $sheet->getColumnDimension($col)->setWidth($widths[$i]);
        }

        $sheet->freezePane("A{$headerRow}");

        return $spreadsheet;
    }
}
