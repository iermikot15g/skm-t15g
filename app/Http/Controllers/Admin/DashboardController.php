<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $periodeId = $request->periode_id;
        $periodes = $this->reportService->getPeriods();
        $data = $this->reportService->getSuperAdminDashboardData($periodeId);

        return view('admin.dashboard.index', compact(
            'data',
            'periodes',
            'periodeId'
        ));
    }
}