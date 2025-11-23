<?php

use App\Http\Controllers\SubstituteAcknowledgementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LeaveApplicationController; // For employee-side leave application management
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AcademicHeadLeaveApplicationController; // Your AH Controller!
use App\Http\Controllers\HrLeaveApplicationController; // Assuming you have this for HR
use App\Http\Controllers\NotificationController; // For global notification actions
use Illuminate\Http\Request; // Needed for the API route closure

use App\Http\Controllers\AdminLeaveApplicationController; // Your AH Controller!
use App\Http\Controllers\LeaveApplicationStatusController;

use App\Http\Controllers\EmployeeLeaveController;
//use App\Http\Controllers\Admin\LeaveController as AdminLeaveController;

use App\Http\Controllers\HrController;

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\FacultyLoadingController;
use App\Http\Controllers\FacultyCourseController;
use App\Http\Controllers\CourseToSectionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Default welcome route
Route::get('/', function () {
    return view('welcome');
});


// // 🔹 TEMPORARY PUBLIC ROUTE
// Route::post('/test-mark-all-as-read', [NotificationController::class, 'markAllAsRead']);

// Authenticated user routes (dashboard, profile, etc.)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Primary Dashboard Route - This is the default authenticated landing page.
    // It should handle redirects or dynamic content based on the user's role.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leaveapplicationstatus', [LeaveApplicationStatusController::class, 'index'])->name('leaveapplicationstatus');
    Route::get('/my-leave', [EmployeeLeaveController::class, 'index']);

    // Admin and Teacher specific routes (apply roles middleware)
    Route::middleware(['role:academic_head|registrar'])->group(function () {
        
        Route::resource('courses', CourseController::class);
        Route::resource('enrollments', EnrollmentController::class);
         Route::resource('coursetosections', CourseToSectionController::class);
        Route::resource('programs', ProgramController::class);
        Route::resource('sections', SectionController::class);
        Route::resource('academic_years', AcademicYearController::class);
        Route::resource('semesters', SemesterController::class);
        Route::get('/students/upload', [StudentController::class, 'showUploadForm'])->name('students.upload.form');
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
        Route::resource('students', StudentController::class);

    });

   

    // HR specific routes (apply roles middleware)
    Route::middleware(['role:hr|admin|academic_head'])->group(function () {
        
        Route::get('/hr/leave-credits/all', [HrController::class, 'showAllEmployeeLeaveCredits'])
         ->name('hr.leave_credits.all');
        Route::resource('/hr/leave-credits', HrController::class);
         Route::resource('leave_applications', LeaveApplicationController::class); // Admin/Teacher view of ALL leave applications?
       // This route will handle the HR view of pending applications
        Route::get('/hr/pending-applications', [LeaveApplicationController::class, 'pending'])->name('hr.leave_applications.pending');

        // This route will handle the HR view of all applications
        Route::get('/hr/all-applications', [LeaveApplicationController::class, 'all'])->name('hr.leave_applications.all');

        Route::resource('employees', EmployeeController::class);
        
        Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])
        ->name('employees.reset-password')
        ->middleware('auth'); // Ensure this is protected by appropriate middleware
        
    });


   
    Route::resource('leave_applications', LeaveApplicationController::class); // Admin/Teacher view of ALL leave applications?
    
    // Reports Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/students-per-course', [ReportController::class, 'studentsPerCourse'])->name('reports.studentsPerCourse');
    Route::get('/reports/student-types', [ReportController::class, 'studentTypes'])->name('reports.studentTypes');

    // API route for dynamic semester loading (for reports filter)
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


    // Substitute Teacher routes
    Route::get('/substitute/acknowledge/{classId}', [SubstituteAcknowledgementController::class, 'showAcknowledgementForm'])
        ->name('substitute.acknowledge')
        ->middleware('signed'); // Protects against tampering

    Route::post('/substitute/acknowledge/{classId}', [SubstituteAcknowledgementController::class, 'processAcknowledgement'])
        ->name('substitute.process_acknowledgement')
        ->middleware('auth:web'); // Requires authentication to process


    // Academic Head Leave Application Management Routes (Consolidated)
    // All routes in this group will require the 'academic_head' role.
    Route::middleware(['role:academic_head'])->prefix('academic-head/leave-applications')->name('ah.leave_applications.')->group(function () {
        Route::get('/', [AcademicHeadLeaveApplicationController::class, 'index'])->name('index'); // AH Dashboard / Pending review list
        Route::get('/review/{leaveApplication}', [AcademicHeadLeaveApplicationController::class, 'review'])->name('review')->middleware('signed'); // View/Review specific application
        Route::post('/decide/{leaveApplication}', [AcademicHeadLeaveApplicationController::class, 'decide'])->name('decide'); // Process decision
        Route::get('/all', [AcademicHeadLeaveApplicationController::class, 'allLeaveApplications'])->name('all'); // NEW: View all applications
    });


     // HR Leave Application Management Routes (Assuming HrLeaveApplicationController exists)
    Route::middleware(['role:hr'])->prefix('hr/leave-applications')->name('hr.leave_applications.')->group(function () {
        Route::get('/', [HrLeaveApplicationController::class, 'index'])->name('index'); // HR Dashboard / Pending review list
        Route::get('/review/{leaveApplication}', [HrLeaveApplicationController::class, 'review'])->name('review')->middleware('signed'); // View/Review specific application
        Route::post('/decide/{leaveApplication}', [HrLeaveApplicationController::class, 'decide'])->name('decide'); // Process decision
        // Consider adding a Route::get('/all', [HrLeaveApplicationController::class, 'allLeaveApplications'])->name('all'); for HR too
    });

     // Admin Leave Application Management Routes (Assuming AdminLeaveApplicationController exists)
    Route::middleware(['role:admin'])->prefix('admin/leave-applications')->name('admin.leave_applications.')->group(function () {
        Route::get('/', [AdminLeaveApplicationController::class, 'index'])->name('index'); // Admin Dashboard / Pending review list
        Route::get('/review/{leaveApplication}', [AdminLeaveApplicationController::class, 'review'])->name('review')->middleware('signed'); // View/Review specific application
        Route::post('/decide/{leaveApplication}', [AdminLeaveApplicationController::class, 'decide'])->name('decide'); // Process decision
        // Consider adding a Route::get('/all', [HrLeaveApplicationController::class, 'allLeaveApplications'])->name('all'); for HR too
    });


    // Global Notifications routes (can be accessed by any authenticated user)
    Route::post('/notifications/{notification}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/test/markall', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    
    // Route::get('/test', [TestController::class, 'index'])->name('test.index');
    // Route::post('/test/call', [TestController::class, 'call'])->name('test.call');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile/password', [ChangePasswordController::class, 'edit'])
        ->name('password.edit');

    Route::put('/profile/password', [ChangePasswordController::class, 'update'])
        ->name('profile.password.update');

    Route::get('faculty-loadings/{id}/delete', [FacultyLoadingController::class, 'delete'])
    ->name('faculty-loadings.delete');

    Route::resource('faculty-loadings', FacultyLoadingController::class);

    // 1. Route to show the initial page with filters (No results yet)
    Route::get('/faculty/course-load', [FacultyCourseController::class, 'index'])
        ->name('faculty.course_load');
        
    // 2. Route to show the filtered results
    Route::get('/faculty/course-load/view', [FacultyCourseController::class, 'showLoad'])
        ->name('faculty.course_load.show');
  
});

// Standard Jetstream authentication routes
require __DIR__.'/auth.php';