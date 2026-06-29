<?php
// app/Http/Controllers/Admin/OPD/DashboardController.php

namespace App\Http\Controllers\Admin\OPD;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;

class DashboardController extends BaseAdminController
{
    protected function getDashboardView(): string
    {
        return 'admin.opd.dashboard';
    }

    protected function getPDFView(): string
    {
        return 'admin.opd.dashboard-pdf';
    }

    protected function getExportRouteName(): string
    {
        return 'admin.opd.dashboard.export-pdf';
    }

    protected function getPDFFilename(): string
    {
        $user = auth()->user();
        $opd = \App\Models\OPD::find($user->opd_id);
        return 'Dashboard_OPD_' . ($opd ? $opd->kode_opd : '') . '_' . date('Ymd') . '.pdf';
    }

    protected function getDashboardData(Request $request): array
    {
        $user = auth()->user();
        $periodeId = $request->periode_id;
        
        if (!$user->opd_id) {
            throw new \Exception('Anda tidak terikat dengan OPD tertentu.');
        }
        
        return $this->reportService->getAdminOPDDashboardData($user->opd_id, $periodeId);
    }

    protected function generatePDF(Request $request): \Barryvdh\DomPDF\PDF
    {
        $user = auth()->user();
        $periodeId = $request->periode_id;
        return $this->pdfGenerator->generateAdminOPDPDF($user->opd_id, $periodeId);
    }

    /**
     * Override index to handle OPD check
     */
    public function index(Request $request)
    {
        try {
            return parent::index($request);
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', $e->getMessage());
        }
    }
}