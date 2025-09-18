<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;

class EmployeeLeaveController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee; // Assuming you have an authenticated user
        $remainingCredits = $employee->getRemainingLeaveCredits();
        $transactions = $employee->leaveApplications()->orderBy('date_filed', 'desc')->get();

        return view('employees.leave.index', compact('remainingCredits', 'transactions'));
    }
}