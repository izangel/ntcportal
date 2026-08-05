<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LeaveApplication;
use App\Models\ImportantDate;
use App\Models\CourseBlock;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\SectionStudent;
use App\Models\SystemUpdate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

      
        $todayStr = now()->toDateString(); 
        $notifications = $user->unreadNotifications;

        // 1. Get Active Semester early to use for both Students and Staff
        $activeSemester = Semester::where('is_active', 1)->first();
        $semesterName = $activeSemester ? $this->getSemesterName($activeSemester->name) : 'N/A';

        
       

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

        $startOfWeek = now()->startOfWeek();
        $daysOfWeek = [];
        for ($i = 0; $i < 5; $i++) {
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
            $enrolledCourses = collect([]);
            $upcomingSchedule = collect([]);

            if ($activeSemester) {
                $studentSection = SectionStudent::where('student_id', $student->id)
                    ->where('academic_year_id', $activeSemester->academic_year_id)
                    ->where('semester', $semesterName)
                    ->first();

                if ($studentSection) {
                    $enrolledCourseIds = Enrollment::where('student_id', $student->id)
                        ->where('academic_year_id', $activeSemester->academic_year_id)
                        ->where('semester', $semesterName)
                        ->pluck('course_id');

                    $upcomingSchedule = CourseBlock::with(['course', 'faculty'])
                        ->where('section_id', $studentSection->section_id)
                        ->where('academic_year_id', $activeSemester->academic_year_id)
                        ->where('semester', $semesterName)
                        ->whereIn('course_id', $enrolledCourseIds)
                        ->get();
                }

               
            }

            $studentData = [
                'enrolledCourses' => $student->enrollments,
                'currentGPA' => $this->calculateGPA($student->enrollments), 
                'totalCredits' => $student->enrollments->sum('course.credits'),
                'upcomingSchedule' => $upcomingSchedule,
                'activeSemester' => $activeSemester,
                'semesterName' => $semesterName,
            ];
        }
        else { 

          
            $staffData['enrollmentTotals'] = [
                'enrollments' => (int) DB::table('student_courseblock')->count(),
                'students'    => (int) DB::table('student_courseblock')->distinct()->count('student_id'),
                'classes'     => (int) DB::table('student_courseblock')->distinct()->count('course_block_id'),
                'programs'    => (int) DB::table('student_courseblock as sc')
                                    ->join('course_blocks as cb', 'sc.course_block_id', '=', 'cb.id')
                                    ->join('sections as sec', 'cb.section_id', '=', 'sec.id')
                                    ->distinct()->count('sec.program_id'),
            ];
            $enrollmentTotal = $staffData['enrollmentTotals']['enrollments'];

            $enrollmentsByAY = DB::table('student_courseblock as sc')
                ->join('course_blocks as cb', 'sc.course_block_id', '=', 'cb.id')
                ->join('academic_years as ay', 'cb.academic_year_id', '=', 'ay.id')
                ->select('ay.id as ay_id', 'ay.start_year', 'ay.end_year', 'cb.semester', DB::raw('count(*) as total'))
                ->groupBy('ay.id', 'ay.start_year', 'ay.end_year', 'cb.semester')
                ->orderBy('ay.start_year')
                ->orderBy('cb.semester')
                ->get()
                ->groupBy('ay_id')
                ->map(fn($rows) => [
                    'label'     => $rows->first()->start_year . '-' . $rows->first()->end_year,
                    'semesters' => $rows->map(function ($r) {
                        $raw = $r->semester;
                        $label = str_contains($raw, 'Second') || $raw === '2nd' ? '2nd'
                            : (str_contains($raw, 'First') || $raw === '1st' ? '1st' : 'Summer');
                        return ['semester' => $label, 'raw' => $raw, 'total' => (int) $r->total];
                    })->values(),
                    'total'     => (int) $rows->sum('total'),
                ])
                ->values();

            $staffData['enrollmentsByAY'] = $enrollmentsByAY;
            $staffData['enrollmentMaxAY'] = $enrollmentsByAY->max('total') ?: 1;

            $enrollmentsByProgram = DB::table('student_courseblock as sc')
                ->join('course_blocks as cb', 'sc.course_block_id', '=', 'cb.id')
                ->join('sections as sec', 'cb.section_id', '=', 'sec.id')
                ->join('programs as p', 'sec.program_id', '=', 'p.id')
                ->select(
                    'p.id as program_id',
                    'p.name',
                    DB::raw('count(*) as enrollments'),
                    DB::raw('count(distinct sc.student_id) as students'),
                    DB::raw('count(distinct cb.course_id) as courses'),
                    DB::raw('count(distinct cb.section_id) as sections')
                )
                ->groupBy('p.id', 'p.name')
                ->orderByDesc('enrollments')
                ->get()
                ->map(fn($row) => [
                    'program_id'  => $row->program_id,
                    'name'        => $row->name,
                    'enrollments' => (int) $row->enrollments,
                    'students'    => (int) $row->students,
                    'courses'     => (int) $row->courses,
                    'sections'    => (int) $row->sections,
                    'share'       => $enrollmentTotal > 0 ? round($row->enrollments / $enrollmentTotal * 100, 1) : 0,
                ]);

            $staffData['enrollmentsByProgram'] = $enrollmentsByProgram;
            $staffData['enrollmentMaxProgram'] = $enrollmentsByProgram->max('enrollments') ?: 1;

            $facultyLoad = DB::table('course_blocks as cb')
                ->join('employees as e', 'cb.faculty_id', '=', 'e.id')
                ->leftJoin('student_courseblock as sc', 'sc.course_block_id', '=', 'cb.id')
                ->select('e.id', 'e.first_name', 'e.last_name', 'e.mid_name', DB::raw('count(distinct cb.id) as classes'), DB::raw('count(distinct sc.student_id) as students'))
                ->groupBy('e.id', 'e.first_name', 'e.last_name', 'e.mid_name')
                ->orderByDesc('classes')
                ->take(8)
                ->get()
                ->map(fn($row) => [
                    'id'       => $row->id,
                    'name'     => trim($row->first_name . ' ' . ($row->mid_name ? substr($row->mid_name, 0, 1) . '.' : '') . ' ' . $row->last_name),
                    'classes'  => (int) $row->classes,
                    'students' => (int) $row->students,
                ]);

            $staffData['facultyLoad'] = $facultyLoad;
            $staffData['facultyMaxClasses'] = $facultyLoad->max('classes') ?: 1;

            $staffData['enrollmentMaxStudents'] = $enrollmentsByProgram->max('students') ?: 1;

            $staffData['gradeSubmission'] = [
                'finalized'  => (int) DB::table('course_blocks')->where('finalized', 1)->count(),
                'inProgress' => (int) DB::table('course_blocks')->where('finalized', 0)->count(),
            ];

            //$staffData['recentUpdates'] = SystemUpdate::latest()->take(5)->get();

            $staffData['myCourses'] = collect();
            if ($user->employee && $activeSemester) {
                // REVISED: Filter by active Academic Year AND Active Semester name
                $staffData['myCourses'] = CourseBlock::where('faculty_id', $user->employee->id)
                    ->where('academic_year_id', $activeSemester->academic_year_id)
                    ->where('semester', $semesterName) // Filter by active semester name
                    ->with(['course', 'sections.program'])
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
       //     'activeAYCount', 'activeSemesterCount', 'currentAYName', 'currentSemName'), 
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
