<?php
// routes/web.php

use App\Http\Controllers\Public\SurveyController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
        
        // FASE 3: Questions & Kritik Saran
        Route::get('/questions', [SurveyController::class, 'questions'])->name('questions');
        Route::post('/store-questions', [SurveyController::class, 'storeQuestions'])->name('store-questions');
        Route::get('/kritik-saran', [SurveyController::class, 'kritikSaran'])->name('kritik-saran');
        Route::post('/store-kritik-saran', [SurveyController::class, 'storeKritikSaran'])->name('store-kritik-saran');
        
        // FASE 3: Review & Submit
        Route::get('/review', [SurveyController::class, 'review'])->name('review');
        Route::post('/submit', [SurveyController::class, 'submit'])->name('submit');
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