<?php

namespace App\Exports;

use App\Models\LeaveApplication;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveApplicationsExport
{
    public function __construct(
        private ?string $startDate = null,
        private ?string $endDate = null,
    ) {
    }

    public function filename(): string
    {
        $fileName = 'Leave_Report';

        if ($this->startDate && $this->endDate) {
            $fileName .= "_{$this->startDate}_to_{$this->endDate}";
        }

        return $fileName . '.xlsx';
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
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leave Applications');

        $applications = $this->query()->get();

        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'LEAVE APPLICATIONS REPORT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $period = 'All time';
        if ($this->startDate && $this->endDate) {
            $period = $this->startDate . ' to ' . $this->endDate;
        }
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Period: ' . $period . ' | Total Records: ' . $applications->count());
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('6B7280');

        $headers = [
            'Date Filed', 'Employee Name', 'Role', 'Leave Type', 'Start Date',
            'End Date', 'Total Days', 'Half Day', 'Status', 'Remarks',
        ];

        $row = 4;
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($this->styleHeader($cell));
        }
        $row++;

        foreach ($applications as $application) {
            $employee = $application->employee;

            $values = [
                $application->date_filed ? $application->date_filed->format('Y-m-d') : '',
                $employee ? trim($employee->last_name . ', ' . $employee->first_name . ($employee->mid_name ? ' ' . $employee->mid_name : '')) : '',
                $employee ? ucwords(str_replace('_', ' ', $employee->role)) : '',
                $application->leaveType->name ?? '',
                $application->start_date ? $application->start_date->format('Y-m-d') : '',
                $application->end_date ? $application->end_date->format('Y-m-d') : '',
                $application->total_days,
                $application->is_half_day ? 'Yes' : 'No',
                str_replace('_', ' ', ucwords($application->approval_status)),
                $application->hr_remarks ?? '',
            ];

            foreach ($values as $col => $value) {
                $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
                $sheet->setCellValue($cell, $value);
            }

            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($this->styleBorder("A{$row}:J{$row}"));
            $row++;
        }

        $widths = [14, 30, 12, 18, 14, 14, 12, 10, 22, 30];
        foreach ($widths as $i => $width) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($width);
        }

        return $spreadsheet;
    }

    private function query()
    {
        $query = LeaveApplication::with(['employee', 'leaveType'])
            ->orderBy('start_date', 'desc');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('start_date', [$this->startDate, $this->endDate]);
        }

        return $query;
    }
}
