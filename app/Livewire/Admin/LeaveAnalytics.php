<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Exports\LeaveAnalyticsExport;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveAnalytics extends Component
{
    public $startDate;
    public $endDate;
    public $leaveTypeId = '';
    public $employeeId = '';

    public $leaveTypes = [];
    public $employees = [];

    public $summary = [];
    public $byLeaveType = [];
    public $byStatus = [];
    public $byMonth = [];
    public $byDepartment = [];
    public $topEmployees = [];

    public function mount()
    {
        $this->leaveTypes = LeaveType::orderBy('name')->get();
        $this->employees = Employee::whereHas('leaveApplications')->orderBy('last_name')->get(['id', 'last_name', 'first_name', 'mid_name']);

        $activeYear = AcademicYear::where('is_active', true)->first();

        if ($activeYear) {
            $this->startDate = Carbon::create($activeYear->start_year, 6, 1)->format('Y-m-d');
            $this->endDate = Carbon::create($activeYear->end_year, 5, 31)->format('Y-m-d');
        } else {
            $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
            $this->endDate = Carbon::now()->format('Y-m-d');
        }

        $this->loadAnalytics();
    }

    public function updatedStartDate()
    {
        $this->loadAnalytics();
    }

    public function updatedEndDate()
    {
        $this->loadAnalytics();
    }

    public function updatedLeaveTypeId()
    {
        $this->loadAnalytics();
    }

    public function updatedEmployeeId()
    {
        $this->loadAnalytics();
    }

    private function filteredQuery()
    {
        $query = LeaveApplication::with(['employee', 'employee.department', 'leaveType'])
            ->whereNotNull('employee_id');

        if ($this->startDate) {
            $query->whereDate('start_date', '>=', Carbon::parse($this->startDate));
        }

        if ($this->endDate) {
            $query->whereDate('start_date', '<=', Carbon::parse($this->endDate));
        }

        if ($this->leaveTypeId) {
            $query->where('leave_type_id', $this->leaveTypeId);
        }

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        return $query;
    }

    public function loadAnalytics()
    {
        $this->summary = [];
        $this->byLeaveType = [];
        $this->byStatus = [];
        $this->byMonth = [];
        $this->byDepartment = [];
        $this->topEmployees = [];

        $applications = $this->filteredQuery()->get();

        if ($applications->isEmpty()) {
            return;
        }

        $approved = $applications->whereIn('approval_status', ['approved_with_pay', 'approved_without_pay']);
        $pending = $applications->where('approval_status', 'pending');
        $rejected = $applications->where('approval_status', 'rejected');
        $cancelled = $applications->where('approval_status', 'cancelled');

        $this->summary = [
            'applications' => $applications->count(),
            'days' => round($applications->sum('total_days'), 2),
            'approved' => $approved->count(),
            'approved_days' => round($approved->sum('total_days'), 2),
            'pending' => $pending->count(),
            'rejected' => $rejected->count(),
            'cancelled' => $cancelled->count(),
            'half_days' => $applications->where('is_half_day', true)->count(),
            'employees' => $applications->pluck('employee_id')->unique()->count(),
        ];

        $this->byLeaveType = $applications
            ->groupBy('leave_type_id')
            ->map(function ($group) {
                $leaveType = $group->first()->leaveType;

                return [
                    'name' => $leaveType->name ?? 'Unknown',
                    'applications' => $group->count(),
                    'days' => round($group->sum('total_days'), 2),
                ];
            })
            ->sortByDesc('days')
            ->values()
            ->toArray();

        $statusLabels = [
            'pending' => 'Pending',
            'noted_by_academic_head' => 'Noted by Academic Head',
            'recommended_by_hr' => 'Recommended by HR',
            'approved_with_pay' => 'Approved with Pay',
            'approved_without_pay' => 'Approved without Pay',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
        ];

        $this->byStatus = $applications
            ->groupBy('approval_status')
            ->map(function ($group) use ($statusLabels) {
                return [
                    'status' => $statusLabels[$group->first()->approval_status] ?? ucwords(str_replace('_', ' ', $group->first()->approval_status)),
                    'applications' => $group->count(),
                    'days' => round($group->sum('total_days'), 2),
                ];
            })
            ->sortByDesc('applications')
            ->values()
            ->toArray();

        $this->byMonth = $applications
            ->groupBy(fn ($app) => $app->start_date->format('Y-m'))
            ->map(function ($group) {
                $month = $group->first()->start_date;

                return [
                    'month' => $month->format('M Y'),
                    'applications' => $group->count(),
                    'days' => round($group->sum('total_days'), 2),
                ];
            })
            ->sortKeys()
            ->values()
            ->toArray();

        $this->byDepartment = $applications
            ->groupBy(fn ($app) => $app->employee->department_id)
            ->map(function ($group) {
                $department = $group->first()->employee->department;

                return [
                    'department' => $department->name ?? 'No Department',
                    'applications' => $group->count(),
                    'days' => round($group->sum('total_days'), 2),
                ];
            })
            ->sortByDesc('days')
            ->values()
            ->toArray();

        $this->topEmployees = $applications
            ->groupBy('employee_id')
            ->map(function ($group) {
                $employee = $group->first()->employee;

                return [
                    'name' => $employee ? trim($employee->last_name . ', ' . $employee->first_name . ($employee->mid_name ? ' ' . $employee->mid_name : '')) : 'Unknown',
                    'applications' => $group->count(),
                    'days' => round($group->sum('total_days'), 2),
                ];
            })
            ->sortByDesc('days')
            ->take(10)
            ->values()
            ->toArray();
    }

    public function exportExcel()
    {
        if (empty($this->summary)) {
            session()->flash('error', 'No leave data available to export for the selected period.');
            return;
        }

        return (new LeaveAnalyticsExport(
            $this->startDate,
            $this->endDate,
            $this->summary,
            $this->byLeaveType,
            $this->byStatus,
            $this->byMonth,
            $this->byDepartment,
            $this->topEmployees,
        ))->download();
    }

    public function render()
    {
        return view('livewire.admin.leave-analytics', [
            'leaveTypes' => $this->leaveTypes,
            'employees' => $this->employees,
        ])->extends('layouts.admin')
            ->section('content');
    }
}
