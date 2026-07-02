<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
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

class LeaveApplicationManager extends Component
{
    use WithPagination;

    // --- VIEW STATE CONTROLLER ---
    public $isCreating = false; 
    public $isHrRecordingMode = false; 
    public $editingApplicationId = null;

    // --- INDEX STATE PROPERTIES ---
    public $filter_leave_type_id = '';
    public $filter_status = '';
    public $filter_academic_year_id = ''; // Added tracking filter state variable
    public $approvalStatuses = ['pending', 'approved_with_pay', 'approved_without_pay', 'rejected'];

    // --- CREATE FORM BINDINGS ---
    public $employee_id;
    public $leave_type_id;
    public $reason;
    public $start_date;
    public $end_date;
    public $approval_status = 'pending'; 
    public $hr_remarks;
    public $tasks_endorsed;
    public $personnel_to_take_over_id;
    public $classes_data = [];

    // --- DYNAMIC CALCULATIONS & MATRICES ---
    public $total_days = 0;
    public $availableCredits = 0;
    public $activeAcademicYear = null;

    /**
     * Lifecycle Initialization Matrix
     */
    public function mount($isHrRecordingMode = false)
    {
        $this->isHrRecordingMode = $isHrRecordingMode;
        $this->activeAcademicYear = AcademicYear::where('is_active', true)->first();
        
        // Default the view grid dashboard layout configuration to filter by active year
        if ($this->activeAcademicYear) {
            $this->filter_academic_year_id = $this->activeAcademicYear->id;
        }
    }

    /**
     * Action to enter form creation view state
     */
    public function enterCreateMode()
    {
        $this->resetFormFields();
        $this->isCreating = true;

        if (!$this->isHrRecordingMode) {
            $this->employee_id = Auth::user()->employee->id;
            $this->calculateLiveCredits();
            $this->addClassRow(); 
        }
    }

    /**
     * Action to discard form changes and return to the index screen
     */
    public function exitCreateMode()
{
    $this->resetErrorBag();
    $this->resetValidation();
    $this->isCreating = false;
    $this->resetFormFields();

    // Force a complete page refresh by redirecting to the route
    return redirect()->route('leave_applications.index'); 
}

    private function resetFormFields()
    {
        $this->reset([
            'employee_id', 'leave_type_id', 'reason', 'start_date', 'end_date',
            'approval_status', 'hr_remarks', 'tasks_endorsed', 
            'personnel_to_take_over_id', 'classes_data', 'total_days', 'availableCredits',
            'editingApplicationId' // <-- ADD THIS TO RESET ARRAY
        ]);
        $this->approval_status = 'pending';
    }

    // --- INDEX INTERACTION HOOKS ---
    public function updatingFilterLeaveTypeId() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterAcademicYearId() { $this->resetPage(); } // Added reset page handler

    public function clearFilters()
    {
        $this->reset(['filter_leave_type_id', 'filter_status']);
        // Default back to active context parameters on filter purge clear triggers
        $this->filter_academic_year_id = $this->activeAcademicYear ? $this->activeAcademicYear->id : '';
        $this->resetPage();
    }

    // --- FORM REACTIVE INTERACTION HOOKS ---
    public function updatedEmployeeId() { $this->calculateLiveCredits(); }
    public function updatedLeaveTypeId() { $this->calculateLiveCredits(); }
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
            // Pass the active academic year ID down to the calculation engine
            $yearId = $this->activeAcademicYear ? $this->activeAcademicYear->id : null;
            $remainingCredits = $employee->getRemainingLeaveCredits($yearId);
            
            $creditColumn = strtolower(str_replace(' ', '_', $leaveType->name));
            $this->availableCredits = $remainingCredits[$creditColumn] ?? 0;
        }
    }

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

    // --- TRANSACTION SUBMISSION ENGINE ---
    public function submit()
    {
        if (!$this->activeAcademicYear) {
            session()->flash('error', 'There is no active school year configured. Applications cannot be processed.');
            return;
        }

        // 1. Core Structural Validation
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
        }

        $this->validate($rules);

        // 2. Business Policy Constraints (Bypassed if filed directly by HR admin)
        if (!$this->isHrRecordingMode) {
            $leaveType = LeaveType::find($this->leave_type_id);
            $typeName = strtolower($leaveType->name);
            
            $today = Carbon::today();
            $startDate = Carbon::parse($this->start_date);

            // POLICY A: Vacation Leave must be filed at least 1 week (7 days) ahead
            if (str_contains($typeName, 'vacation')) {
                // diffInDays returns a signed negative value if start_date is in the past
                $daysNotice = $today->diffInDays($startDate, false);
                
                if ($daysNotice < 7) {
                    session()->flash('error', 'Filing Blocked: Vacation leaves must be filed at least 1 week (7 days) in advance.');
                    return;
                }
            }

            // POLICY B: Sick Leave must be filed within 3 days from the start of absence
            if (str_contains($typeName, 'sick')) {
                if ($startDate->isPast()) {
                    $daysElapsed = $startDate->diffInDays($today, false);
                    
                    if ($daysElapsed > 3) {
                        session()->flash('error', 'Filing Blocked: Sick leaves cannot be recorded more than 3 days after the start date.');
                        return;
                    }
                }
            }

            // 3. Credit Verification Engine
            if ($this->availableCredits < $this->total_days) {
                session()->flash('error', "Insufficient credits. Available: {$this->availableCredits}, Requested: {$this->total_days}");
                return;
            }
        }

        // 4. Save Pipeline Action Execution
        $employee = Employee::find($this->employee_id);
        $payload = [
            'employee_id'    => $this->employee_id,
            'leave_type_id'  => $this->leave_type_id,
            'school_year_id' => $this->activeAcademicYear->id,
            'reason'         => $this->reason,
            'start_date'     => $this->start_date,
            'end_date'       => $this->end_date,
            'total_days'     => $this->total_days,
        ];

        if ($this->isHrRecordingMode) {
            $payload['approval_status'] = $this->approval_status;
            
            // If changing to approved, cascade approved markers dynamically
            if (in_array($this->approval_status, ['approved_with_pay', 'approved_without_pay'])) {
                $payload['ah_status']    = 'approved'; 
                $payload['hr_status']    = 'approved';
                $payload['admin_status'] = 'approved';
            }
            
            $hrName = auth()->user()->employee->first_name . ' ' . auth()->user()->employee->last_name;
            $payload['hr_remarks'] = "Modified by HR ({$hrName}) on " . now()->format('M d, Y') . ($this->hr_remarks ? " - " . $this->hr_remarks : "");
            $payload['hr_approved_by'] = auth()->user()->employee->id;
            $payload['hr_approved_at'] = now();
        } else {
            $payload['tasks_endorsed']  = $this->tasks_endorsed;
            $payload['personnel_to_take_over_id'] = $this->personnel_to_take_over_id;
        }

        // --- DETERMINE PERSISTENCE MODE (EDIT VS NEW) ---
        if ($this->editingApplicationId) {
            $leaveApplication = LeaveApplication::findOrFail($this->editingApplicationId);
            
            // Final validation structural shield wrap
            if ($leaveApplication->approval_status !== 'pending') {
                session()->flash('error', 'Write Protection Error: This row has been updated outside this frame and is locked.');
                return;
            }

            $leaveApplication->update($payload);
            
            // Flush older mapped class schedules to drop dirty overlaps before replacements save down
            $leaveApplication->classesToMiss()->delete();
        } else {
            $payload['date_filed'] = now();
            $payload['approval_status'] = 'pending';
            $payload['hr_status']       = 'pending';
            $payload['admin_status']    = 'pending';
            $payload['ah_status']       = ($employee->role === 'staff') ? 'approved' : 'pending';
            $payload['hr_remarks']      = "Self-filed via Portal Hub";

            $leaveApplication = LeaveApplication::create($payload);
        }

        // Process Classes & Dispatch Notifications
        if (!$this->isHrRecordingMode) {
            foreach ($this->classes_data as $classData) {
                if (array_filter($classData)) {
                    $leaveClass = $leaveApplication->classesToMiss()->create($classData);
                    if ($leaveClass->substitute_teacher_id && $leaveClass->substituteTeacher->user) {
                        $leaveClass->substituteTeacher->user->notify(new SubstituteTeacherAssignment($leaveClass, $employee->name));
                    }
                }
            }

            // Only notify managers on initial creations to stop email noise storms during corrections
            if (!$this->editingApplicationId) {
                if ($employee->role === 'staff') {
                    $hrHeads = User::whereHas('employee', function ($q) { $q->where('role', 'hr'); })->get();
                    foreach ($hrHeads as $hr) { $hr->notify(new LeaveApplicationSubmittedForHR($leaveApplication)); }
                } else {
                    $academicHeads = User::whereHas('employee', function ($q) { $q->where('role', 'academic_head'); })->get();
                    foreach ($academicHeads as $ah) { $ah->notify(new LeaveApplicationSubmittedForAH($leaveApplication)); }
                }
            }
        }

        session()->flash('success', 'Leave Application processed and updated successfully.');
        $this->exitCreateMode();
    }

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

    // --- PROPERTY ACCESSORS ---
   public function getRemainingCreditsProperty()
    {
        $employee = Auth::user()->employee;
        $yearId = $this->activeAcademicYear ? $this->activeAcademicYear->id : null;
        
        return $employee ? $employee->getRemainingLeaveCredits($yearId) : [];
    }

    public function render()
    {
        $employeeId = Auth::user()->employee->id;

        // Base Query Configuration
        $query = LeaveApplication::with(['employee', 'leaveType', 'academicYear']) // eager load relation
            ->orderBy('date_filed', 'desc');

        // Context query filters depending on mode scope
        if (!$this->isHrRecordingMode) {
            $query->where('employee_id', $employeeId);
        }

        // Apply school year filtration
        $query->when($this->filter_academic_year_id, function ($q) {
            return $q->where('school_year_id', $this->filter_academic_year_id);
        });

        // Apply interactive active table structural filter configurations
        $query->when($this->filter_leave_type_id, function ($q) {
            return $q->where('leave_type_id', $this->filter_leave_type_id);
        });

        $query->when($this->filter_status, function ($q) {
            return $q->where('approval_status', $this->filter_status);
        });

        return view('livewire.leave-application-manager', [
            'leaveApplications' => $query->paginate(10),
            'leaveTypes'        => LeaveType::orderBy('name')->get(),
            'academicYears'     => AcademicYear::orderBy('start_year', 'desc')->get(), // Added collection
            'employees'         => Employee::orderBy('last_name')->get(),
            'teachers'          => Employee::where('role', '!=', 'staff')->where('user_id', '!=', Auth::id())->orderBy('last_name')->get(),
            'staffPersonnel'    => Employee::where('user_id', '!=', Auth::id())->orderBy('last_name')->get(),
            'remainingCredits'  => $this->remainingCredits,
        ])->extends('layouts.admin')
            ->section('content');
    }

    /**
     * Action to pull model information back up into form states for modification
     */
    public function edit($id)
    {
        $this->resetFormFields();
        
        $application = LeaveApplication::with('classesToMiss')->findOrFail($id);

        // Security Barrier Check: Guard against manual DOM injection hacks on approved data sets
        if ($application->approval_status !== 'pending') {
            session()->flash('error', 'Action Aborted: This application record has already processed out of pending states.');
            return;
        }

        $this->editingApplicationId = $application->id;
        $this->employee_id = $application->employee_id;
        $this->leave_type_id = $application->leave_type_id;
        $this->start_date = $application->start_date;
        $this->end_date = $application->end_date;
        $this->reason = $application->reason;
        $this->total_days = $application->total_days;
        $this->approval_status = $application->approval_status;
        $this->hr_remarks = $application->hr_remarks;
        $this->tasks_endorsed = $application->tasks_endorsed;
        $this->personnel_to_take_over_id = $application->personnel_to_take_over_id;

        // Map and format missing class metadata lists recursively
        foreach ($application->classesToMiss as $class) {
            $this->classes_data[] = [
                'course_code' => $class->course_code,
                'title' => $class->title,
                'day_time_room' => $class->day_time_room,
                'topics' => $class->topics,
                'substitute_teacher_id' => $class->substitute_teacher_id
            ];
        }

        // Initialize empty baseline if none found
        if (empty($this->classes_data)) {
            $this->addClassRow();
        }

        $this->calculateLiveCredits();
        $this->isCreating = true; // Flips open form panel view layout frame
    }

    public function creditsSummary(\Illuminate\Http\Request $request)
{
    $academicYears = \App\Models\AcademicYear::orderBy('start_year', 'desc')->get();
    $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
    $selectedYearId = $request->get('academic_year_id', $activeYear ? $activeYear->id : null);

    if ($activeYear && $selectedYearId != $activeYear->id) {
        $query = \App\Models\Employee::withTrashed();
    } else {
        $query = \App\Models\Employee::query(); // Hides former workers automatically in current active cycles
    }

    // 1. Core Change: Only display active employees by default
    if ($request->has('show_inactive')) {
        $query->whereIn('employment_status', ['resigned', 'terminated', 'retired']);
    } else {
        $query->where('employment_status', 'active');
    }

    // 2. Search constraint
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('last_name', 'like', '%' . $request->search . '%')
              ->orWhere('first_name', 'like', '%' . $request->search . '%');
        });
    }

    $employees = $query->orderBy('last_name')->paginate(15);

    $summary = $employees->getCollection()->map(function ($employee) use ($selectedYearId) {
        return [
            'id' => $employee->id,
            'name' => "{$employee->last_name}, {$employee->first_name}",
            'role' => $employee->role,
            'status' => $employee->employment_status, // Useful layout marker
            'credits' => $employee->getRemainingLeaveCredits($selectedYearId), 
        ];
    });

    return view('admin.credits_summary', compact('summary', 'employees', 'academicYears', 'selectedYearId'));
}
}