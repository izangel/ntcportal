<?php

namespace App\Http\Controllers;

// In app/Http/Controllers/HrController.php
use App\Models\Employee;
use App\Models\LeaveCredit;
use App\Models\AcademicYear;
use App\Models\LeaveType; 
use Illuminate\Http\Request;

class HrController extends Controller


{


    /**
     * Display a listing of the semesters.
     */
    public function index()
    {
        // Eager load the academic year relationship
   $leavecredits = LeaveCredit::with('academicYear', 'employee') // Eager load 'employee' too for the view
        
        // 1. Join the employees table to access the 'last_name' column
        ->join('employees', 'leave_credits.employee_id', '=', 'employees.id')
        
        // 2. Add the sorting clause by the employee's last name
        ->orderBy('employees.last_name', 'asc') 
        
        // 3. Keep secondary sorting (optional)
        ->orderBy('academic_year_id', 'desc')
        
        // 4. Select the leave_credits columns to prevent overwriting issues
        ->select('leave_credits.*') 
        
        ->paginate(10);
        
    return view('hr.leave_credits.index', compact('leavecredits'));
    }

    public function create()
    {
        $employees = Employee::all();
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get(); // Get all academic years for dropdown
        return view('hr.leave_credits.create', compact('employees','academicYears'));
    }
   
    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'sick_leave' => 'required|numeric|min:0',
            'vacation_leave' => 'required|numeric|min:0',
            'service_incentive_leave' => 'required|numeric|min:0',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // Check if leave credits already exist for this employee and academic year
        $existingLeaveCredit = LeaveCredit::where('employee_id', $validatedData['employee_id'])
            ->where('academic_year_id', $validatedData['academic_year_id'])
            ->first();

        if ($existingLeaveCredit) {
            return redirect()->back()->with('error', 'Leave credits for this employee and academic year already exist.');
        }

       LeaveCredit::create($validatedData);

        return redirect()->route('leave-credits.index')->with('success', 'Leave credits updated successfully! ✅');
    }


    public function edit(LeaveCredit $leave_credit)
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get(); // Get all academic years for dropdown
        $leavecredit = $leave_credit;
        $employees = Employee::all();
        return view('hr.leave_credits.edit', compact('leavecredit', 'academicYears','employees'));
    }

     public function update(Request $request, LeaveCredit $leave_credit)
    {
          
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'sick_leave' => 'required|numeric|min:0',
            'vacation_leave' => 'required|numeric|min:0',
            'service_incentive_leave' => 'required|numeric|min:0',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);


       

        $leave_credit->update($validatedData);

        return redirect()->route('leave-credits.index')->with('success', 'Leave credit updated successfully.');
    }

    public function destroy(LeaveCredit $leave_credit)
    {
        // You might want to add a check here to prevent deleting if
        // there are associated table.
        
        $leave_credit->delete();

        return redirect()->route('leave-credits.index')->with('success', 'Leave credit deleted successfully.');
    }

    public function showAllEmployeeLeaveCredits()
    {
        $employees = Employee::with('leaveCredits', 'leaveApplications')->get();
        $allCredits = [];

        foreach ($employees as $employee) {
            $leavecredit = $employee->leaveCredits()->first();
            $employeeData = [
                'last_name' => $employee->last_name,
                'first_name' => $employee->first_name,
                'mid_name' => $employee->mid_name,
                'credits' => 'No leave credits set.'
            ];

            if ($leavecredit) {
                $credits = [];
                $leaveTypes = LeaveType::all();
                
                foreach ($leaveTypes as $leaveType) {
                    $key = strtolower(str_replace(' ', '_', $leaveType->name));
                    $credits[$key] = $leavecredit->{$key};
                }
                $employeeData['credits'] = $credits;
            }
            $allCredits[] = $employeeData;
        }

        return view('hr.leave_credits.all', [
            'employeesData' => $allCredits
        ]);
    }

}
