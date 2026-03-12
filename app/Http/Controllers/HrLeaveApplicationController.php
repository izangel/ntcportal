<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveCredit;
use App\Notifications\LeaveApplicationDecision; // We'll create this next
use Illuminate\Notifications\DatabaseNotification;
use App\Notifications\LeaveApplicationSubmittedForAdmin;


class HrLeaveApplicationController extends Controller
{
    // Optional: Middleware to ensure only HR can access
    public function __construct()
    {
        // You'll need to define a gate or policy for 'is_hr'
        // For now, this is a placeholder. You might use Laravel Spatie Permissions.
        // $this->middleware('can:manage_leave_applications');
    }

    /**
     * Show all pending leave applications for HR review.
     */
    public function index()
    {
        // Only show applications that have been approved by Academic Head and are pending HR review
        $pendingApplications = LeaveApplication::where('ah_status', 'approved')
                                                ->where('hr_status', 'pending')
                                                ->with(['employee', 'classesToMiss'])
                                                ->orderBy('created_at', 'asc')
                                                ->get();

        return view('hr.leave_applications.index', compact('pendingApplications'));
    }

    /**
     * Show details of a specific leave application for review.
     */
    public function review(Request $request, LeaveApplication $leaveApplication)
    {
        // Verify signed URL if coming from notification (though index might be direct)
        // if (!URL::hasValidSignature($request)) { // Only if accessed via signed URL
        //     abort(403, 'Invalid or expired review link.');
        // }

        // Load related data
        $leaveApplication->load(['employee', 'classesToMiss.substituteTeacher']);

        return view('hr.leave_applications.review', compact('leaveApplication'));
    }

    /**
     * Process HR decision (approve/reject).
     */
    public function decide(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        // HIERARCHY CHECK: HR can only review if Academic Head has already approved
        if ($leaveApplication->ah_status !== 'approved') {
            return redirect()->back()->with('error', 'This leave application cannot be reviewed by HR yet. Academic Head approval is required first.');
        }

        $decision = $request->input('decision');
        $remarks = $request->input('remarks');
        $approverRole = Auth::user()->employee->role; // Get the approver's role

        // Prevent re-deciding already processed applications
        if ($leaveApplication->hr_status !== 'pending') {
            return redirect()->back()->with('error', 'This leave application has already been processed by HR.');
        }

        // Update application status
        $leaveApplication->hr_status = $decision;
        $leaveApplication->hr_approved_at = Carbon::now();
        $leaveApplication->hr_approved_by = Auth::user()->employee->id; // Assuming HR is logged in
        $leaveApplication->hr_remarks = $remarks;
        $leaveApplication->save();

        // ----------------------------------------------------------------------
        // NEW: Mark the HR Manager's notification for this leave application as read
        $notification = Auth::user()->unreadNotifications()
                            ->where('type', 'App\Notifications\LeaveApplicationSubmittedForHR') // HR notification type
                            ->whereJsonContains('data->leave_application_id', $leaveApplication->id)
                            ->first();

        if ($notification) {
            $notification->markAsRead();
        }
        // ----------------------------------------------------------------------


        // Mark HR notification as read
        if (Auth::user()->unreadNotifications->where('data.leave_application_id', $leaveApplication->id)->first()) {
            Auth::user()->unreadNotifications->where('data.leave_application_id', $leaveApplication->id)->first()->markAsRead();
        }

        
        //Notify Admin
        if ($decision === 'approved') {
           
            // Notify Admin that the application is now approved by HR and ready for their review
            $adminUsers = User::whereHas('employee', function ($query) {
                $query->where('role', 'admin');
            })->get();
            
         
            foreach ($adminUsers as $adminUser) {
                $adminUser->notify(new LeaveApplicationSubmittedForAdmin($leaveApplication));
            }
        } 

        // --- Notify the original employee about the HR decision ---
        $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication, $decision, $approverRole, $remarks));

        return redirect()->route('hr.leave_applications.index')->with('success', "Leave application {$decision} successfully.");
    }

    /**
     * Show form to file retroactive leave applications
     */
    public function showRetroactiveForm()
    {
        $employees = Employee::all();
        $leaveTypes = LeaveType::all();

        return view('hr.leave_applications.create_retroactive', compact('employees', 'leaveTypes'));
    }

    /**
     * Calculate total working days excluding weekends and holidays
     */
    private function calculateWorkDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Keep this list aligned with the main LeaveApplicationController
        $holidays = [
            // Remaining 2025
            '2025-12-30',
            '2025-12-31',
            // 2026
            '2026-01-01',
            '2026-01-02',
            '2026-02-25',
            '2026-04-02',
            '2026-04-03',
            '2026-04-04',
            '2026-04-09',
            '2026-05-01',
            '2026-06-12',
            '2026-08-21',
            '2026-08-31',
            '2026-11-01',
            '2026-11-02',
            '2026-11-30',
            '2026-12-08',
            '2026-12-24',
            '2026-12-25',
            '2026-12-30',
            '2026-12-31',
        ];

        return $start->diffInDaysFiltered(function (Carbon $date) use ($holidays) {
            return $date->isWeekday() && !in_array($date->toDateString(), $holidays);
        }, $end->copy()->addDay());
    }

    /**
     * Store a retroactive leave application filed by HR
     */
    public function storeRetroactive(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|before_or_equal:today',
            'end_date' => 'required|date|before_or_equal:today|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'approval_status' => 'required|in:approved_with_pay,approved_without_pay',
            'hr_remarks' => 'nullable|string|max:500',
        ]);

        // Calculate total working days (exclude weekends/holidays)
        $totalDays = $this->calculateWorkDays($validatedData['start_date'], $validatedData['end_date']);
        if ($totalDays <= 0) {
            return back()
                ->with('error', 'Selected dates contain no working days (weekends/holidays excluded).')
                ->withErrors(['total_days' => 'No working days between the selected dates.'])
                ->withInput();
        }

        // Validate remaining credits are sufficient for the selected leave type
        $leaveCredit = LeaveCredit::where('employee_id', $validatedData['employee_id'])->first();
        $leaveType = LeaveType::find($validatedData['leave_type_id']);
        if (!$leaveCredit || !$leaveType) {
            return back()
                ->with('error', 'Unable to verify remaining leave credits.')
                ->withErrors(['credits' => 'Unable to verify remaining leave credits.'])
                ->withInput();
        }
        $key = strtolower(str_replace(' ', '_', $leaveType->name));
        $currentBalance = $leaveCredit->{$key} ?? 0;
        if ($totalDays > $currentBalance) {
            return back()
                ->with('error', 'Total days exceed the available leave credits for the selected leave type.')
                ->withErrors(['total_days' => 'Insufficient remaining leave credits for the selected leave type.'])
                ->withInput();
        }

        // Create the leave application with retroactive flag
        $leaveApplication = LeaveApplication::create([
            'employee_id' => $validatedData['employee_id'],
            'leave_type_id' => $validatedData['leave_type_id'],
            'reason' => $validatedData['reason'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'total_days' => $totalDays,
            'date_filed' => Carbon::now(),
            
            // Set it as already approved by HR since HR is filing it directly
            'ah_status' => 'approved',
            'hr_status' => 'approved',
            // Retroactive filings bypass Admin; mark as fully approved in the hierarchy
            'admin_status' => 'approved',
            'admin_approved_at' => Carbon::now(),
            'admin_approved_by' => null,
            'approval_status' => $validatedData['approval_status'],
            'ah_approved_at' => Carbon::now(),
            'hr_approved_at' => Carbon::now(),
            'hr_approved_by' => Auth::user()->employee->id,
            'hr_remarks' => $validatedData['hr_remarks'],
        ]);

        // Deduct from employee's leave credits
        $this->deductLeaveCredits($validatedData['employee_id'], $validatedData['leave_type_id'], $totalDays);

        return redirect()->route('hr.leave_applications.index')
            ->with('success', "Retroactive leave application for {$leaveApplication->employee->first_name} {$leaveApplication->employee->last_name} has been created and processed.");
    }

    /**
     * Get employee's leave credits for AJAX request
     */
    public function getEmployeeLeaveCredits($employeeId)
    {
        $employee = Employee::find($employeeId);
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $leaveCredit = $employee->leaveCredits()->first();
        
        if (!$leaveCredit) {
            return response()->json(['credits' => null]);
        }

        // Return credits by leave type ID
        $credits = [];
        $leaveTypes = LeaveType::all();

        foreach ($leaveTypes as $leaveType) {
            $key = strtolower(str_replace(' ', '_', $leaveType->name));
            $credits[$leaveType->id] = $leaveCredit->{$key} ?? 0;
        }

        return response()->json(['credits' => $credits]);
    }

    /**
     * Helper function to deduct leave credits from employee
     */
    private function deductLeaveCredits($employeeId, $leaveTypeId, $totalDays)
    {
        $leaveCredit = LeaveCredit::where('employee_id', $employeeId)->first();
        
        if (!$leaveCredit) {
            return; // No leave credits set up
        }

        $leaveType = LeaveType::find($leaveTypeId);
        
        if (!$leaveType) {
            return;
        }

        $key = strtolower(str_replace(' ', '_', $leaveType->name));
        
        // Deduct the days safely: never add, never go below zero
        $currentBalance = (int) ($leaveCredit->{$key} ?? 0);
        $days = max(0, (int) $totalDays);
        $deduct = min($currentBalance, $days);
        $leaveCredit->{$key} = $currentBalance - $deduct;
        $leaveCredit->save();
    }
}
