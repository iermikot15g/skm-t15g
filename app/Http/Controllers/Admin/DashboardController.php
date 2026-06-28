<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use App\Services\Report\DashboardPDFGenerator;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportService $reportService;
    protected DashboardPDFGenerator $pdfGenerator;

    public function __construct(ReportService $reportService, DashboardPDFGenerator $pdfGenerator)
    {
        $this->reportService = $reportService;
        $this->pdfGenerator = $pdfGenerator;
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

    /**
     * Export dashboard ke PDF
     */
    public function exportPDF(Request $request)
    {
        $periodeId = $request->periode_id;
        $pdf = $this->pdfGenerator->generateSuperAdminPDF($periodeId);
        
        $filename = 'Dashboard_SuperAdmin_' . date('Ymd') . '.pdf';
        return $pdf->download($filename);
    }
}