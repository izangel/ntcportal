<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Section;
use App\Models\User;
use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon; // Import Carbon for date handling

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

        // ------------------------------------------
        // 1. DATA COMMON TO ALL USERS (Notifications)
        // ------------------------------------------
        $notifications = $user->unreadNotifications;

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
        // 2. STUDENT SPECIFIC DATA (Revised Eager Loading for Schedule)
        // ------------------------------------------
        if ($user->hasRole('student') && $user->student) {
            
            // NOTE: Eager load course for credits/name and section.
            // We now load section.courseBlocks to get scheduling data from the 'course_blocks' table.
            $student = $user->student->load([
                'enrollments.course',
                // Assuming 'section' relation is defined in Enrollment model,
                // and 'courseBlocks' relation is defined in the Section model.
                'enrollments.section.courseBlocks', 
                // 'attendances',
            ]);

            $studentData = [
                'enrolledCourses' => $student->enrollments,
                'currentGPA' => $this->calculateGPA($student->enrollments), 
                'totalCredits' => $student->enrollments->sum('course.credits'),
                
                'recentGrades' => $student->enrollments
                                    ->filter(fn ($e) => !empty($e->grade)) 
                                    ->sortByDesc('updated_at')
                                    ->take(5),
                                    
                // **UPDATED:** Use the new function to fetch the schedule
                'upcomingSchedule' => $this->getUpcomingSchedule($student->enrollments),
                // 'attendanceSummary' => $this->getAttendanceSummary($student->attendances),
            ];
            
        } 
        
        // ------------------------------------------
        // 3. STAFF/ADMIN DATA (Fetched ONLY if not a student)
        // ------------------------------------------
        else { 
            
            // ... (Staff Data Fetching Logic remains unchanged) ...
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
        // 4. RETURN VIEW
        // ------------------------------------------
        $viewData = array_merge(
            compact('user', 'notifications'),
            $staffData,
            $studentData
        );

        return view('dashboard', $viewData);
    }


    // ------------------------------------------
    // 5. HELPER METHODS (STUDENT-SPECIFIC LOGIC)
    // ------------------------------------------

    /**
     * Calculates GPA using the 'grade' column on the Enrollment model.
     * (Logic remains the same as it correctly uses $enrollment->grade and $enrollment->course->credits)
     *
     * @param \Illuminate\Database\Eloquent\Collection $enrollments
     * @return float
     */
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

    /**
     * Converts a score or letter grade to a GPA point.
     *
     * @param string|int $grade
     * @return float
     */
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

    /**
     * Fetches the next 5 upcoming schedule blocks based on the user's enrollments.
     * Uses the course_blocks table structure: section_id, schedule_string, room_name.
     *
     * @param \Illuminate\Database\Eloquent\Collection $enrollments
     * @return \Illuminate\Support\Collection
     */
    protected function getUpcomingSchedule(Collection $enrollments): Collection
    {
        $schedule = collect();
        $now = Carbon::now();
        $limit = 5;

        foreach ($enrollments as $enrollment) {
            // Check if section and courseBlocks exist due to eager loading
            if ($enrollment->section && $enrollment->section->courseBlocks) {
                
                foreach ($enrollment->section->courseBlocks as $block) {
                    // NOTE: The logic here assumes the 'schedule_string' can be parsed 
                    // to determine the exact next class time.
                    
                    // --- Placeholder Logic (You MUST replace this with real schedule parsing) ---
                    // Since 'schedule_string' is a VARCHAR, we can only provide mock data 
                    // or simplified logic until the format is known (e.g., 'MWF 9:00-10:00').
                    $mockTime = rand(1, 4); // Simple random ordering for mock data

                    $schedule->push((object)[
                        'title' => $enrollment->course->name ?? 'N/A',
                        'course_name' => $enrollment->course->code ?? 'N/A',
                        'time_display' => $block->schedule_string . ' in ' . $block->room_name,
                        'sort_order' => $mockTime, // Use a real time for sorting in production
                    ]);
                    // --- END Placeholder Logic ---
                }
            }
        }

        // Return the first 5 sorted items based on simulated "next up" time
        return $schedule->unique('time_display')->sortBy('sort_order')->take($limit);
    }

    /**
     * Placeholder for summarizing recent attendance data.
     *
     * @param \Illuminate\Database\Eloquent\Collection $attendances
     * @return array
     */
    // protected function getAttendanceSummary(Collection $attendances): array
    // {
    //     // Placeholder Data Structure
    //     return [
    //         'total_absences' => 5,
    //         'attendance_rate' => '92%',
    //     ];
    // }
}