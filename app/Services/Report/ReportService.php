<?php
// app/Services/Report/ReportService.php

namespace App\Services\Report;

use App\Models\OPD;
use App\Models\SurveiResponse;
use App\Models\Layanan;
use App\Models\PeriodeSurvei;
use App\Services\Survey\IKMCalculator;
use Illuminate\Support\Facades\DB;

class ReportService
{
    protected IKMCalculator $ikmCalculator;

    public function __construct(IKMCalculator $ikmCalculator)
    {
        $this->ikmCalculator = $ikmCalculator;
    }

    /**
     * Get dashboard stats for OPD dengan filter
     */
    public function getOPDStats(int $opdId, $periodeId = null): array
    {
        $query = SurveiResponse::whereHas('layanan', function ($q) use ($opdId) {
            $q->where('opd_id', $opdId);
        })->where('status', 'completed');

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $responses = $query->get();

        $totalResponses = $responses->count();
        $ikm = $this->calculateOPDIKM($responses);

        // Data per layanan
        $perLayanan = Layanan::where('opd_id', $opdId)
            ->where('is_active', true)
            ->withCount(['surveiResponses as total' => function ($q) use ($periodeId) {
                $q->where('status', 'completed');
                if ($periodeId) {
                    $q->where('periode_id', $periodeId);
                }
            }])
            ->get();

        // Data responden terbaru (tanpa NIK & HP) - 20 data terakhir
        $recentQuery = SurveiResponse::whereHas('layanan', function ($q) use ($opdId) {
            $q->where('opd_id', $opdId);
        })->where('status', 'completed');

        if ($periodeId) {
            $recentQuery->where('periode_id', $periodeId);
        }

        $recentRespondents = $recentQuery
            ->with(['responden', 'layanan'])
            ->orderBy('submitted_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->responden->nama,
                    'usia' => $item->responden->usia,
                    'jenis_kelamin' => $item->responden->jenis_kelamin,
                    'pendidikan' => $item->responden->pendidikan,
                    'pekerjaan' => $item->responden->pekerjaan,
                    'layanan' => $item->layanan->nama_layanan,
                    'submitted_at' => $item->submitted_at,
                ];
            });

        return [
            'total_responses' => $totalResponses,
            'ikm' => $ikm,
            'per_layanan' => $perLayanan,
            'recent_respondents' => $recentRespondents,
        ];
    }

    /**
     * Get all OPD stats dengan filter
     */
    public function getAllOPDStats($periodeId = null): array
    {
        $opds = OPD::where('is_active', true)->get();
        $stats = [];

        foreach ($opds as $opd) {
            $opdStats = $this->getOPDStats($opd->id, $periodeId);
            $stats[] = [
                'opd' => $opd,
                'total_responses' => $opdStats['total_responses'],
                'ikm' => $opdStats['ikm'],
                'per_layanan' => $opdStats['per_layanan'],
            ];
        }

        return $stats;
    }

    /**
     * Get single OPD stats for detail view
     */
    public function getSingleOPDStats(int $opdId, $periodeId = null): array
    {
        return $this->getOPDStats($opdId, $periodeId);
    }

    /**
     * Calculate IKM for OPD
     */
    private function calculateOPDIKM($responses): ?array
    {
        if ($responses->isEmpty()) {
            return null;
        }

        $totalIKM = 0;
        $count = 0;

        foreach ($responses as $response) {
            $jawabans = $response->jawabans->pluck('nilai')->toArray();
            if (count($jawabans) === 9) {
                $result = $this->ikmCalculator->calculate($jawabans);
                $totalIKM += $result['ikm'];
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        $averageIKM = round($totalIKM / $count, 2);
        $category = $this->ikmCalculator->getCategory($averageIKM);

        return [
            'ikm' => $averageIKM,
            'category' => $category,
            'total_responden' => $count,
        ];
    }

    /**
     * Get available periods for filter
     */
    public function getPeriods()
    {
        return PeriodeSurvei::orderBy('created_at', 'desc')->get();
    }
}