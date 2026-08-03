<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveAnalyticsExport
{
    public function __construct(
        private ?string $startDate,
        private ?string $endDate,
        private array $summary,
        private array $byLeaveType,
        private array $byStatus,
        private array $byMonth,
        private array $byDepartment,
        private array $topEmployees,
    ) {
    }

    public function filename(): string
    {
        $range = '';

        if ($this->startDate && $this->endDate) {
            $range = "_{$this->startDate}_to_{$this->endDate}";
        }

        return 'leave_analytics' . $range . '.xlsx';
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

    private function setTitle($sheet, string $title): void
    {
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $period = 'All time';
        if ($this->startDate && $this->endDate) {
            $period = $this->startDate . ' to ' . $this->endDate;
        }
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'Period: ' . $period);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('6B7280');
    }

    private function writeTable($sheet, array $headers, array $rows, int $startRow): void
    {
        $row = $startRow;
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($this->styleHeader($cell));
        }
        $row++;

        foreach ($rows as $item) {
            foreach (array_values($item) as $col => $value) {
                $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
                $sheet->setCellValue($cell, $value);
            }
            $sheet->getStyle("A{$row}:" . Coordinate::stringFromColumnIndex(count($headers)) . $row)
                ->applyFromArray($this->styleBorder("A{$row}:" . Coordinate::stringFromColumnIndex(count($headers)) . $row));
            $row++;
        }
    }

    public function buildSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $this->buildSummarySheet($spreadsheet->getActiveSheet());
        $this->buildTrendSheet($spreadsheet->createSheet());
        $this->buildByTypeSheet($spreadsheet->createSheet());
        $this->buildByDepartmentSheet($spreadsheet->createSheet());

        return $spreadsheet;
    }

    private function buildSummarySheet($sheet): void
    {
        $sheet->setTitle('Summary');
        $this->setTitle($sheet, 'LEAVE ANALYTICS - SUMMARY');

        $rows = [
            ['Total Applications', $this->summary['applications'] ?? 0],
            ['Total Leave Days', $this->summary['days'] ?? 0],
            ['Approved Applications', $this->summary['approved'] ?? 0],
            ['Approved Days', $this->summary['approved_days'] ?? 0],
            ['Pending', $this->summary['pending'] ?? 0],
            ['Rejected', $this->summary['rejected'] ?? 0],
            ['Cancelled', $this->summary['cancelled'] ?? 0],
            ['Half Days', $this->summary['half_days'] ?? 0],
            ['Employees on Leave', $this->summary['employees'] ?? 0],
        ];

        $row = 4;
        foreach ($rows as [$label, $value]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->setCellValue("B{$row}", $value);
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($this->styleBorder("A{$row}:B{$row}"));
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('B')->setWidth(18);
    }

    private function buildTrendSheet($sheet): void
    {
        $sheet->setTitle('Monthly Trend');
        $this->setTitle($sheet, 'MONTHLY LEAVE TREND');

        $this->writeTable(
            $sheet,
            ['Month', 'Applications', 'Days'],
            $this->byMonth,
            4
        );

        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(14);
    }

    private function buildByTypeSheet($sheet): void
    {
        $sheet->setTitle('By Leave Type');
        $this->setTitle($sheet, 'LEAVE DAYS BY TYPE');

        $this->writeTable(
            $sheet,
            ['Leave Type', 'Applications', 'Days'],
            $this->byLeaveType,
            4
        );

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(14);
    }

    private function buildByDepartmentSheet($sheet): void
    {
        $sheet->setTitle('By Department');
        $this->setTitle($sheet, 'LEAVE DAYS BY DEPARTMENT');

        $this->writeTable(
            $sheet,
            ['Department', 'Applications', 'Days'],
            $this->byDepartment,
            4
        );

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(14);
    }
}
