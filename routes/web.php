<?php

use App\Http\Controllers\AuditDashboardController;
use App\Http\Controllers\AuditParametersController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuditDashboardController::class, 'index'])->name('dashboard');
    Route::get('/schools', [AuditDashboardController::class, 'schools'])->name('schools');
    Route::put('/schools/{school}', [AuditDashboardController::class, 'updateSchool'])->name('schools.update');
    Route::redirect('/secondary/dashboard', '/dashboard');
    Route::get('/secondary/schools', fn () => redirect()->route('schools', ['level' => 'secondary']));
    Route::put('/secondary/schools/{school}', [AuditDashboardController::class, 'updateSchool'])->name('secondary.schools.update');
    Route::get('/parameters', [AuditParametersController::class, 'index'])->name('parameters');
    Route::put('/parameters', [AuditParametersController::class, 'update'])->name('parameters.update');
});
