<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;

class EmployeeIndex extends Component
{
    use WithPagination;

    // --- SEARCH & FILTER BINDINGS ---
    public $search = '';
    public $role = '';

    // --- BULK ACTION STATES ---
    public $selectedEmployees = []; // Holds arrays of checked employee IDs
    public $selectAll = false;
    public $bulkRole = ''; // Variable targeting mass role updates

    // --- DROPDOWN PARAMETERS ---
    public $roles = ['teacher', 'staff', 'admin', 'hr', 'academic_head'];

    /**
     * Reset pagination automatically whenever filtering keys change
     */
    public function updatingSearch() { $this->resetPage(); }
    public function updatingRole() { $this->resetPage(); }

    /**
     * Toggles the selection state of all rows on the current page
     */
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedEmployees = $this->getEmployeesProperty()
                ->pluck('id')
                ->map(fn($id) => (string)$id)
                ->toArray();
        } else {
            $this->selectedEmployees = [];
        }
    }

    /**
     * Clear master toggle if any single item is unchecked manually
     */
    public function updatedSelectedEmployees()
    {
        $this->selectAll = false;
    }

    /**
     * Computed Property to safely query items matching filter hooks
     */
    public function getEmployeesProperty()
    {
        $query = Employee::with('user');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->role)) {
            $query->where('role', $this->role);
        }

        return $query->orderBy('last_name')->paginate(10);
    }

    /**
     * ACTION: Apply a mass role swap across all highlighted records
     */
    public function bulkUpdateRole()
    {
        if (empty($this->selectedEmployees)) {
            session()->flash('error', 'Execution blocked: No employee rows were selected.');
            return;
        }

        $this->validate([
            'bulkRole' => 'required|in:teacher,staff,admin,hr,academic_head'
        ]);

        Employee::whereIn('id', $this->selectedEmployees)->update([
            'role' => $this->bulkRole
        ]);

        $count = count($this->selectedEmployees);
        $this->reset(['selectedEmployees', 'selectAll', 'bulkRole']);
        
        session()->flash('success', "Successfully changed the system role configuration for {$count} profiles.");
    }

    /**
     * ACTION: Mass soft-delete all highlighted records
     */
    public function bulkDelete()
    {
        if (empty($this->selectedEmployees)) {
            session()->flash('error', 'Execution blocked: No employee rows were selected.');
            return;
        }

        Employee::whereIn('id', $this->selectedEmployees)->delete();

        $count = count($this->selectedEmployees);
        $this->reset(['selectedEmployees', 'selectAll']);

        session()->flash('success', "Successfully soft-deleted and archived {$count} workforce profiles.");
    }

    public function render()
    {
        return view('livewire.employee-index', [
            'employees' => $this->employees // <-- FIX: Access it cleanly as a magic property
        ]);
    }
}