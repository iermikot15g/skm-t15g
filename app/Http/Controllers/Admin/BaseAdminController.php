<?php
// app/Http/Controllers/Admin/BaseAdminController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use App\Services\Report\DashboardPDFGenerator;
use Illuminate\Http\Request;

/**
 * Base Admin Controller
 * 
 * Provides common functionality for all admin controllers
 */
abstract class BaseAdminController extends Controller
{
    protected ReportService $reportService;
    protected DashboardPDFGenerator $pdfGenerator;

    public function __construct(ReportService $reportService, DashboardPDFGenerator $pdfGenerator)
    {
        $this->reportService = $reportService;
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Get the view name for this dashboard
     */
    abstract protected function getDashboardView(): string;

    /**
     * Get the PDF view name for this dashboard
     */
    abstract protected function getPDFView(): string;

    /**
     * Get the route name for export PDF
     */
    abstract protected function getExportRouteName(): string;

    /**
     * Get the filename for exported PDF
     */
    abstract protected function getPDFFilename(): string;

    /**
     * Get data for dashboard
     */
    abstract protected function getDashboardData(Request $request): array;

    /**
     * Display dashboard
     */
    public function index(Request $request)
    {
        $data = $this->getDashboardData($request);
        $periodes = $this->reportService->getPeriods();
        $periodeId = $request->periode_id;

        return view($this->getDashboardView(), compact('data', 'periodes', 'periodeId'));
    }

    /**
     * Export dashboard to PDF
     */
    public function exportPDF(Request $request)
    {
        $pdf = $this->generatePDF($request);
        return $pdf->download($this->getPDFFilename());
    }

    /**
     * Generate PDF
     */
    abstract protected function generatePDF(Request $request): \Barryvdh\DomPDF\PDF;
}