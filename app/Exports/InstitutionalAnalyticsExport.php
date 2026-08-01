<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstitutionalAnalyticsExport
{
    public function __construct(
        private array $summary,
        private array $byProgram,
        private array $byFaculty,
        private array $gradeDistribution,
    ) {
    }

    public function filename(): string
    {
        return 'institutional_analytics.xlsx';
    }

    public function download(): StreamedResponse
    {
        $spreadsheet = $this->buildSpreadsheet();
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $this->filename());
    }

    private function styleHeader($cell)
    {
        return [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
    }

    private function styleBorder($range)
    {
        return [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ];
    }

    public function buildSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $this->buildSummarySheet($spreadsheet->getActiveSheet());
        $this->buildProgramSheet($spreadsheet->createSheet());
        $this->buildFacultySheet($spreadsheet->createSheet());

        return $spreadsheet;
    }

    private function buildSummarySheet($sheet): void
    {
        $sheet->setTitle('Summary');
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'INSTITUTIONAL ANALYTICS - SUMMARY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $rows = [
            ['Total Classes', $this->summary['classes'] ?? 0],
            ['Total Faculty', $this->summary['faculty'] ?? 0],
            ['Total Students', $this->summary['students'] ?? 0],
            ['Attendance Sessions', $this->summary['sessions'] ?? 0],
            ['Present', $this->summary['present'] ?? 0],
            ['Late', $this->summary['late'] ?? 0],
            ['Absent', $this->summary['absent'] ?? 0],
            ['Excused', $this->summary['excused'] ?? 0],
            ['Overall Attendance Rate %', $this->summary['rate'] ?? 'N/A'],
            ['Grades Entered', $this->summary['grades_entered'] ?? 0],
        ];

        $row = 3;
        foreach ($rows as $i => [$label, $value]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->setCellValue("B{$row}", $value);
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($this->styleBorder("A{$row}:B{$row}"));
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(18);
    }

    private function buildProgramSheet($sheet): void
    {
        $sheet->setTitle('By Program');
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'ANALYTICS BY PROGRAM');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $row = 3;
        $headers = ['Program', 'Classes', 'Students', 'Attendance Rate %', 'Grades Entered'];
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($this->styleHeader($cell));
        }
        $row++;

        foreach ($this->byProgram as $item) {
            $sheet->setCellValue("A{$row}", $item['program']);
            $sheet->setCellValue("B{$row}", $item['classes']);
            $sheet->setCellValue("C{$row}", $item['students']);
            $sheet->setCellValue("D{$row}", $item['rate'] ?? '');
            $sheet->setCellValue("E{$row}", $item['grades_entered']);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($this->styleBorder("A{$row}:E{$row}"));
            $row++;
        }

        foreach (['A', 'B', 'C', 'D', 'E'] as $i => $col) {
            $widths = [28, 10, 10, 16, 14];
            $sheet->getColumnDimension($col)->setWidth($widths[$i]);
        }
    }

    private function buildFacultySheet($sheet): void
    {
        $sheet->setTitle('By Faculty');
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'ANALYTICS BY FACULTY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $row = 3;
        $headers = ['Faculty', 'Classes', 'Students', 'Attendance Rate %', 'Grades Entered'];
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($this->styleHeader($cell));
        }
        $row++;

        foreach ($this->byFaculty as $item) {
            $sheet->setCellValue("A{$row}", $item['faculty']);
            $sheet->setCellValue("B{$row}", $item['classes']);
            $sheet->setCellValue("C{$row}", $item['students']);
            $sheet->setCellValue("D{$row}", $item['rate'] ?? '');
            $sheet->setCellValue("E{$row}", $item['grades_entered']);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($this->styleBorder("A{$row}:E{$row}"));
            $row++;
        }

        foreach (['A', 'B', 'C', 'D', 'E'] as $i => $col) {
            $widths = [28, 10, 10, 16, 14];
            $sheet->getColumnDimension($col)->setWidth($widths[$i]);
        }
    }
}
