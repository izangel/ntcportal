<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Section;
use App\Models\User;
use App\Models\LeaveApplication;
use App\Models\ImportantDate;
use App\Models\CourseBlock;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\SectionStudent;
use App\Models\SystemUpdate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $todayStr = now()->toDateString();
        $notifications = $user->unreadNotifications;

        $recentDates = ImportantDate::with('categories')
            ->where(function($query) use ($todayStr) {
                $query->where('end_date', '>=', $todayStr)
                    ->orWhere(function($q) use ($todayStr) {
                        $q->whereNull('end_date')->where('start_date', '>=', $todayStr);
                    });
            })
            ->orderByRaw("CASE WHEN '$todayStr' BETWEEN start_date AND COALESCE(end_date, start_date) THEN 1 ELSE 2 END ASC")
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        // LOGIC FOR WORK WEEK (MONDAY - FRIDAY)
        $startOfWeek = now()->startOfWeek(); // Carbon default is Monday
        $daysOfWeek = [];
        for ($i = 0; $i < 5; $i++) { // Changed from 7 to 5
            $daysOfWeek[] = $startOfWeek->copy()->addDays($i);
        }

        $rawLeaves = LeaveApplication::with('employee')
            ->whereIn('approval_status', ['pending', 'approved_with_pay', 'approved_without_pay'])
            ->where('start_date', '<=', now()->endOfWeek())
            ->where('end_date', '>=', now()->startOfWeek())
            ->get();

        $leavesByDay = [];
        foreach ($daysOfWeek as $day) {
            $dateStr = $day->toDateString();
            $leavesByDay[$dateStr] = $rawLeaves->filter(function ($leave) use ($day) {
                return $day->between(
                    Carbon::parse($leave->start_date)->startOfDay(),
                    Carbon::parse($leave->end_date)->endOfDay()
                );
            });
        }

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

        if ($user->hasRole('student') && $user->student) {
            $student = $user->student;

            // 1. GET ACTIVE SEMESTER LOGIC (From Monitoring Controller)
            $activeSemester = Semester::where('is_active', 1)->first();
            $semesterName = $activeSemester ? $this->getSemesterName($activeSemester->name) : 'N/A';

            $enrolledCourses = collect([]);
            $upcomingSchedule = collect([]);

            if ($activeSemester) {
                // 2. FIND STUDENT'S SECTION FOR THE ACTIVE SEMESTER
                $studentSection = SectionStudent::where('student_id', $student->id)
                    ->where('academic_year_id', $activeSemester->academic_year_id)
                    ->where('semester', $semesterName)
                    ->first();

                if ($studentSection) {
                    // 3. GET SPECIFIC ENROLLMENTS FOR THIS SEMESTER
                    $enrolledCourseIds = Enrollment::where('student_id', $student->id)
                        ->where('academic_year_id', $activeSemester->academic_year_id)
                        ->where('semester', $semesterName)
                        ->pluck('course_id');

                    // 4. GET COURSE BLOCKS (SCHEDULE)
                    $upcomingSchedule = CourseBlock::with(['course', 'faculty'])
                        ->where('section_id', $studentSection->section_id)
                        ->where('academic_year_id', $activeSemester->academic_year_id)
                        ->where('semester', $semesterName)
                        ->whereIn('course_id', $enrolledCourseIds)
                        ->get();
                }
            }

            $studentData = [
                'enrolledCourses' => $student->enrollments, // Keep all for GPA
                'currentGPA' => $this->calculateGPA($student->enrollments),
                'totalCredits' => $student->enrollments->sum('course.credits'),
                'upcomingSchedule' => $upcomingSchedule,
                'activeSemester' => $activeSemester,
                'semesterName' => $semesterName,
            ];
        }
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

            $staffData['recentUpdates'] = SystemUpdate::latest()->take(5)->get();

            $staffData['myCourses'] = collect();
            if ($user->employee) {
                $currentAY = AcademicYear::orderBy('start_year', 'desc')->first();
                $staffData['myCourses'] = CourseBlock::where('faculty_id', $user->employee->id)
                    ->where('academic_year_id', $currentAY->id ?? null)
                    ->with(['course', 'section.program'])
                    ->get()
                    ->groupBy(fn($item) => $item->course_id . '-' . $item->schedule_string)
                    ->map(fn($group) => [
                        'code'      => $group->first()->course->code,
                        'name'      => $group->first()->course->name,
                        'schedule'  => $group->first()->schedule_string,
                        'sections'  => $group->map(fn($i) => ($i->section->program->name ?? '').'-'.($i->section->name ?? ''))->unique()->implode(', '),
                        'finalized' => $group->first()->finalized
                    ])
                    ->sortBy('schedule')
                    ->values();
            }

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

        $viewData = array_merge(
            compact('user', 'notifications', 'recentDates', 'leavesByDay', 'daysOfWeek'),
            $staffData,
            $studentData
        );

        return view('dashboard', $viewData);
    }

    // Helper to map semester names
    private function getSemesterName($name) {
        return match (true) {
            str_contains($name, 'First')  => '1st',
            str_contains($name, 'Second') => '2nd',
            default                       => 'Summer',
        };
    }

    protected function calculateGPA(Collection $enrollments): float {
        $totalPoints = 0; $totalCredits = 0;
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

    protected function convertToGradePoint($grade): float {
        $score = (int) $grade;
        if ($score >= 93) return 4.0; if ($score >= 90) return 3.7;
        if ($score >= 87) return 3.3; if ($score >= 83) return 3.0;
        if ($score >= 80) return 2.7; if ($score >= 77) return 2.3;
        if ($score >= 73) return 2.0; if ($score >= 70) return 1.7;
        return 0.0;
    }

    protected function getUpcomingSchedule(Collection $enrollments): Collection {
        $schedule = collect();
        foreach ($enrollments as $enrollment) {
            if ($enrollment->section && $enrollment->section->courseBlocks) {
                foreach ($enrollment->section->courseBlocks as $block) {
                    $schedule->push((object)[
                        'title' => $enrollment->course->name ?? 'N/A',
                        'course_name' => $enrollment->course->code ?? 'N/A',
                        'time_display' => $block->schedule_string . ' in ' . $block->room_name,
                        'sort_order' => rand(1, 4),
                    ]);
                }
            }
        }
        return $schedule->unique('time_display')->sortBy('sort_order')->take(5);
    }
}
