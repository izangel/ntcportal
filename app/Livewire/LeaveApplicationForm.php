<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\AcademicYear;
use App\Models\LeaveApplication;
use App\Models\User;
use App\Notifications\SubstituteTeacherAssignment;
use App\Notifications\LeaveApplicationSubmittedForHR;
use App\Notifications\LeaveApplicationSubmittedForAH;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveApplicationForm extends Component
{
    // Form Fields
    public $employee_id;
    public $leave_type_id;
    public $reason;
    public $start_date;
    public $end_date;
    
    // Mode-specific fields
    public $approval_status = 'pending';
    public $hr_remarks;
    public $tasks_endorsed;
    public $personnel_to_take_over_id;
    public $classes_data = [];

    // System States
    public $isHrRecordingMode = false;
    public $total_days = 0;
    public $availableCredits = 0;
    public $activeAcademicYear = null;

    // Dropdown Data Collections
    public $employees = [];
    public $leaveTypes = [];
    public $teachers = [];
    public $staffPersonnel = [];
    public $approvalStatuses = ['pending', 'approved_with_pay', 'approved_without_pay', 'rejected'];

    /**
     * Component Lifecycle Mount
     */
    public function mount($isHrRecordingMode = false)
    {
        $this->isHrRecordingMode = $isHrRecordingMode;
        
        // 1. Check for Active School Year immediately
        $this->activeAcademicYear = AcademicYear::where('is_active', true)->first();

        // 2. Hydrate Dropdowns
        $this->leaveTypes = LeaveType::orderBy('name')->get();
        
        if ($this->isHrRecordingMode) {
            $this->employees = Employee::orderBy('last_name')->get();
            $this->teachers = collect();
            $this->staffPersonnel = collect();
        } else {
            // Self-Filing setups
            $this->employee_id = Auth::user()->employee->id;
            $this->updatedEmployeeId($this->employee_id);

            $this->teachers = Employee::where('role', '!=', 'staff')
                ->where('user_id', '!=', Auth::id())
                ->orderBy('last_name')->get();

            $this->staffPersonnel = Employee::where('user_id', '!=', Auth::id())
                ->orderBy('last_name')->get();
        }

        // Initialize one empty row for class schedules if self-filing
        if (!$this->isHrRecordingMode) {
            $this->addClassRow();
        }
    }

    /**
     * Hook triggered when Employee or Leave Type changes to calculate live credits
     */
    public function updatedEmployeeId($value)
    {
        $this->calculateLiveCredits();
    }

    public function updatedLeaveTypeId($value)
    {
        $this->calculateLiveCredits();
    }

    /**
     * Hook triggered when dates change to calculate total workdays dynamically
     */
    public function updatedStartDate() { $this->calculateDays(); }
    public function updatedEndDate() { $this->calculateDays(); }

    public function calculateDays()
    {
        if ($this->start_date && $this->end_date && $this->start_date <= $this->end_date) {
            $this->total_days = $this->calculateWorkDays($this->start_date, $this->end_date);
        } else {
            $this->total_days = 0;
        }
    }

    private function calculateLiveCredits()
    {
        if (!$this->employee_id || !$this->leave_type_id) {
            $this->availableCredits = 0;
            return;
        }

        $employee = Employee::find($this->employee_id);
        $leaveType = LeaveType::find($this->leave_type_id);

        if ($employee && $leaveType) {
            $remainingCredits = $employee->getRemainingLeaveCredits();
            $creditColumn = strtolower(str_replace(' ', '_', $leaveType->name));
            $this->availableCredits = $remainingCredits[$creditColumn] ?? 0;
        }
    }

    /**
     * Livewire Actions to handle multi-row sub structures dynamically
     */
    public function addClassRow()
    {
        $this->classes_data[] = [
            'course_code' => '', 'title' => '', 'day_time_room' => '', 'topics' => '', 'substitute_teacher_id' => ''
        ];
    }

    public function removeClassRow($index)
    {
        unset($this->classes_data[$index]);
        $this->classes_data = array_values($this->classes_data);
    }

    /**
     * Execution Submission Pipeline
     */
    public function submit()
    {
        // 1. Structural Validation rule for Active School Year
        if (!$this->activeAcademicYear) {
            session()->flash('error', 'There is no active school year configured. Leave cannot be recorded.');
            return;
        }

        // 2. Run Dynamic Validations
        $rules = [
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'reason'        => 'required|string|max:1000',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
        ];

        if ($this->isHrRecordingMode) {
            $rules['approval_status'] = 'required|in:pending,approved_with_pay,approved_without_pay,rejected';
            $rules['hr_remarks']      = 'nullable|string|max:500';
        } else {
            $rules['tasks_endorsed'] = 'nullable|string|max:1000';
            $rules['personnel_to_take_over_id'] = 'nullable|exists:employees,id';
            $rules['classes_data.*.course_code'] = 'required|string';
            $rules['classes_data.*.substitute_teacher_id'] = 'required|exists:employees,id';
        }

        $this->validate($rules);

        // 3. Prevent submission if credits are exceeded (Standard Employees only)
        if (!$this->isHrRecordingMode && $this->availableCredits < $this->total_days) {
            session()->flash('error', "Insufficient credits. Available: {$this->availableCredits}, Requested: {$this->total_days}");
            return;
        }

        // 4. Map DB Payload arrays
        $employee = Employee::find($this->employee_id);
        $payload = [
            'employee_id'    => $this->employee_id,
            'leave_type_id'  => $this->leave_type_id,
            'school_year_id' => $this->activeAcademicYear->id, // Structural allocation rule
            'reason'         => $this->reason,
            'start_date'     => $this->start_date,
            'end_date'       => $this->end_date,
            'total_days'     => $this->total_days,
            'date_filed'     => now(),
        ];

        if ($this->isHrRecordingMode) {
            $payload['approval_status'] = $this->approval_status;
            $payload['ah_status']       = 'approved'; 
            $payload['hr_status']       = 'approved';
            $payload['admin_status']    = 'approved';
            
            $hrName = auth()->user()->employee->first_name . ' ' . auth()->user()->employee->last_name;
            $payload['hr_remarks'] = "Recorded by HR ({$hrName}) on " . now()->format('M d, Y') . ($this->hr_remarks ? " - " . $this->hr_remarks : "");
            $payload['hr_approved_by'] = auth()->user()->employee->id;
            $payload['hr_approved_at'] = now();
        } else {
            $payload['approval_status'] = 'pending';
            $payload['hr_status']       = 'pending';
            $payload['admin_status']    = 'pending';
            $payload['ah_status']       = ($employee->role === 'staff') ? 'approved' : 'pending';
            $payload['hr_remarks']      = "Self-filed via Livewire Portal";
            $payload['tasks_endorsed']  = $this->tasks_endorsed;
            $payload['personnel_to_take_over_id'] = $this->personnel_to_take_over_id;
        }

        $leaveApplication = LeaveApplication::create($payload);

        // 5. Run standard sub-transaction models & notifications
        if (!$this->isHrRecordingMode) {
            foreach ($this->classes_data as $classData) {
                if (array_filter($classData)) {
                    $leaveClass = $leaveApplication->classesToMiss()->create($classData);
                    if ($leaveClass->substitute_teacher_id && $leaveClass->substituteTeacher->user) {
                        $leaveClass->substituteTeacher->user->notify(new SubstituteTeacherAssignment($leaveClass, $employee->name));
                    }
                }
            }

            if ($employee->role === 'staff') {
                $hrHeads = User::whereHas('employee', function ($q) { $q->where('role', 'hr'); })->get();
                foreach ($hrHeads as $hr) { $hr->notify(new LeaveApplicationSubmittedForHR($leaveApplication)); }
            } else {
                $academicHeads = User::whereHas('employee', function ($q) { $q->where('role', 'academic_head'); })->get();
                foreach ($academicHeads as $ah) { $ah->notify(new LeaveApplicationSubmittedForAH($leaveApplication)); }
            }
        }

        session()->flash('success', 'Leave Application logged successfully.');
        return redirect()->route($this->isHrRecordingMode ? 'hr.leave_applications.all' : 'leave_applications.index');
    }

    /**
     * Calendar Holiday calculation matrix
     */
    private function calculateWorkDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $totalDays = $start->diffInDays($end) + 1;

        $holidays = [
            '2026-01-01', '2026-01-02', '2026-02-25', '2026-04-02', '2026-04-03', 
            '2026-04-04', '2026-04-09', '2026-05-01', '2026-06-12', '2026-08-21', 
            '2026-08-31', '2026-11-01', '2026-11-02', '2026-11-30', '2026-12-08', 
            '2026-12-24', '2026-12-25', '2026-12-30', '2026-12-31'
        ];

        $holidaysInRange = array_filter($holidays, function ($holiday) use ($start, $end) {
            return Carbon::parse($holiday)->between($start, $end, true);
        });

        return $totalDays - count($holidaysInRange);
    }

    public function render()
    {
        return view('livewire.leave-application-form')->extends('layouts.admin')
            ->section('content');
    }
}