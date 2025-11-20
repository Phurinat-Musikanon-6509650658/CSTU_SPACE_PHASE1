<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\StudentManagementController;
use App\Http\Controllers\AdminLogController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\PermissionTestController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupInvitationController;

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
Route::middleware(['auth:student'])->group(function () {
    
    // Student Menu/Dashboard
    Route::get('/student/menu', [StudentController::class, 'menu'])->name('student.menu');
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    
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
        Route::post('{invitation}/accept', [GroupInvitationController::class, 'accept'])->name('accept');
        Route::post('{invitation}/decline', [GroupInvitationController::class, 'decline'])->name('decline');
    });
    
});

// ======================================
// Protected Routes
// ======================================

// Protected routes with session timeout middleware
Route::middleware('session.timeout')->group(function () {
    
    // ======================================
    // Main Menu
    // ======================================
    Route::get('menu', [MenuController::class, 'index'])->name('menu');

    // ======================================
    // User Management (Admin Only)
    // ======================================
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('{user}', [UserManagementController::class, 'show'])->name('show');
        Route::get('{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        
        // Import/Export functionality
        Route::get('import/form', [UserManagementController::class, 'importForm'])->name('importForm');
        Route::post('import', [UserManagementController::class, 'import'])->name('import');
        Route::get('template/download', [UserManagementController::class, 'downloadTemplate'])->name('downloadTemplate');
    });

    // ======================================
    // Student Management (Admin/Coordinator)
    // ======================================
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('create', [StudentManagementController::class, 'create'])->name('create');
        Route::post('/', [StudentManagementController::class, 'store'])->name('store');
        Route::get('{student}', [StudentManagementController::class, 'show'])->name('show');
        Route::get('{student}/edit', [StudentManagementController::class, 'edit'])->name('edit');
        Route::put('{student}', [StudentManagementController::class, 'update'])->name('update');
        Route::delete('{student}', [StudentManagementController::class, 'destroy'])->name('destroy');
        
        // Import/Export functionality
        Route::get('import/form', [StudentManagementController::class, 'importForm'])->name('importForm');
        Route::post('import', [StudentManagementController::class, 'import'])->name('import');
        Route::get('template/download', [StudentManagementController::class, 'downloadTemplate'])->name('downloadTemplate');
    });

    // ======================================
    // Admin Management
    // ======================================
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Login Logs Management
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [AdminLogController::class, 'index'])->name('index');
            Route::get('{log}', [AdminLogController::class, 'show'])->name('show');
            Route::get('export/csv', [AdminLogController::class, 'export'])->name('export');
        });
        
        // System Settings Management (Admin Only)
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/', [SystemSettingsController::class, 'index'])->name('index');
            Route::post('clear-cache', [SystemSettingsController::class, 'clearCache'])->name('clear-cache');
            Route::post('optimize', [SystemSettingsController::class, 'optimize'])->name('optimize');
            Route::get('config', [SystemSettingsController::class, 'showConfig'])->name('config');
            Route::post('migrate', [SystemSettingsController::class, 'runMigrations'])->name('migrate');
            Route::get('logs', [SystemSettingsController::class, 'showLogs'])->name('logs');
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
