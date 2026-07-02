<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveCredit;
use App\Models\AcademicYear;
use App\Models\LeaveType; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HrController extends Controller
{
    /**
     * Display a listing of the leave credits.
     */
    public function index()
    {
        $leavecredits = LeaveCredit::with(['academicYear', 'employee'])
            ->join('employees', 'leave_credits.employee_id', '=', 'employees.id')
            ->orderBy('employees.last_name', 'asc') 
            ->orderBy('academic_year_id', 'desc')
            ->select('leave_credits.*') 
            ->paginate(10);
        
        return view('hr.leave_credits.index', compact('leavecredits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::orderBy('last_name', 'asc')->get();
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        
        return view('hr.leave_credits.create', compact('employees', 'academicYears'));
    }
   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate($this->validationRules());

        // Check if unique combination already exists
        $exists = LeaveCredit::where('employee_id', $validatedData['employee_id'])
            ->where('academic_year_id', $validatedData['academic_year_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Leave credits for this employee and academic year already exist.');
        }

        LeaveCredit::create($validatedData);

        return redirect()->route('leave-credits.index')->with('success', 'Leave credits created successfully! ✅');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveCredit $leaveCredit)
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $employees = Employee::orderBy('last_name', 'asc')->get();
        
        return view('hr.leave_credits.edit', compact('leaveCredit', 'academicYears', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveCredit $leaveCredit)
    {
        $validatedData = $request->validate($this->validationRules());

        // Guard against updating to a duplicate pair assigned to another record
        $duplicateExists = LeaveCredit::where('employee_id', $validatedData['employee_id'])
            ->where('academic_year_id', $validatedData['academic_year_id'])
            ->where('id', '!=', $leaveCredit->id)
            ->exists();

        if ($duplicateExists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Another record with this employee and academic year already exists.');
        }

        $leaveCredit->update($validatedData);

        return redirect()->route('leave-credits.index')->with('success', 'Leave credit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveCredit $leaveCredit)
    {
        $leaveCredit->delete();

        return redirect()->route('leave-credits.index')->with('success', 'Leave credit deleted successfully.');
    }

    /**
     * Display all employee leave credits (Fixed N+1 Issue).
     */
    public function showAllEmployeeLeaveCredits()
    {
        // Load relationships efficiently
        $employees = Employee::with(['leaveCredits', 'leaveApplications'])->get();
        $leaveTypes = LeaveType::all(); // Moved OUTSIDE the loop to prevent N+1 queries
        $allCredits = [];

        foreach ($employees as $employee) {
            $leavecredit = $employee->leaveCredits->first(); // Uses already loaded collection rather than running query
            
            $employeeData = [
                'last_name'  => $employee->last_name,
                'first_name' => $employee->first_name,
                'mid_name'   => $employee->mid_name,
                'credits'    => 'No leave credits set.'
            ];

            if ($leavecredit) {
                $credits = [];
                foreach ($leaveTypes as $leaveType) {
                    $key = strtolower(str_replace(' ', '_', $leaveType->name));
                    $credits[$key] = $leavecredit->{$key} ?? 0;
                }
                $employeeData['credits'] = $credits;
            }
            
            $allCredits[] = $employeeData;
        }

        return view('hr.leave_credits.all', ['employeesData' => $allCredits]);
    }

    /**
     * Centralized validation rules to keep code DRY.
     */
    protected function validationRules(): array
    {
        return [
            'employee_id'               => 'required|exists:employees,id',
            'sick_leave'                => 'required|numeric|min:0',
            'vacation_leave'            => 'required|numeric|min:0',
            'service_incentive_leave'   => 'required|numeric|min:0',
            'academic_year_id'          => 'required|exists:academic_years,id',
        ];
    }
}