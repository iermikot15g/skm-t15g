<?php
// routes/web.php

use App\Http\Controllers\Public\SurveyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OPDController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// ============================================
// PUBLIC ROUTES
// ============================================
Route::get('/', [SurveyController::class, 'landing'])->name('home');
Route::get('/survei/closed', [SurveyController::class, 'closed'])->name('survey.closed');

Route::prefix('survei')->name('survey.')->group(function () {
    Route::middleware(['check.survey.period'])->group(function () {
        Route::get('/opd', [SurveyController::class, 'selectOPD'])->name('opd');
        Route::post('/identity', [SurveyController::class, 'identity'])->name('identity');
        Route::post('/store-identity', [SurveyController::class, 'storeIdentity'])->name('store-identity');
        Route::get('/questions', [SurveyController::class, 'questions'])->name('questions');
        Route::post('/store-questions', [SurveyController::class, 'storeQuestions'])->name('store-questions');
        Route::get('/kritik-saran', [SurveyController::class, 'kritikSaran'])->name('kritik-saran');
        Route::post('/store-kritik-saran', [SurveyController::class, 'storeKritikSaran'])->name('store-kritik-saran');
        Route::get('/review', [SurveyController::class, 'review'])->name('review');
        Route::post('/submit', [SurveyController::class, 'submit'])->name('submit');
    });
});

// ============================================
// ADMIN ROUTES - Semua role admin
// ============================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:super_admin,admin_opd,pimpinan_opd,pimpinan_utama'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// ============================================
// ADMIN ROUTES - Super Admin Only
// ============================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:super_admin'])->group(function () {
    // OPD Management
    Route::resource('opd', OPDController::class);
    Route::post('/opd/{opd}/toggle', [OPDController::class, 'toggle'])->name('opd.toggle');
    
    // Layanan Management
    Route::resource('layanan', LayananController::class);
    Route::post('/layanan/{layanan}/toggle', [LayananController::class, 'toggle'])->name('layanan.toggle');
    
    // Periode Management
    Route::resource('periode', PeriodeController::class);
    Route::post('/periode/{periode}/toggle', [PeriodeController::class, 'toggle'])->name('periode.toggle');
    
    // User Management
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
});

// ============================================
// AUTH ROUTES (Breeze)
// ============================================
require __DIR__.'/auth.php';