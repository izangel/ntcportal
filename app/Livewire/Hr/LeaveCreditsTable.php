<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveCredit;
use App\Models\Employee;
use App\Models\AcademicYear;

class LeaveCreditsTable extends Component
{
    use WithPagination;

    // Table Filters & State
    public $search = '';
    public $selectedYear = '';

    // Form Fields & State
    public $isFormOpen = false;
    public $isEditMode = false;
    public $leaveCreditId; // Tracks the record being edited

    public $employee_id = '';
    public $academic_year_id = '';
    public $sick_leave = 0;
    public $vacation_leave = 0;
    public $service_incentive_leave = 0;

    // Reset pagination when searching
    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedYear() { $this->resetPage(); }

    /**
     * Validation Rules
     */
    protected function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'sick_leave' => 'required|numeric|min:0',
            'vacation_leave' => 'required|numeric|min:0',
            'service_incentive_leave' => 'required|numeric|min:0',
        ];
    }

    /**
     * Open form for Creating
     */
    public function create()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->isFormOpen = true;
    }

    /**
     * Open form for Editing
     */
    public function edit($id)
    {
        $this->resetForm();
        $this->leaveCreditId = $id;
        $this->isEditMode = true;

        $record = LeaveCredit::findOrFail($id);
        $this->employee_id = $record->employee_id;
        $this->academic_year_id = $record->academic_year_id;
        $this->sick_leave = $record->sick_leave;
        $this->vacation_leave = $record->vacation_leave;
        $this->service_incentive_leave = $record->service_incentive_leave;

        $this->isFormOpen = true;
    }

    /**
     * Close Form Drawer
     */
    public function closeForm()
    {
        $this->isFormOpen = false;
        $this->resetForm();
    }

    /**
     * Reset Form Input Fields
     */
    private function resetForm()
    {
        $this->employee_id = '';
        $this->academic_year_id = '';
        $this->sick_leave = 0;
        $this->vacation_leave = 0;
        $this->service_incentive_leave = 0;
        $this->leaveCreditId = null;
        $this->resetErrorBag();
    }

    /**
     * Handle Save (Both Store & Update)
     */
    public function save()
    {
        $validatedData = $this->validate();

        if ($this->isEditMode) {
            // Check for duplication excluding current record
            $exists = LeaveCredit::where('employee_id', $this->employee_id)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('id', '!=', $this->leaveCreditId)
                ->exists();

            if ($exists) {
                session()->flash('error', 'Leave credits for this employee and academic year already exist.');
                return;
            }

            $record = LeaveCredit::findOrFail($this->leaveCreditId);
            $record->update($validatedData);
            session()->flash('success', 'Leave credits updated successfully! ✅');
        } else {
            // Check for strict unique allocation
            $exists = LeaveCredit::where('employee_id', $this->employee_id)
                ->where('academic_year_id', $this->academic_year_id)
                ->exists();

            if ($exists) {
                session()->flash('error', 'Leave credits for this employee and academic year already exist.');
                return;
            }

            LeaveCredit::create($validatedData);
            session()->flash('success', 'Leave credits assigned successfully! ✅');
        }

        $this->closeForm();
    }

    /**
     * Handle Delete Actions safely inline
     */
    public function destroy($id)
    {
        LeaveCredit::findOrFail($id)->delete();
        session()->flash('success', 'Leave credit record removed successfully.');
    }

    public function render()
    {
        $query = LeaveCredit::with(['academicYear', 'employee']);

        if (!empty($this->search)) {
            $query->whereHas('employee', function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedYear)) {
            $query->where('academic_year_id', $this->selectedYear);
        }

        return view('livewire.hr.leave-credits-table', [
            'leavecredits' => $query->latest()->paginate(10),
            'academicYears' => AcademicYear::orderBy('start_year', 'desc')->get(),
            'employees' => Employee::orderBy('last_name', 'asc')->get()
        ])->extends('layouts.admin')
            ->section('content');

        
    }
}