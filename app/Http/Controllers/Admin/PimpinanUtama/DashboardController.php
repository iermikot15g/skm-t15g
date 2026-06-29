<?php
// app/Http/Controllers/Admin/PimpinanUtama/DashboardController.php

namespace App\Http\Controllers\Admin\PimpinanUtama;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;

class DashboardController extends BaseAdminController
{
    protected function getDashboardView(): string
    {
        return 'admin.pimpinan-utama.dashboard';
    }

    protected function getPDFView(): string
    {
        return 'admin.pimpinan-utama.dashboard-pdf';
    }

    protected function getExportRouteName(): string
    {
        return 'admin.utama.dashboard.export-pdf';
    }

    protected function getPDFFilename(): string
    {
        return 'Dashboard_PimpinanUtama_' . date('Ymd') . '.pdf';
    }

    protected function getDashboardData(Request $request): array
    {
        $periodeId = $request->periode_id;
        $selectedOPD = $request->opd_id;

        $allStats = $this->reportService->getAllOPDStats($periodeId);

        if ($selectedOPD && $selectedOPD !== 'all') {
            $allStats = array_filter($allStats, function ($item) use ($selectedOPD) {
                return $item['opd']->id == $selectedOPD;
            });
            $allStats = array_values($allStats);
        }

        return [
            'allStats' => $allStats,
            'selectedOPD' => $selectedOPD,
        ];
    }

    protected function generatePDF(Request $request): \Barryvdh\DomPDF\PDF
    {
        $periodeId = $request->periode_id;
        $opdId = $request->opd_id;
        return $this->pdfGenerator->generatePimpinanUtamaPDF($periodeId, $opdId);
    }

    /**
     * Override index to include additional data
     */
    public function index(Request $request)
    {
        $data = $this->getDashboardData($request);
        $periodes = $this->reportService->getPeriods();
        $periodeId = $request->periode_id;
        $selectedOPD = $request->opd_id;

        // Calculate stats
        $allStats = $data['allStats'];
        $totalResponses = collect($allStats)->sum('total_responses');
        $totalOPD = count($allStats);
        
        $ikmData = collect($allStats)->filter(function ($item) {
            return $item['ikm'] !== null;
        });
        $ikmOverall = $ikmData->isNotEmpty() ? round($ikmData->avg('ikm.ikm'), 2) : null;

        $sortedByIKM = $ikmData->sortByDesc('ikm.ikm');
        $topOPD = $sortedByIKM->first();
        $bottomOPD = $sortedByIKM->last();

        $chartData = $this->prepareChartData($allStats);

        return view('admin.pimpinan-utama.dashboard', compact(
            'allStats',
            'totalResponses',
            'totalOPD',
            'ikmOverall',
            'topOPD',
            'bottomOPD',
            'chartData',
            'periodes',
            'periodeId',
            'selectedOPD'
        ));
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