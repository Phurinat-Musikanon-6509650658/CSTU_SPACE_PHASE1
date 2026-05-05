<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\StudentManagementController;
use App\Http\Controllers\AdminLogController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\PermissionTestController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupInvitationController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\CoordinatorUserController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AdminSubmissionController;
use App\Http\Controllers\CoordinatorSubmissionController;
use App\Http\Controllers\StaffSubmissionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\LecturerSubmissionController;

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

// ======================================
// Authentication Routes
// ======================================

// Login page (named so controllers can redirect to the login route)
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login.page');
Route::post('login', [AuthController::class, 'login'])->name('login.submit');

// Logout routes
Route::get('logout', [AuthController::class, 'logout'])->name('logout');
Route::post('logout-beacon', [AuthController::class, 'logoutBeacon'])->name('logout-beacon');

// Session management
Route::post('refresh-session', [AuthController::class, 'refreshSession'])->name('refresh-session');

// ======================================
// Student Routes (Protected by student auth)
// ======================================
Route::middleware(['auth:student', 'check.system.status', 'check.subject.access'])->group(function () {
    
    // Student Menu/Dashboard
    Route::get('/student/menu', [StudentController::class, 'menu'])->name('student.menu');
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/student/debug', function() {
        $student = Auth::guard('student')->user();
        $myGroup = $student->groups()->with(['members.student', 'latestProposal.lecturer'])->first();
        $pendingInvitations = $student->pendingInvitations()->with(['group', 'inviter'])->orderBy('created_at', 'desc')->get();
        $isGroupLeader = false;
        if ($myGroup) {
            $firstMember = $myGroup->members()->orderBy('groupmem_id', 'asc')->first();
            $isGroupLeader = $firstMember && $firstMember->username_std === $student->username_std;
        }
        return view('student.debug', compact('student', 'myGroup', 'pendingInvitations', 'isGroupLeader'));
    })->name('student.debug');
    
    // Group Management Routes
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::get('create', [GroupController::class, 'create'])->name('create');
        Route::post('store', [GroupController::class, 'store'])->name('store');
        Route::get('{group}', [GroupController::class, 'show'])->name('show');
        Route::get('search/students', [GroupController::class, 'searchStudents'])->name('search-students');
        Route::post('leave', [GroupController::class, 'leaveGroup'])->name('leave');
    });
    
    // Group Invitation Management Routes
    Route::prefix('invitations')->name('invitations.')->group(function () {
        Route::get('/', [GroupInvitationController::class, 'index'])->name('index');
        Route::post('/', [GroupInvitationController::class, 'store'])->name('store');
        Route::post('{invitation}/accept', [GroupInvitationController::class, 'accept'])->name('accept');
        Route::post('{invitation}/decline', [GroupInvitationController::class, 'decline'])->name('decline');
        Route::delete('{invitation}/cancel', [GroupInvitationController::class, 'cancel'])->name('cancel');
    });
    
    // Project Proposal Routes (Group Leaders only)
    Route::prefix('proposals')->name('proposals.')->group(function () {
        Route::get('groups/{group}/create', [ProposalController::class, 'create'])->name('create');
        Route::post('groups/{group}', [ProposalController::class, 'store'])->name('store');
    });
    
    // Submission Routes (PDF Report Upload)
    Route::prefix('submission')->name('student.submission.')->group(function () {
        Route::get('/', [SubmissionController::class, 'showUploadForm'])->name('form');
        Route::post('/{project}/upload', [SubmissionController::class, 'upload'])->name('upload');
        Route::get('/{project}/download', [SubmissionController::class, 'download'])->name('download');
    });
    
    // Student Grades View
    Route::get('grades', [StudentController::class, 'viewGrades'])->name('student.grades');
    
});

// ======================================
// Protected Routes
// ======================================

// Protected routes with session timeout middleware
Route::middleware('session.timeout')->group(function () {
    
    // ======================================
    // Coordinator Routes
    // ======================================
    Route::prefix('coordinator')->name('coordinator.')->middleware('role:coordinator,admin,staff', 'check.system.status')->group(function () {
        Route::get('/', function() { return view('coordinator.menu'); })->name('menu');
        Route::get('dashboard', [CoordinatorController::class, 'dashboard'])->name('dashboard');
        
        // Exam Schedules Management (Full Access for Coordinator & Staff)
        Route::prefix('exam-schedules')->name('exam-schedules.')->middleware('role:staff,coordinator,admin')->group(function () {
            Route::get('/', [SystemSettingsController::class, 'coordinatorExamScheduleIndex'])->name('index');
            Route::get('calendar', [SystemSettingsController::class, 'coordinatorExamScheduleCalendar'])->name('calendar');
            Route::get('create', [SystemSettingsController::class, 'coordinatorExamScheduleCreate'])->name('create');
            Route::post('/', [SystemSettingsController::class, 'coordinatorExamScheduleStore'])->name('store');
            Route::get('{id}/edit', [SystemSettingsController::class, 'coordinatorExamScheduleEdit'])->name('edit');
            Route::put('{id}', [SystemSettingsController::class, 'coordinatorExamScheduleUpdate'])->name('update');
            Route::delete('{id}', [SystemSettingsController::class, 'coordinatorExamScheduleDestroy'])->name('destroy');
        });
        
        Route::prefix('groups')->name('groups.')->group(function () {
            Route::get('/', [CoordinatorController::class, 'groups'])->name('index');
            Route::get('{id}', [CoordinatorController::class, 'groupShow'])->name('show');
            Route::post('{id}/approve', [CoordinatorController::class, 'approveGroup'])->name('approve');
        });
        
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::put('{id}', [CoordinatorController::class, 'updateProject'])->name('update');
            Route::get('export/csv', [CoordinatorController::class, 'exportCsv'])->name('export.csv');
            Route::get('review', [CoordinatorController::class, 'projectsReview'])->name('review');
            Route::put('review/{projectId}', [CoordinatorController::class, 'updateProjectReview'])->name('update-review');
        });
        
        Route::prefix('proposals')->name('proposals.')->group(function () {
            Route::get('/', [ProposalController::class, 'coordinatorIndex'])->name('index');
        });
        
        // Submission Download for Coordinator/Staff
        Route::get('submission/{project}/download', [SubmissionController::class, 'download'])->name('submission.download');
        
        // User & Student Management (Import/Export only - NO role assignment)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [CoordinatorUserController::class, 'index'])->name('index');
            Route::get('export', [CoordinatorUserController::class, 'exportUsers'])->name('export');
            Route::get('import', [CoordinatorUserController::class, 'importUserForm'])->name('importForm');
            Route::post('import', [CoordinatorUserController::class, 'importUsers'])->name('import');
        });
        
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('export', [CoordinatorUserController::class, 'exportStudents'])->name('export');
            Route::get('import', [CoordinatorUserController::class, 'importStudentForm'])->name('importForm');
            Route::post('import', [CoordinatorUserController::class, 'importStudents'])->name('import');
        });
        
        // Schedule & Committee Assignment (Full Access for Coordinator & Staff)
        Route::prefix('schedules')->name('schedules.')->middleware('role:staff,coordinator,admin')->group(function () {
            Route::get('/', [CoordinatorController::class, 'schedulesIndex'])->name('index');
            Route::get('{project}/edit', [CoordinatorController::class, 'scheduleEdit'])->name('edit');
            Route::put('{project}', [CoordinatorController::class, 'scheduleUpdate'])->name('update');
        });
        
        // Evaluation & Grading
        Route::prefix('evaluations')->name('evaluations.')->group(function () {
            Route::get('/', [CoordinatorController::class, 'evaluationsIndex'])->name('index');
            Route::get('{project}/scores', [CoordinatorController::class, 'viewScores'])->name('scores');
            Route::get('{project}/grades', [CoordinatorController::class, 'viewGrades'])->name('grades');
            Route::get('summary', [CoordinatorController::class, 'scoreSummary'])->name('summary');
            Route::post('{project}/release', [CoordinatorController::class, 'releaseGrade'])->name('release');
        });
        
        // Submissions Management
        Route::prefix('submissions')->name('submissions.')->group(function () {
            Route::get('/', [CoordinatorSubmissionController::class, 'index'])->name('index');
            Route::get('{project_id}', [CoordinatorSubmissionController::class, 'show'])->name('show');
            Route::get('{project_id}/download', [CoordinatorSubmissionController::class, 'download'])->name('download');
        });
        
        Route::get('settings', [CoordinatorController::class, 'settings'])->name('settings');
    });
    
    // ======================================
    // Lecturer Routes
    // ======================================
    Route::prefix('lecturer')->name('lecturer.')->middleware('role:lecturer,admin', 'check.system.status')->group(function () {
        // Menu/Dashboard
        Route::get('/', function() { return view('lecturer.menu'); })->name('menu');
        Route::get('/dashboard', [App\Http\Controllers\LecturerController::class, 'dashboard'])->name('dashboard');
        
        // My Projects
        Route::get('/projects', [App\Http\Controllers\LecturerController::class, 'myProjects'])->name('projects.index');
        
        Route::prefix('proposals')->name('proposals.')->group(function () {
            Route::get('/', [ProposalController::class, 'lecturerIndex'])->name('index');
            Route::get('{proposal}', [ProposalController::class, 'show'])->name('show');
            Route::post('{proposal}/approve', [ProposalController::class, 'approve'])->name('approve');
            Route::post('{proposal}/reject', [ProposalController::class, 'reject'])->name('reject');
        });
        
        // Submission Download for Lecturer
        Route::get('submission/{project}/download', [SubmissionController::class, 'download'])->name('submission.download');
        
        // Evaluation & Grading
        Route::prefix('evaluations')->name('evaluations.')->group(function () {
            Route::get('/', [App\Http\Controllers\LecturerController::class, 'evaluationsIndex'])->name('index');
            Route::get('{project}/evaluate', [App\Http\Controllers\LecturerController::class, 'evaluateForm'])->name('form');
            Route::post('{project}/evaluate', [App\Http\Controllers\LecturerController::class, 'submitEvaluation'])->name('submit');
            Route::get('{project}/grades', [App\Http\Controllers\LecturerController::class, 'viewGrade'])->name('grade');
            Route::post('{project}/confirm', [App\Http\Controllers\LecturerController::class, 'confirmGrade'])->name('confirm');
        });
        
        // Submissions Management
        Route::prefix('submissions')->name('submissions.')->group(function () {
            Route::get('/', [LecturerSubmissionController::class, 'index'])->name('index');
            Route::get('{project_id}', [LecturerSubmissionController::class, 'show'])->name('show');
            Route::get('{project_id}/download', [LecturerSubmissionController::class, 'download'])->name('download');
        });
        
        // Grade Confirmation
        Route::get('grades/confirmation', [App\Http\Controllers\LecturerController::class, 'gradeConfirmationIndex'])->name('grades.confirmation');
    });
    
    // ======================================
    // Staff Routes (Submissions only)
    // ======================================
    Route::middleware(['role:staff,coordinator,admin', 'check.system.status'])->prefix('staff')->name('staff.')->group(function () {
        
        // Submissions Management
        Route::prefix('submissions')->name('submissions.')->group(function () {
            Route::get('/', [StaffSubmissionController::class, 'index'])->name('index');
            Route::get('{project_id}', [StaffSubmissionController::class, 'show'])->name('show');
            Route::get('{project_id}/download', [StaffSubmissionController::class, 'download'])->name('download');
        });
        
    });

    // ======================================
    // Main Menu
    // ======================================
    Route::get('menu', [MenuController::class, 'index'])->name('menu');

    // ======================================
    // User Management (Admin/Coordinator Only)
    // ======================================
    Route::prefix('users')->name('users.')->middleware('role:coordinator,admin')->group(function () {
        // View list - Coordinator, Admin can view
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        
        // Create, Edit, Delete
        Route::get('create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        
        // Import/Export
        Route::get('import/form', [UserManagementController::class, 'importForm'])->name('importForm');
        Route::post('import', [UserManagementController::class, 'import'])->name('import');
        Route::get('template/download', [UserManagementController::class, 'downloadTemplate'])->name('downloadTemplate');
        Route::get('export/all', [UserManagementController::class, 'exportAll'])->name('exportAll');
        
        // View details
        Route::get('{user}', [UserManagementController::class, 'show'])->name('show');
    });

    // ======================================
    // Student Management (Admin/Coordinator Only)
    // ======================================
    Route::prefix('students')->name('students.')->middleware('role:coordinator,admin')->group(function () {
        // Create, Edit, Delete
        Route::get('create', [StudentManagementController::class, 'create'])->name('create');
        Route::post('/', [StudentManagementController::class, 'store'])->name('store');
        Route::get('{student}/edit', [StudentManagementController::class, 'edit'])->name('edit');
        Route::put('{student}', [StudentManagementController::class, 'update'])->name('update');
        Route::delete('{student}', [StudentManagementController::class, 'destroy'])->name('destroy');
        
        // Import/Export
        Route::get('import/form', [StudentManagementController::class, 'importForm'])->name('importForm');
        Route::post('import', [StudentManagementController::class, 'import'])->name('import');
        Route::get('template/download', [StudentManagementController::class, 'downloadTemplate'])->name('downloadTemplate');
        Route::get('export/all', [StudentManagementController::class, 'exportAll'])->name('exportAll');
        
        // View details
        Route::get('{student}', [StudentManagementController::class, 'show'])->name('show');
    });

    // ======================================
    // Admin Management
    // ======================================
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Submissions Management
        Route::prefix('submissions')->name('submissions.')->group(function () {
            Route::get('/', [AdminSubmissionController::class, 'index'])->name('index');
            Route::get('{project_id}', [AdminSubmissionController::class, 'show'])->name('show');
            Route::get('{project_id}/download', [AdminSubmissionController::class, 'download'])->name('download');
        });
        
        // Login Logs Management
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [AdminLogController::class, 'index'])->name('index');
            Route::get('{log}', [AdminLogController::class, 'show'])->name('show');
            Route::get('export/csv', [AdminLogController::class, 'export'])->name('export');
        });
        
        // Subject Management (Admin + Staff)
        Route::prefix('subjects')->name('subjects.')->middleware('role:admin,staff')->group(function () {
            Route::get('/', [SubjectController::class, 'index'])->name('index');
            Route::get('create', [SubjectController::class, 'create'])->name('create');
            Route::post('/', [SubjectController::class, 'store'])->name('store');
            Route::get('{subject}/edit', [SubjectController::class, 'edit'])->name('edit');
            Route::put('{subject}', [SubjectController::class, 'update'])->name('update');
            Route::post('{subject}/toggle', [SubjectController::class, 'toggle'])->name('toggle');
            Route::delete('{subject}', [SubjectController::class, 'destroy'])->name('destroy');
        });
        
    });
    
    // ======================================
    // Statistics Dashboard (Admin Only)
    // ======================================
    Route::prefix('statistics')->name('statistics.')->group(function () {
        Route::get('/', [StatisticsController::class, 'index'])->name('index');
        Route::get('export', [StatisticsController::class, 'export'])->name('export');
    });

    // ======================================
    // Permission Testing Route
    // ======================================
    Route::get('test-permission', [PermissionTestController::class, 'testPermission'])->name('test.permission');

});
