<?php
// app/Http/Controllers/Admin/PimpinanUtama/DashboardController.php

namespace App\Http\Controllers\Admin\PimpinanUtama;

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
        $selectedOPD = $request->opd_id;

        // Ambil semua periode untuk filter
        $periodes = $this->reportService->getPeriods();

        // Ambil statistik dengan filter
        $allStats = $this->reportService->getAllOPDStats($periodeId);

        // Filter berdasarkan OPD jika dipilih
        if ($selectedOPD && $selectedOPD !== 'all') {
            $allStats = array_filter($allStats, function ($item) use ($selectedOPD) {
                return $item['opd']->id == $selectedOPD;
            });
            // Re-index array
            $allStats = array_values($allStats);
        }

        // Hitung total keseluruhan
        $totalResponses = collect($allStats)->sum('total_responses');
        $totalOPD = count($allStats);

        // IKM keseluruhan
        $ikmOverall = null;
        $ikmData = collect($allStats)->filter(function ($item) {
            return $item['ikm'] !== null;
        });

        if ($ikmData->isNotEmpty()) {
            $avgIKM = $ikmData->avg('ikm.ikm');
            $ikmOverall = round($avgIKM, 2);
        }

        // OPD dengan IKM tertinggi dan terendah
        $sortedByIKM = $ikmData->sortByDesc('ikm.ikm');
        $topOPD = $sortedByIKM->first();
        $bottomOPD = $sortedByIKM->last();

        // Data untuk chart
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