<?php
// app/Services/Report/DashboardPDFGenerator.php

namespace App\Services\Report;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OPD;
use App\Models\PeriodeSurvei;
use App\Services\Report\ReportService;

class DashboardPDFGenerator
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate PDF untuk Super Admin Dashboard
     */
    public function generateSuperAdminPDF($periodeId = null)
    {
        $data = $this->reportService->getSuperAdminDashboardData($periodeId);
        $periode = $periodeId ? PeriodeSurvei::find($periodeId) : null;
        $periodes = $this->reportService->getPeriods();

        $pdfData = [
            'data' => $data,
            'periode' => $periode,
            'periodes' => $periodes,
            'periodeId' => $periodeId,
            'generated_at' => now()->format('d/m/Y H:i'),
            'role' => 'Super Admin',
            'title' => 'Dashboard Super Admin',
        ];

        $pdf = Pdf::loadView('admin.dashboard.pdf', $pdfData);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    /**
     * Generate PDF untuk Admin OPD Dashboard
     */
    public function generateAdminOPDPDF(int $opdId, $periodeId = null)
    {
        $data = $this->reportService->getAdminOPDDashboardData($opdId, $periodeId);
        $opd = OPD::find($opdId);
        $periode = $periodeId ? PeriodeSurvei::find($periodeId) : null;
        $periodes = $this->reportService->getPeriods();

        $pdfData = [
            'data' => $data,
            'opd' => $opd,
            'periode' => $periode,
            'periodes' => $periodes,
            'periodeId' => $periodeId,
            'generated_at' => now()->format('d/m/Y H:i'),
            'role' => 'Admin OPD',
            'title' => 'Dashboard OPD - ' . ($opd ? $opd->nama_opd : ''),
        ];

        $pdf = Pdf::loadView('admin.opd.dashboard-pdf', $pdfData);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    /**
     * Generate PDF untuk Pimpinan OPD Dashboard
     */
    public function generatePimpinanOPDPDF(int $opdId, $periodeId = null)
    {
        // Sama seperti Admin OPD, tapi view berbeda (lebih ringkas)
        $data = $this->reportService->getAdminOPDDashboardData($opdId, $periodeId);
        $opd = OPD::find($opdId);
        $periode = $periodeId ? PeriodeSurvei::find($periodeId) : null;
        $periodes = $this->reportService->getPeriods();

        $pdfData = [
            'data' => $data,
            'opd' => $opd,
            'periode' => $periode,
            'periodes' => $periodes,
            'periodeId' => $periodeId,
            'generated_at' => now()->format('d/m/Y H:i'),
            'role' => 'Pimpinan OPD',
            'title' => 'Dashboard Pimpinan OPD - ' . ($opd ? $opd->nama_opd : ''),
        ];

        $pdf = Pdf::loadView('admin.pimpinan.dashboard-pdf', $pdfData);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    /**
     * Generate PDF untuk Pimpinan Utama Dashboard
     */
    public function generatePimpinanUtamaPDF($periodeId = null, $opdId = null)
    {
        $allStats = $this->reportService->getAllOPDStats($periodeId);
        
        // Filter berdasarkan OPD jika dipilih
        if ($opdId && $opdId !== 'all') {
            $allStats = array_filter($allStats, function ($item) use ($opdId) {
                return $item['opd']->id == $opdId;
            });
            $allStats = array_values($allStats);
        }

        // Hitung statistik
        $totalResponses = collect($allStats)->sum('total_responses');
        $ikmData = collect($allStats)->filter(function ($item) {
            return $item['ikm'] !== null;
        });
        $ikmOverall = $ikmData->isNotEmpty() ? round($ikmData->avg('ikm.ikm'), 2) : null;

        // Chart data
        $chartData = $this->prepareChartData($allStats);

        $periode = $periodeId ? PeriodeSurvei::find($periodeId) : null;
        $periodes = $this->reportService->getPeriods();

        $pdfData = [
            'allStats' => $allStats,
            'totalResponses' => $totalResponses,
            'ikmOverall' => $ikmOverall,
            'chartData' => $chartData,
            'periode' => $periode,
            'periodes' => $periodes,
            'periodeId' => $periodeId,
            'opdId' => $opdId,
            'generated_at' => now()->format('d/m/Y H:i'),
            'role' => 'Pimpinan Utama',
            'title' => 'Dashboard Pimpinan Utama',
        ];

        $pdf = Pdf::loadView('admin.pimpinan-utama.dashboard-pdf', $pdfData);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf;
    }

    private function prepareChartData($allStats)
    {
        $labels = [];
        $data = [];
        $colors = [];

        foreach ($allStats as $stat) {
            if ($stat['ikm'] !== null) {
                $labels[] = $stat['opd']->nama_opd;
                $data[] = $stat['ikm']['ikm'];
                $category = $stat['ikm']['category'];
                $colors[] = $category['color'] ?? '#3B82F6';
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }
}