<?php
// routes/web.php

use App\Http\Controllers\Public\SurveyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OPDController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OPD\DashboardController as OPDDashboardController;
use App\Http\Controllers\Admin\Pimpinan\DashboardController as PimpinanDashboardController;
use App\Http\Controllers\Admin\PimpinanUtama\DashboardController as PimpinanUtamaDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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
// DASHBOARD ROUTE - WAJIB ADA UNTUK REDIRECT AFTER LOGIN
// ============================================
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if (!$user) {
        return redirect()->route('login');
    }

    // Redirect berdasarkan role
    if ($user->isSuperAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->isAdminOPD()) {
        return redirect()->route('admin.opd.dashboard');
    } elseif ($user->isPimpinanOPD()) {
        return redirect()->route('admin.pimpinan.dashboard');
    } elseif ($user->isPimpinanUtama()) {
        return redirect()->route('admin.utama.dashboard');
    }
    
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ============================================
// PROFILE ROUTES (Breeze)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================
// ADMIN ROUTES - Semua role admin
// ============================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    
    // ============================================
    // DASHBOARD SUPER ADMIN - hanya super_admin
    // ============================================
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Super Admin Dashboard - Export PDF
        Route::post('/dashboard/export-pdf', [DashboardController::class, 'exportPDF'])->name('dashboard.export-pdf');
    });
    
    // ============================================
    // DASHBOARD ADMIN OPD - admin_opd
    // ============================================
    Route::middleware(['role:admin_opd'])->group(function () {
        Route::get('/opd/dashboard', [OPDDashboardController::class, 'index'])->name('opd.dashboard');
        
        // Admin OPD Dashboard - Export PDF
        Route::post('/opd/dashboard/export-pdf', [OPDDashboardController::class, 'exportPDF'])->name('opd.dashboard.export-pdf');
    });
    
    // ============================================
    // DASHBOARD PIMPINAN OPD - pimpinan_opd
    // ============================================
    Route::middleware(['role:pimpinan_opd'])->group(function () {
        Route::get('/pimpinan/dashboard', [PimpinanDashboardController::class, 'index'])->name('pimpinan.dashboard');
        
        // Pimpinan OPD Dashboard - Export PDF
        Route::post('/pimpinan/dashboard/export-pdf', [PimpinanDashboardController::class, 'exportPDF'])->name('pimpinan.dashboard.export-pdf');
    });
    
    // ============================================
    // DASHBOARD PIMPINAN UTAMA - pimpinan_utama & super_admin
    // ============================================
    Route::middleware(['role:pimpinan_utama,super_admin'])->group(function () {
        Route::get('/utama/dashboard', [PimpinanUtamaDashboardController::class, 'index'])->name('utama.dashboard');
        
        // Pimpinan Utama Dashboard - Export PDF
        Route::post('/utama/dashboard/export-pdf', [PimpinanUtamaDashboardController::class, 'exportPDF'])->name('utama.dashboard.export-pdf');
    });

    // ============================================
    // ADMIN OPD - LAYANAN MANAGEMENT (CRUD)
    // ============================================
    Route::middleware(['role:admin_opd'])->prefix('opd')->name('opd.')->group(function () {
        Route::resource('layanan', \App\Http\Controllers\Admin\OPD\LayananController::class)
            ->except(['show']);
        Route::post('/layanan/{layanan}/toggle', [\App\Http\Controllers\Admin\OPD\LayananController::class, 'toggle'])
            ->name('layanan.toggle');
    });
    
    // ============================================
    // SUPER ADMIN ONLY - CRUD Operations
    // ============================================
    Route::middleware(['role:super_admin'])->group(function () {
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
    // LAPORAN ROUTES - Semua role admin
    // ============================================
    Route::middleware(['role:super_admin,admin_opd,pimpinan_opd,pimpinan_utama'])->group(function () {
        Route::get('/laporan', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('laporan.index');
        Route::post('/laporan/export-pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportPDF'])->name('laporan.export-pdf');
        Route::post('/laporan/export-excel', [\App\Http\Controllers\Admin\ReportController::class, 'exportExcel'])->name('laporan.export-excel');
    });
});

// ============================================
// AUTH ROUTES (Breeze)
// ============================================
require __DIR__.'/auth.php';