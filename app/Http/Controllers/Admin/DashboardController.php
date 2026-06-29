<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class DashboardController extends BaseAdminController
{
    protected function getDashboardView(): string
    {
        return 'admin.dashboard.index';
    }

    protected function getPDFView(): string
    {
        return 'admin.dashboard.pdf';
    }

    protected function getExportRouteName(): string
    {
        return 'admin.dashboard.export-pdf';
    }

    protected function getPDFFilename(): string
    {
        return 'Dashboard_SuperAdmin_' . date('Ymd') . '.pdf';
    }

    protected function getDashboardData(Request $request): array
    {
        $periodeId = $request->periode_id;
        return $this->reportService->getSuperAdminDashboardData($periodeId);
    }

    protected function generatePDF(Request $request): \Barryvdh\DomPDF\PDF
    {
        $periodeId = $request->periode_id;
        return $this->pdfGenerator->generateSuperAdminPDF($periodeId);
    }
}