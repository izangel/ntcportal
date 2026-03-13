<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Controllers
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AcademicHeadLeaveApplicationController;
use App\Http\Controllers\HrLeaveApplicationController;
use App\Http\Controllers\AdminLeaveApplicationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LeaveApplicationStatusController;
use App\Http\Controllers\EmployeeLeaveController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\FacultyLoadingController;
use App\Http\Controllers\FacultyCourseController;
use App\Http\Controllers\CourseToSectionController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ImportantDateController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\EvaluationReportController;
use App\Http\Controllers\PeerAssignmentController;
use App\Http\Controllers\PeerEvaluationController;
use App\Http\Controllers\SelfEvaluationController;
use App\Http\Controllers\SupervisorEvaluationController;
use App\Http\Controllers\SupervisorAssignmentController;
use App\Http\Controllers\StudentEvaluationController;
use App\Http\Controllers\Admin\BulkUserController;
use App\Http\Controllers\Admin\StudentAccountController;
use App\Http\Controllers\Teacher\MyEvaluationController;
use App\Http\Controllers\Admin\EvaluationMonitoringController;
use App\Http\Controllers\SubstituteAcknowledgementController;

// Livewire Components
use App\Livewire\AssignCourses;
use App\Livewire\AssignCoursesIndividual;
use App\Livewire\CourseBlockManager;
use App\Livewire\FacultyCourseBlockView;
use App\Livewire\Admin\FacultyCourseListView;
use App\Livewire\CourseBlockBulkUploader;
use App\Livewire\FacultyCourseLoad;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Default welcome route
Route::get('/', function () {
    return view('welcome');
});

// Authenticated user routes (dashboard, profile, etc.)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    // Primary Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/portal-updates', \App\Livewire\User\SystemUpdateList::class)->name('portal-updates.list');
    Route::get('/leaveapplicationstatus', [LeaveApplicationStatusController::class, 'index'])->name('leaveapplicationstatus');
    Route::get('/my-leave', [EmployeeLeaveController::class, 'index']);

    // -- Announcements --
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');

    // Only Admin and Teachers can Manage Announcements
    Route::middleware(['can:post-announcements'])->group(function () {
        Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    // -- Important Dates --
    Route::resource('important_dates', ImportantDateController::class)->names([
        'index'   => 'important_dates.index',
        'create'  => 'important_dates.create',
        'store'   => 'important_dates.store',
        'show'    => 'important_dates.show',
        'edit'    => 'important_dates.edit',
        'update'  => 'important_dates.update',
        'destroy' => 'important_dates.destroy',
    ]);

    // -- Admin and Teacher specific routes --
    Route::middleware(['role:academic_head|registrar|hr|admin'])->group(function () {
        Route::resource('courses', CourseController::class);
        Route::resource('coursetosections', CourseToSectionController::class);
        Route::resource('programs', ProgramController::class);
        Route::resource('sections', SectionController::class);
        Route::resource('academic_years', AcademicYearController::class);
        Route::resource('semesters', SemesterController::class);
        
        Route::get('/students/upload', [StudentController::class, 'showUploadForm'])->name('students.upload.form');
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
        Route::get('/students/export', [StudentController::class, 'export'])->name('students.export');
        Route::resource('students', StudentController::class);
        
        Route::get('/assignment/assign-courses', AssignCourses::class)->name('assign.courses');
        Route::get('/assignment/individual', AssignCoursesIndividual::class)->name('assign.individual');
        Route::get('course-blocks', CourseBlockManager::class)->name('course-blocks');
        Route::get('faculty/course-blocks', FacultyCourseBlockView::class)->name('faculty.course-blocks');

        Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
        Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

        Route::get('/system-maintenance/updates', \App\Livewire\Admin\SystemUpdateManager::class)->name('system-updates.manager');
    });

    // -- HR specific routes --
    Route::middleware(['role:hr|admin|academic_head'])->group(function () {
        Route::get('/hr/leave-credits/all', [HrController::class, 'showAllEmployeeLeaveCredits'])->name('hr.leave_credits.all');
        Route::resource('/hr/leave-credits', HrController::class);
        
        Route::get('/hr/pending-applications', [LeaveApplicationController::class, 'pending'])->name('hr.leave_applications.pending');
        Route::get('/hr/all-applications', [LeaveApplicationController::class, 'all'])->name('hr.leave_applications.all');

        Route::resource('employees', EmployeeController::class);
        Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])
            ->name('employees.reset-password')
            ->middleware('auth');

        Route::get('/course-blocks/bulk-upload', CourseBlockBulkUploader::class)->name('course-blocks.bulk-uploader');
        Route::get('/admin/leave-summary', [LeaveApplicationController::class, 'leaveSummary'])->name('admin.leave.summary');

        // HR Peer assignment
        Route::get('/peer-assignments', [PeerAssignmentController::class, 'index'])->name('hr.peer-assignments.index');
        Route::post('/peer-assignments', [PeerAssignmentController::class, 'store'])->name('hr.peer-assignments.store');
        Route::delete('/peer-assignments/{assignment}', [PeerAssignmentController::class, 'destroy'])->name('hr.peer-assignments.destroy');

        Route::get('/supervisor-assignments', [SupervisorAssignmentController::class, 'index'])->name('hr.supervisor-assignments.index');
        Route::post('/supervisor-assignments', [SupervisorAssignmentController::class, 'store'])->name('hr.supervisor-assignments.store');
        Route::delete('/supervisor-assignments/{assignment}', [SupervisorAssignmentController::class, 'destroy'])->name('hr.supervisor-assignments.destroy');

        // Bulk upload student user accounts
        Route::get('/admin/bulk-upload', [BulkUserController::class, 'index'])->name('admin.bulk-upload');
        Route::post('/admin/bulk-upload', [BulkUserController::class, 'store'])->name('admin.bulk-upload.store');

        // Manage student accounts
        Route::get('/admin/student-accounts', [StudentAccountController::class, 'index'])->name('admin.student-accounts.index');
        Route::patch('/admin/student-accounts/{user}/reset', [StudentAccountController::class, 'resetPassword'])->name('admin.student-accounts.reset');

        // View student evaluation status
        Route::get('/admin/monitoring/evaluations', [EvaluationMonitoringController::class, 'index'])->name('admin.monitoring.evaluations');
    });

    Route::resource('leave_applications', LeaveApplicationController::class);

    // -- Reports Routes --
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/students-per-course', [ReportController::class, 'studentsPerCourse'])->name('reports.studentsPerCourse');
    Route::get('/reports/student-types', [ReportController::class, 'studentTypes'])->name('reports.studentTypes');

    // API route for dynamic semester loading
    Route::get('/api/semesters-by-academic-year', function (Request $request) {
        $academicYearId = $request->input('academic_year_id');
        if ($academicYearId) {
            return response()->json(
                \App\Models\Semester::where('academic_year_id', $academicYearId)
                                    ->orderBy('name')
                                    ->get(['id', 'name'])
            );
        }
        return response()->json([]);
    })->name('api.semestersByAcademicYear');

    Route::get('/admin/faculty-courses', FacultyCourseListView::class)->name('admin.faculty.courses');

    // -- Substitute Teacher routes --
    Route::get('/substitute/acknowledge/{classId}', [SubstituteAcknowledgementController::class, 'showAcknowledgementForm'])
        ->name('substitute.acknowledge')
        ->middleware('signed');
    Route::post('/substitute/acknowledge/{classId}', [SubstituteAcknowledgementController::class, 'processAcknowledgement'])
        ->name('substitute.process_acknowledgement')
        ->middleware('auth:web');

    // -- Academic Head Leave Application Management --
    Route::middleware(['role:academic_head'])->prefix('academic-head/leave-applications')->name('ah.leave_applications.')->group(function () {
        Route::get('/', [AcademicHeadLeaveApplicationController::class, 'index'])->name('index');
        Route::get('/review/{leaveApplication}', [AcademicHeadLeaveApplicationController::class, 'review'])->name('review')->middleware('signed');
        Route::post('/decide/{leaveApplication}', [AcademicHeadLeaveApplicationController::class, 'decide'])->name('decide');
        Route::get('/all', [AcademicHeadLeaveApplicationController::class, 'allLeaveApplications'])->name('all');
    });

    // -- HR Leave Application Management --
    Route::middleware(['role:hr'])->prefix('hr/leave-applications')->name('hr.leave_applications.')->group(function () {
        Route::get('/', [HrLeaveApplicationController::class, 'index'])->name('index');
        Route::get('/review/{leaveApplication}', [HrLeaveApplicationController::class, 'review'])->name('review')->middleware('signed');
        Route::post('/decide/{leaveApplication}', [HrLeaveApplicationController::class, 'decide'])->name('decide');
        Route::get('/retroactive', [HrLeaveApplicationController::class, 'showRetroactiveForm'])->name('retroactive_form');
        Route::post('/retroactive', [HrLeaveApplicationController::class, 'storeRetroactive'])->name('store_retroactive');
    });

    Route::middleware(['role:hr'])->get('/hr/employee-leave-credits/{employeeId}', [HrLeaveApplicationController::class, 'getEmployeeLeaveCredits'])->name('hr.employee_leave_credits');

    // -- Admin Leave Application Management --
    Route::middleware(['role:admin'])->prefix('admin/leave-applications')->name('admin.leave_applications.')->group(function () {
        Route::get('/', [AdminLeaveApplicationController::class, 'index'])->name('index');
        Route::get('/review/{leaveApplication}', [AdminLeaveApplicationController::class, 'review'])->name('review')->middleware('signed');
        Route::post('/decide/{leaveApplication}', [AdminLeaveApplicationController::class, 'decide'])->name('decide');
    });

    // -- Global Notifications routes --
    Route::post('/notifications/{notification}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/test/markall', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile/password', [ChangePasswordController::class, 'edit'])->name('password.edit');
    Route::put('/profile/password', [ChangePasswordController::class, 'update'])->name('profile.password.update');

    Route::get('faculty-loadings/{id}/delete', [FacultyLoadingController::class, 'delete'])->name('faculty-loadings.delete');
    Route::resource('faculty-loadings', FacultyLoadingController::class);

    // Faculty Course Load
    Route::get('/faculty/course-load', [FacultyCourseController::class, 'index'])->name('faculty.course_load');
    Route::get('/faculty/course-load/view', [FacultyCourseController::class, 'showLoad'])->name('faculty.course_load.show');
    Route::get('faculty/course-blocks', FacultyCourseBlockView::class)->name('faculty.course-blocks');
    Route::get('/my-course-load', FacultyCourseLoad::class)->name('faculty.course-load');

    // Student Dashboard/Courses Route
    Route::get('/my-courses', [StudentCourseController::class, 'index'])->name('student.courses');
    Route::post('/my-courses/evaluate', [StudentCourseController::class, 'storeEvaluation'])->name('student.courses.evaluate');

    // Faculty Reports
    Route::get('/faculty/reports', [EvaluationReportController::class, 'index'])->name('faculty.reports.index');
    Route::get('/faculty/reports/view', [EvaluationReportController::class, 'show360Report'])->name('faculty.reports.view');

    // Faculty Peer evaluations
    Route::get('/peer-evaluations', [PeerEvaluationController::class, 'index'])->name('faculty.peer-evaluations.index');
    Route::get('/peer-evaluations/{assignment}/create', [PeerEvaluationController::class, 'create'])->name('faculty.peer-evaluations.create');
    Route::post('/peer-evaluations/{assignment}', [PeerEvaluationController::class, 'store'])->name('faculty.peer-evaluations.store');

    // Teacher evaluation report
    Route::get('/my-evaluations', [MyEvaluationController::class, 'index'])->name('teacher.evaluations.index');
    Route::get('/my-evaluations/{academic_year_id}/{semester}', [MyEvaluationController::class, 'show'])->name('teacher.evaluations.report');

    // Self-Evaluation
    Route::get('/self-evaluation', [SelfEvaluationController::class, 'index'])->name('faculty.self-evaluations.index');
    Route::get('/self-evaluation/form', [SelfEvaluationController::class, 'create'])->name('faculty.self-evaluations.create');
    Route::post('/self-evaluation', [SelfEvaluationController::class, 'store'])->name('faculty.self-evaluations.store');

    // Supervisor Evaluations
    Route::get('/evaluations', [SupervisorEvaluationController::class, 'index'])->name('supervisor.evaluations.index');
    Route::get('/evaluations/{assignment}/create', [SupervisorEvaluationController::class, 'create'])->name('supervisor.evaluations.create');
    Route::post('/evaluations/{assignment}', [SupervisorEvaluationController::class, 'store'])->name('supervisor.evaluations.store');

    // --- STUDENT ROUTES ---
    Route::middleware(['role:student'])
        ->prefix('student')
        ->name('student.')
        ->group(function () {
            Route::get('/evaluations', [StudentEvaluationController::class, 'index'])->name('evaluations.index');
            Route::get('/evaluations/{courseBlock}/create', [StudentEvaluationController::class, 'create'])->name('evaluations.create');
            Route::post('/evaluations/{courseBlock}', [StudentEvaluationController::class, 'store'])->name('evaluations.store');
            Route::get('course-blocks', \App\Livewire\StudentCourseBlock::class)->name('course-blocks');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';