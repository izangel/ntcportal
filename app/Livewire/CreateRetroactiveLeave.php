<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\AcademicYear;
use App\Models\LeaveApplication;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CreateRetroactiveLeave extends Component
{
    // Form Inputs
    public $employee_id;
    public $leave_type_id;
    public $start_date;
    public $end_date;
    public $reason;
    public $approval_status = 'approved_with_pay';
    public $hr_remarks = 'Filed by HR';

    // Computed Properties
    public $availableCredits = 0;
    public $total_days = 0;
    public $activeAcademicYear = null;

    public function mount()
    {
        $this->activeAcademicYear = AcademicYear::where('is_active', true)->first();
    }

    // --- REACTIVE HOOKS (Triggered instantly on dropdown/input changes) ---
    public function updatedEmployeeId()
    {
        $this->calculateLiveCredits();
    }

    public function updatedLeaveTypeId()
    {
        $this->calculateLiveCredits();
    }

    public function updatedStartDate()
    {
        $this->calculateDays();
    }

    public function updatedEndDate()
    {
        $this->calculateDays();
    }

    /**
     * Identical calculation engine as View 1
     */
    private function calculateLiveCredits()
    {
        if (!$this->employee_id || !$this->leave_type_id) {
            $this->availableCredits = 0;
            return;
        }

        $employee = Employee::find($this->employee_id);
        $leaveType = LeaveType::find($this->leave_type_id);

        if ($employee && $leaveType) {
            $yearId = $this->activeAcademicYear ? $this->activeAcademicYear->id : null;
            $remainingCredits = $employee->getRemainingLeaveCredits($yearId);

            $creditColumn = strtolower(str_replace(' ', '_', $leaveType->name));
            $this->availableCredits = $remainingCredits[$creditColumn] ?? 0;
        }
    }

    public function calculateDays()
    {
        if ($this->start_date && $this->end_date && $this->start_date <= $this->end_date) {
            $this->total_days = $this->calculateWorkDays($this->start_date, $this->end_date);
        } else {
            $this->total_days = 0;
        }
    }

    private function calculateWorkDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $holidays = [
            '2025-12-30', '2025-12-31',
            '2026-01-01', '2026-01-02', '2026-02-25', '2026-04-02', '2026-04-03',
            '2026-04-04', '2026-04-09', '2026-05-01', '2026-06-12', '2026-08-21',
            '2026-08-31', '2026-11-01', '2026-11-02', '2026-11-30', '2026-12-08',
            '2026-12-24', '2026-12-25', '2026-12-30', '2026-12-31',
        ];

        return $start->diffInDaysFiltered(function (Carbon $date) use ($holidays) {
            return $date->isWeekday() && !in_array($date->toDateString(), $holidays);
        }, $end->copy()->addDay());
    }

    public function submit()
    {
        $today = Carbon::today()->toDateString();

        $this->validate([
            'employee_id'     => 'required|exists:employees,id',
            'leave_type_id'   => 'required|exists:leave_types,id',
            'start_date'      => "required|date|before_or_equal:{$today}",
            'end_date'        => "required|date|before_or_equal:{$today}|after_or_equal:start_date",
            'reason'          => 'required|string|max:1000',
            'approval_status' => 'required|in:approved_with_pay,approved_without_pay',
            'hr_remarks'      => 'nullable|string|max:500',
        ]);

        if ($this->total_days <= 0) {
            session()->flash('error', 'Selected dates contain no working days (weekends/holidays excluded).');
            return;
        }

        if ($this->total_days > $this->availableCredits) {
            session()->flash('error', "Total days ({$this->total_days}) exceed available leave credits ({$this->availableCredits}).");
            return;
        }

        $hrName = Auth::user()->employee ? Auth::user()->employee->first_name . ' ' . Auth::user()->employee->last_name : 'HR';

        LeaveApplication::create([
            'employee_id'       => $this->employee_id,
            'leave_type_id'     => $this->leave_type_id,
            'school_year_id'    => $this->activeAcademicYear ? $this->activeAcademicYear->id : null,
            'reason'            => $this->reason,
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'total_days'        => $this->total_days,
            'date_filed'        => Carbon::now(),
            'ah_status'         => 'approved',
            'hr_status'         => 'approved',
            'admin_status'      => 'approved',
            'approval_status'   => $this->approval_status,
            'ah_approved_at'    => Carbon::now(),
            'hr_approved_at'    => Carbon::now(),
            'admin_approved_at' => Carbon::now(),
            'hr_approved_by'    => Auth::user()->employee ? Auth::user()->employee->id : null,
            'hr_remarks'        => "Recorded by HR ({$hrName})" . ($this->hr_remarks ? " - {$this->hr_remarks}" : ''),
        ]);

        session()->flash('success', 'Retroactive leave application created and processed successfully.');
        return redirect()->route('hr.leave_applications.all');
    }

    public function render()
    {
        return view('livewire.create-retroactive-leave', [
            'employees'  => Employee::orderBy('last_name')->get(),
            'leaveTypes' => LeaveType::orderBy('name')->get(),
        ])->extends('layouts.admin')
            ->section('content');
    }
}