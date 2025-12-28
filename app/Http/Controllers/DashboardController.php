<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Section;
use App\Models\User;
use App\Models\LeaveApplication;
use App\Models\ImportantDate; // Added ImportantDate Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard based on the authenticated user's role.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $todayStr = now()->toDateString(); // Helper for SQL query

        // ------------------------------------------
        // 1. DATA COMMON TO ALL USERS
        // ------------------------------------------
        $notifications = $user->unreadNotifications;

        // Fetch Top 5 Important Dates (Ongoing first, then Upcoming)
        $recentDates = ImportantDate::with('categories')
            ->where(function($query) use ($todayStr) {
                // Keep events that haven't ended yet
                $query->where('end_date', '>=', $todayStr)
                      ->orWhere(function($q) use ($todayStr) {
                          $q->whereNull('end_date')->where('start_date', '>=', $todayStr);
                      });
            })
            ->orderByRaw("
                CASE 
                    WHEN '$todayStr' BETWEEN start_date AND COALESCE(end_date, start_date) THEN 1
                    ELSE 2
                END ASC
            ")
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        if ($user && $user->employee) {
            $user->load([
                'employee.leaveApplications.ahApprover',
                'employee.leaveApplications.hrApprover',
                'employee.leaveApplications.adminApprover'
            ]);
        }
        
        $staffData = [];
        $studentData = [];
        $pendingApplications = collect(); 

        // ------------------------------------------
        // 2. STUDENT SPECIFIC DATA
        // ------------------------------------------
        if ($user->hasRole('student') && $user->student) {
            
            $student = $user->student->load([
                'enrollments.course',
                'enrollments.section.courseBlocks', 
            ]);

            $studentData = [
                'enrolledCourses' => $student->enrollments,
                'currentGPA' => $this->calculateGPA($student->enrollments), 
                'totalCredits' => $student->enrollments->sum('course.credits'),
                
                'recentGrades' => $student->enrollments
                                    ->filter(fn ($e) => !empty($e->grade)) 
                                    ->sortByDesc('updated_at')
                                    ->take(5),
                                    
                'upcomingSchedule' => $this->getUpcomingSchedule($student->enrollments),
            ];
            
        } 
        
        // ------------------------------------------
        // 3. STAFF/ADMIN DATA
        // ------------------------------------------
        else { 
            $staffData['totalStudents'] = Student::count();
            $staffData['totalCourses'] = Course::count();
            $staffData['totalEnrollments'] = Enrollment::count();
            $staffData['totalPrograms'] = Program::count();
            $staffData['totalSections'] = Section::count();
            $staffData['totalTeachers'] = User::whereHas('employee', function($query) {
                                            $query->where('role', 'teacher');
                                        })->count();
            $staffData['totalUsers'] = User::count();
            $staffData['recentStudents'] = Student::latest()->take(5)->get();
            $staffData['recentCourses'] = Course::latest()->take(5)->get();

            if ($user->hasRole('academic_head')) {
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
            $staffData['pendingApplications'] = $pendingApplications;
        }

        // ------------------------------------------
        // 4. RETURN VIEW (Added recentDates to compact)
        // ------------------------------------------
        $viewData = array_merge(
            compact('user', 'notifications', 'recentDates'),
            $staffData,
            $studentData
        );

        return view('dashboard', $viewData);
    }


    // ------------------------------------------
    // 5. HELPER METHODS
    // ------------------------------------------

    protected function calculateGPA(Collection $enrollments): float
    {
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($enrollments as $enrollment) {
            if ($enrollment->course && !empty($enrollment->grade)) {
                $gradePoint = $this->convertToGradePoint($enrollment->grade); 
                $credits = $enrollment->course->credits;
                $totalPoints += ($gradePoint * $credits);
                $totalCredits += $credits;
            }
        }
        return $totalCredits > 0 ? $totalPoints / $totalCredits : 0.0;
    }

    protected function convertToGradePoint($grade): float
    {
        $score = (int) $grade;

        if ($score >= 93) return 4.0;
        if ($score >= 90) return 3.7;
        if ($score >= 87) return 3.3;
        if ($score >= 83) return 3.0;
        if ($score >= 80) return 2.7;
        if ($score >= 77) return 2.3;
        if ($score >= 73) return 2.0;
        if ($score >= 70) return 1.7;
        return 0.0;
    }

    protected function getUpcomingSchedule(Collection $enrollments): Collection
    {
        $schedule = collect();
        $limit = 5;

        foreach ($enrollments as $enrollment) {
            if ($enrollment->section && $enrollment->section->courseBlocks) {
                foreach ($enrollment->section->courseBlocks as $block) {
                    $mockTime = rand(1, 4); 

                    $schedule->push((object)[
                        'title' => $enrollment->course->name ?? 'N/A',
                        'course_name' => $enrollment->course->code ?? 'N/A',
                        'time_display' => $block->schedule_string . ' in ' . $block->room_name,
                        'sort_order' => $mockTime, 
                    ]);
                }
            }
        }

        return $schedule->unique('time_display')->sortBy('sort_order')->take($limit);
    }
}