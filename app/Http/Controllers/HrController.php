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
        $leavecredits = LeaveCredit::with('academicYear')->orderBy('academic_year_id', 'desc')->paginate(10);
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
        $allRemainingCredits = [];

        foreach ($employees as $employee) {
            $leavecredit = $employee->leaveCredits()->first();
            $employeeData = [
                'last_name' => $employee->last_name,
                'first_name' => $employee->first_name,
                'mid_name' => $employee->mid_name,
                'credits' => 'No leave credits set.'
            ];

            if ($leavecredit) {
                $remainingCredits = [];
                $leaveTypes = LeaveType::all();
                
                foreach ($leaveTypes as $leaveType) {
                    $taken = $employee->leaveApplications()
                        ->where('leave_type_id', $leaveType->id)
                        ->where('approval_status', 'approved_with_pay')
                        ->sum('total_days');

                    $key = strtolower(str_replace(' ', '_', $leaveType->name));
                    $remainingCredits[$key] = $leavecredit->{$key} - $taken;
                }
                $employeeData['credits'] = $remainingCredits;
            }
            $allRemainingCredits[] = $employeeData;
        }

        return view('hr.leave_credits.all', [
            'employeesData' => $allRemainingCredits
        ]);
    }

}
