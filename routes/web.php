<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\StudentManagementController;

// Login page (named so controllers can redirect to the login route)
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);

// Menu page with role-based content
Route::get('menu', [MenuController::class, 'index'])->name('menu');

// User Management (Admin only)
Route::resource('users', UserManagementController::class);
Route::get('users-import', [UserManagementController::class, 'importForm'])->name('users.importForm');
Route::post('users-import', [UserManagementController::class, 'import'])->name('users.import');
Route::get('users-template', [UserManagementController::class, 'downloadTemplate'])->name('users.downloadTemplate');

// Student Management (Admin only)
Route::resource('students', StudentManagementController::class)->except(['index']);
Route::get('students-import', [StudentManagementController::class, 'importForm'])->name('students.importForm');
Route::post('students-import', [StudentManagementController::class, 'import'])->name('students.import');
Route::get('students-template', [StudentManagementController::class, 'downloadTemplate'])->name('students.downloadTemplate');

// Simple logout route (clears session and redirects to login)
Route::get('logout', [AuthController::class, 'logout'])->name('logout');
