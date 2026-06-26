<?php
// routes/web.php

use App\Http\Controllers\Public\SurveyController;
use App\Http\Controllers\Admin\DashboardController;

// ============================================
// PUBLIC ROUTES
// ============================================
Route::get('/', [SurveyController::class, 'landing'])->name('home');
Route::get('/survei/closed', [SurveyController::class, 'closed'])->name('survey.closed');

// Survey routes with middleware
Route::prefix('survei')->name('survey.')->group(function () {
    Route::middleware(['check.survey.period'])->group(function () {
        Route::get('/opd', [SurveyController::class, 'selectOPD'])->name('opd');
        Route::post('/identity', [SurveyController::class, 'identity'])->name('identity');
        Route::post('/store-identity', [SurveyController::class, 'storeIdentity'])->name('store-identity');
        Route::get('/questions', function () {
            return view('public.survey.questions-placeholder');
        })->name('questions');
    });
});

// ============================================
// ADMIN ROUTES
// ============================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:super_admin,admin_opd,pimpinan_opd,pimpinan_utama'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// ============================================
// AUTH ROUTES (Breeze)
// ============================================
require __DIR__.'/auth.php';