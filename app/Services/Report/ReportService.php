<?php
// app/Services/Report/ReportService.php

namespace App\Services\Report;

use App\Models\OPD;
use App\Models\Layanan;
use App\Models\SurveiResponse;
use App\Models\PeriodeSurvei;
use App\Services\Stats\IKMStatsService;
use App\Services\Stats\DashboardStatsService;
use Illuminate\Support\Collection;

class ReportService
{
    protected IKMStatsService $ikmStats;
    protected DashboardStatsService $dashboardStats;

    public function __construct(
        IKMStatsService $ikmStats,
        DashboardStatsService $dashboardStats
    ) {
        $this->ikmStats = $ikmStats;
        $this->dashboardStats = $dashboardStats;
    }

    /**
     * Get Super Admin Dashboard Data
     */
    public function getSuperAdminDashboardData($periodeId = null): array
    {
        return $this->dashboardStats->getSuperAdminData($periodeId);
    }

    /**
     * Get Admin OPD Dashboard Data
     */
    public function getAdminOPDDashboardData(int $opdId, $periodeId = null): array
    {
        return $this->dashboardStats->getAdminOPDData($opdId, $periodeId);
    }

    /**
     * Get all OPD stats for Pimpinan Utama
     */
    public function getAllOPDStats($periodeId = null): array
    {
        $opds = OPD::where('is_active', true)->get();
        $stats = [];

        foreach ($opds as $opd) {
            $responses = $this->getResponses($opd->id, $periodeId);
            $ikm = $this->ikmStats->calculateFromResponses($responses);

            $stats[] = [
                'opd' => $opd,
                'total_responses' => $responses->count(),
                'ikm' => $ikm,
                'per_layanan' => $this->getLayananStats($opd->id, $periodeId),
            ];
        }

        return $stats;
    }

    /**
     * Get periods for filter
     */
    public function getPeriods()
    {
        return PeriodeSurvei::orderBy('created_at', 'desc')->get();
    }

    // ==========================================
    // PRIVATE METHODS - Will be moved to other services
    // ==========================================

    private function getResponses($opdId = null, $periodeId = null): Collection
    {
        $query = SurveiResponse::where('status', 'completed')
            ->with(['jawabans', 'responden', 'layanan']);

        if ($opdId) {
            $query->whereHas('layanan', function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            });
        }

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        return $query->get();
    }

    private function getLayananStats($opdId, $periodeId = null): array
    {
        $layanans = Layanan::where('opd_id', $opdId)
            ->where('is_active', true)
            ->get();

        $result = [];
        foreach ($layanans as $layanan) {
            $responses = $this->getResponses($opdId, $periodeId)
                ->filter(function ($item) use ($layanan) {
                    return $item->layanan_id === $layanan->id;
                });

            $ikm = $this->ikmStats->calculateFromResponses($responses);

            $result[] = [
                'id' => $layanan->id,
                'nama_layanan' => $layanan->nama_layanan,
                'total' => $responses->count(),
                'ikm' => $ikm,
            ];
        }

        return $result;
    }
}