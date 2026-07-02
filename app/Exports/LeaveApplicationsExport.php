<?php

namespace App\Exports;

use App\Models\LeaveApplication;
use Maatwebsite\Excel\Concerns\FromQuery; // Use FromQuery for better performance with filters
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveApplicationsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        $query = LeaveApplication::query()->with(['employee', 'leaveType']);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('start_date', [$this->startDate, $this->endDate]);
        }

        return $query->orderBy('start_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Date Filed', 'Employee Name', 'Leave Type', 'Start Date', 
            'End Date', 'Total Days', 'Status', 'HR Remarks'
        ];
    }

    public function map($leave): array
    {
        return [
            $leave->date_filed->format('Y-m-d'),
            $leave->employee->last_name . ', ' . $leave->employee->first_name,
            $leave->leaveType->name,
            $leave->start_date->format('Y-m-d'),
            $leave->end_date->format('Y-m-d'),
            $leave->total_days,
            str_replace('_', ' ', strtoupper($leave->approval_status)),
            $leave->hr_remarks ?? 'Self-filed',
        ];
    }
}