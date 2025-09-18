<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Section;
use App\Models\User;
use App\Models\LeaveApplication; // Import LeaveApplication
use App\Models\Department;       // Import Department (needed for filtering)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveApplicationStatusController extends Controller
{
    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Eager load relations for the authenticated user if they have an employee record
        if ($user && $user->employee) {
            $user->load([
                'employee.leaveApplications.ahApprover',
                'employee.leaveApplications.hrApprover',
                'employee.leaveApplications.adminApprover'
                

            ]);
        }

        // Your existing notification fetching
        $notifications = $user->unreadNotifications;

        // Initialize pendingApplications as an empty collection by default
        $pendingApplications = collect();

        // **NEW/UPDATED LOGIC:** Fetch pending applications ONLY if the user is an Academic Head
        // This relies on the hasRole method you added to the User model, which now checks employees.role.
        if ($user->hasRole('academic_head')) { // <--- Using your custom hasRole method
            $departmentId = $user->employee->department_id ?? null;

            if ($departmentId) {
                $pendingApplications = LeaveApplication::where('ah_status', 'pending')
                                        ->whereHas('employee.department', function ($query) use ($departmentId) {
                                            $query->where('id', $departmentId);
                                        })
                                        ->orderBy('created_at', 'desc')
                                        ->get();
            }
        }

        // Fetch overview statistics (your existing code)
        $totalStudents = Student::count();
        $totalCourses = Course::count();
        $totalEnrollments = Enrollment::count();
        $totalPrograms = Program::count();
        $totalSections = Section::count();
        // This query for teachers might also need to use employees.role for consistency
        $totalTeachers = User::whereHas('employee', function($query) {
                            $query->where('role', 'teacher');
                        })->count();
        $totalUsers = User::count();

        // Fetch some recent activities (optional)
        $recentStudents = Student::latest()->take(5)->get();
        $recentCourses = Course::latest()->take(5)->get();
        $recentEnrollments = Enrollment::latest()->with(['student', 'course'])->take(5)->get();

        return view('leaveapplications', compact(
            'user',
            'notifications',
            'pendingApplications', // Pass this to the view!
            'totalStudents',
            'totalCourses',
            'totalEnrollments',
            'totalPrograms',
            'totalSections',
            'totalTeachers',
            'totalUsers',
            'recentStudents',
            'recentCourses',
            'recentEnrollments'
        ));
    }
}