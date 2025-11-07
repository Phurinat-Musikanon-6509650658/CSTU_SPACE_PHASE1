<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Login page (named so controllers can redirect to the login route)
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);

// Protected welcome page
Route::get('welcome', [AuthController::class, 'showWelcome'])->name('welcome');

// Simple logout route (clears session and redirects to login)
Route::get('logout', [AuthController::class, 'logout'])->name('logout');
