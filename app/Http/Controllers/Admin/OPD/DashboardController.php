<?php
// app/Http/Controllers/Admin/OPD/DashboardController.php

namespace App\Http\Controllers\Admin\OPD;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use App\Services\Report\DashboardPDFGenerator;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportService $reportService;
    protected DashboardPDFGenerator $pdfGenerator;

    /**
     * Constructor dengan 2 dependencies
     */
    public function __construct(ReportService $reportService, DashboardPDFGenerator $pdfGenerator)
    {
        $this->reportService = $reportService;
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Tampilkan dashboard Admin OPD
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Jika user tidak memiliki OPD, redirect
        if (!$user->opd_id) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Anda tidak terikat dengan OPD tertentu.');
        }

        $periodeId = $request->periode_id;
        $periodes = $this->reportService->getPeriods();
        $data = $this->reportService->getAdminOPDDashboardData($user->opd_id, $periodeId);

        return view('admin.opd.dashboard', compact(
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
        $user = auth()->user();
        $periodeId = $request->periode_id;
        $pdf = $this->pdfGenerator->generateAdminOPDPDF($user->opd_id, $periodeId);
        
        $opd = \App\Models\OPD::find($user->opd_id);
        $filename = 'Dashboard_OPD_' . ($opd ? $opd->kode_opd : '') . '_' . date('Ymd') . '.pdf';
        return $pdf->download($filename);
    }
}