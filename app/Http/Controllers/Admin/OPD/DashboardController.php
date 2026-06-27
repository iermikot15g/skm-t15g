<?php
// app/Http/Controllers/Admin/OPD/DashboardController.php

namespace App\Http\Controllers\Admin\OPD;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;

class DashboardController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        $user = auth()->user();
        
        // Jika user tidak memiliki OPD, redirect
        if (!$user->opd_id) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Anda tidak terikat dengan OPD tertentu.');
        }

        $stats = $this->reportService->getOPDStats($user->opd_id);

        return view('admin.opd.dashboard', compact('stats'));
    }
}