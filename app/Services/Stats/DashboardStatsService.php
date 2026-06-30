<?php
// app/Services/Stats/DashboardStatsService.php

namespace App\Services\Stats;

use App\Models\OPD;
use App\Models\Layanan;
use App\Models\SurveiResponse;
use App\Models\PeriodeSurvei;
use App\Models\UnsurSurvei;
use App\Constants\IKMConstants;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    protected IKMStatsService $ikmStats;

    public function __construct(IKMStatsService $ikmStats)
    {
        $this->ikmStats = $ikmStats;
    }

    /**
     * Get Super Admin Dashboard Data
     */
    public function getSuperAdminData($periodeId = null): array
    {
        $cacheKey = 'dashboard_super_admin_' . ($periodeId ?? 'all');
        
        return Cache::remember($cacheKey, IKMConstants::CACHE_DURATION, function () use ($periodeId) {
            $responses = $this->getResponses(null, $periodeId);

            // 1. IKM per OPD
            $ikmPerOPD = $this->ikmStats->calculatePerOPD($responses);

            // 2. Tren IKM per OPD
            $trenData = $this->calculateTrendPerOPD($periodeId);

            // 3. Distribusi Survei per OPD
            $distribusiOPD = $this->getDistribusiOPD($responses);

            // 4. Per Unsur per OPD
            $unsurPerOPD = $this->calculateUnsurPerOPD($responses);

            // 5. Demografi Responden
            $demografi = $this->getDemografi($responses);

            // 6. 5 OPD Terbaik & Terendah
            $topBottomOPD = $this->ikmStats->getTopBottomOPD($ikmPerOPD);

            // 7. Detail Responden Terbaru
            $recentRespondents = $this->getRecentRespondents($responses);

            // 8. Statistik Umum
            $stats = [
                'total_opd' => OPD::where('is_active', true)->count(),
                'total_layanan' => Layanan::where('is_active', true)->count(),
                'total_responden' => $responses->count(),
                'ikm_overall' => $this->ikmStats->calculateOverall($responses),
                'active_periode' => PeriodeSurvei::where('is_active', true)
                    ->where('tanggal_mulai', '<=', now())
                    ->where('tanggal_selesai', '>=', now())
                    ->first(),
            ];

            return [
                'stats' => $stats,
                'ikm_per_opd' => $ikmPerOPD,
                'tren_data' => $trenData,
                'distribusi_opd' => $distribusiOPD,
                'unsur_per_opd' => $unsurPerOPD,
                'demografi' => $demografi,
                'top_bottom_opd' => $topBottomOPD,
                'recent_respondents' => $recentRespondents,
            ];
        });
    }

    /**
     * Get Admin OPD Dashboard Data
     */
    public function getAdminOPDData(int $opdId, $periodeId = null): array
    {
        $cacheKey = 'dashboard_admin_opd_' . $opdId . '_' . ($periodeId ?? 'all');
        
        return Cache::remember($cacheKey, IKMConstants::CACHE_DURATION, function () use ($opdId, $periodeId) {
            $responses = $this->getResponses($opdId, $periodeId);

            // 1. IKM per Layanan
            $ikmPerLayanan = $this->ikmStats->calculatePerLayanan($responses);

            // 2. Tren IKM
            $trenData = $this->calculateTrenLayanan($opdId, $periodeId);

            // 3. Per Unsur
            $unsurData = $this->calculateUnsurFromResponses($responses);

            // 4. Distribusi Survei per Layanan
            $distribusiLayanan = $this->getDistribusiLayanan($responses);

            // 5. Demografi Responden
            $demografi = $this->getDemografi($responses);

            // 6. Unsur Terkuat & Terlemah
            $unsurTerkuat = null;
            $unsurTerlemah = null;
            if (!empty($unsurData)) {
                $unsurTerkuat = $this->getStrongestWeakestUnsur($unsurData, 'max');
                $unsurTerlemah = $this->getStrongestWeakestUnsur($unsurData, 'min');
            }

            // 7. Detail Responden Terbaru
            $recentRespondents = $this->getRecentRespondentsForOPD($responses);

            // 8. Statistik Umum
            $ikmOverall = $this->ikmStats->calculateFromResponses($responses);
            $stats = [
                'total_survei' => $responses->count(),
                'total_layanan' => Layanan::where('opd_id', $opdId)->where('is_active', true)->count(),
                'ikm' => $ikmOverall,
                'target_ikm' => IKMConstants::TARGET_IKM,
            ];

            // 9. Layanan Terbaik & Terendah
            $layananTerbaik = null;
            $layananTerendah = null;
            if (!empty($ikmPerLayanan)) {
                $sorted = collect($ikmPerLayanan)->sortByDesc('ikm')->values();
                $layananTerbaik = $sorted->first();
                $layananTerendah = $sorted->last();
            }

            return [
                'stats' => $stats,
                'ikm_per_layanan' => $ikmPerLayanan,
                'tren_data' => $trenData,
                'unsur_data' => $unsurData,
                'distribusi_layanan' => $distribusiLayanan,
                'demografi' => $demografi,
                'unsur_terkuat' => $unsurTerkuat,
                'unsur_terlemah' => $unsurTerlemah,
                'layanan_terbaik' => $layananTerbaik,
                'layanan_terendah' => $layananTerendah,
                'recent_respondents' => $recentRespondents,
            ];
        });
    }

    /**
     * Get Responses with eager loading
     */
    private function getResponses($opdId = null, $periodeId = null): Collection
    {
        $query = SurveiResponse::where('status', 'completed')
            ->with(['responden', 'layanan.opd', 'jawabans.pertanyaan.unsur']);

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        if ($opdId) {
            $query->whereHas('layanan', function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            });
        }

        return $query->get();
    }

    /**
     * Calculate Trend per OPD (Multi-Line)
     */
    private function calculateTrendPerOPD($periodeId = null): array
    {
        $periodes = PeriodeSurvei::orderBy('tanggal_mulai')->get();
        if ($periodeId) {
            $periodes = $periodes->where('id', $periodeId);
        }

        $trendData = [];
        $opds = OPD::where('is_active', true)->get();

        foreach ($opds as $opd) {
            $data = [];
            foreach ($periodes as $periode) {
                $responses = $this->getResponses($opd->id, $periode->id);
                $ikm = $this->ikmStats->calculateFromResponses($responses);
                $data[] = [
                    'periode' => $periode->nama_periode,
                    'ikm' => $ikm ? $ikm['ikm'] : null,
                ];
            }

            if (count(array_filter($data, function ($d) { return $d['ikm'] !== null; })) > 0) {
                $trendData[] = [
                    'opd_id' => $opd->id,
                    'nama_opd' => $opd->nama_opd,
                    'data' => $data,
                ];
            }
        }

        return $trendData;
    }

    /**
     * Calculate Trend per Layanan
     */
    private function calculateTrenLayanan($opdId, $periodeId = null): array
    {
        $periodes = PeriodeSurvei::orderBy('tanggal_mulai')->get();
        if ($periodeId) {
            $periodes = $periodes->where('id', $periodeId);
        }

        $layanans = Layanan::where('opd_id', $opdId)->where('is_active', true)->get();
        $result = [];

        foreach ($layanans as $layanan) {
            $data = [];
            foreach ($periodes as $periode) {
                $responses = SurveiResponse::where('layanan_id', $layanan->id)
                    ->where('status', 'completed')
                    ->where('periode_id', $periode->id)
                    ->with('jawabans')
                    ->get();

                $ikm = $this->ikmStats->calculateFromResponses($responses);
                $data[] = [
                    'periode' => $periode->nama_periode,
                    'ikm' => $ikm ? $ikm['ikm'] : null,
                ];
            }

            if (count(array_filter($data, function ($d) { return $d['ikm'] !== null; })) > 0) {
                $result[] = [
                    'layanan_id' => $layanan->id,
                    'nama_layanan' => $layanan->nama_layanan,
                    'data' => $data,
                ];
            }
        }

        return $result;
    }

    /**
     * Get Distribusi per OPD
     */
    private function getDistribusiOPD($responses): array
    {
        $distribusi = [];
        $grouped = $responses->groupBy(function ($item) {
            return $item->layanan->opd_id ?? 'unknown';
        });

        foreach ($grouped as $opdId => $items) {
            $opd = OPD::find($opdId);
            if ($opd) {
                $distribusi[] = [
                    'label' => $opd->nama_opd,
                    'value' => $items->count(),
                ];
            }
        }

        return $distribusi;
    }

    /**
     * Get Distribusi per Layanan
     */
    private function getDistribusiLayanan($responses): array
    {
        $distribusi = [];
        $grouped = $responses->groupBy('layanan_id');

        foreach ($grouped as $layananId => $items) {
            $layanan = Layanan::find($layananId);
            if ($layanan) {
                $distribusi[] = [
                    'label' => $layanan->nama_layanan,
                    'value' => $items->count(),
                ];
            }
        }

        return $distribusi;
    }

    /**
     * Calculate Unsur per OPD (Radar Chart)
     */
    private function calculateUnsurPerOPD($responses): array
    {
        $result = [];
        $grouped = $responses->groupBy(function ($item) {
            return $item->layanan->opd_id ?? 'unknown';
        });

        foreach ($grouped as $opdId => $items) {
            $opd = OPD::find($opdId);
            if (!$opd) continue;

            $unsurData = $this->calculateUnsurFromResponses($items);
            if (!empty($unsurData)) {
                $result[] = [
                    'opd_id' => $opd->id,
                    'nama_opd' => $opd->nama_opd,
                    'data' => $unsurData,
                ];
            }
        }

        return $result;
    }

    /**
     * Calculate Unsur from Responses
     */
    private function calculateUnsurFromResponses($responses): array
    {
        $unsurValues = [];
        foreach ($responses as $response) {
            foreach ($response->jawabans as $jawaban) {
                $unsurId = $jawaban->pertanyaan->unsur_id;
                if (!isset($unsurValues[$unsurId])) {
                    $unsurValues[$unsurId] = [];
                }
                $unsurValues[$unsurId][] = $jawaban->nilai;
            }
        }

        $result = [];
        foreach ($unsurValues as $unsurId => $values) {
            $unsur = UnsurSurvei::find($unsurId);
            if ($unsur) {
                $result[] = [
                    'unsur' => $unsur->nama_unsur,
                    'nilai' => round(array_sum($values) / count($values), 2),
                ];
            }
        }

        usort($result, function ($a, $b) {
            return strcmp($a['unsur'], $b['unsur']);
        });

        return $result;
    }

    /**
     * Get Demografi Responden
     */
    private function getDemografi($responses): array
    {
        $demografi = [
            'gender' => ['L' => 0, 'P' => 0],
            'pendidikan' => [],
            'pekerjaan' => [],
            'usia' => ['<20' => 0, '20-30' => 0, '31-40' => 0, '41-50' => 0, '>50' => 0],
        ];

        foreach ($responses as $response) {
            $responden = $response->responden;
            
            if ($responden->jenis_kelamin == 'L') {
                $demografi['gender']['L']++;
            } else {
                $demografi['gender']['P']++;
            }

            $pend = $responden->pendidikan;
            if (!isset($demografi['pendidikan'][$pend])) {
                $demografi['pendidikan'][$pend] = 0;
            }
            $demografi['pendidikan'][$pend]++;

            $kerja = $responden->pekerjaan;
            if (!isset($demografi['pekerjaan'][$kerja])) {
                $demografi['pekerjaan'][$kerja] = 0;
            }
            $demografi['pekerjaan'][$kerja]++;

            $usia = $responden->usia;
            if ($usia < 20) $demografi['usia']['<20']++;
            elseif ($usia <= 30) $demografi['usia']['20-30']++;
            elseif ($usia <= 40) $demografi['usia']['31-40']++;
            elseif ($usia <= 50) $demografi['usia']['41-50']++;
            else $demografi['usia']['>50']++;
        }

        return $demografi;
    }

    /**
     * Get Recent Respondents (Super Admin)
     */
    private function getRecentRespondents($responses, $limit = 20): array
    {
        return $responses->sortByDesc('submitted_at')->take($limit)->map(function ($item) {
            $nilai = $item->jawabans->pluck('nilai')->toArray();
            
            // ✅ PERBAIKAN: IKM = (Total/9) × 25
            $ikm = null;
            if (count($nilai) === IKMConstants::UNSUUR_COUNT) {
                $average = array_sum($nilai) / IKMConstants::UNSUUR_COUNT;
                $ikm = round($average * IKMConstants::KONVERSI_IKM, 2); // × 25
            }
            
            return [
                'nama' => $item->responden->nama,
                'usia' => $item->responden->usia,
                'jenis_kelamin' => $item->responden->jenis_kelamin,
                'pendidikan' => $item->responden->pendidikan,
                'pekerjaan' => $item->responden->pekerjaan,
                'opd' => $item->layanan->opd->nama_opd ?? '-',
                'layanan' => $item->layanan->nama_layanan,
                'ikm' => $ikm,
                'tanggal' => $item->submitted_at->format('d/m/Y H:i'),
            ];
        })->toArray();
    }

    /**
     * Get Recent Respondents for OPD
     */
    private function getRecentRespondentsForOPD($responses, $limit = 20): array
    {
        return $responses->sortByDesc('submitted_at')->take($limit)->map(function ($item) {
            $nilai = $item->jawabans->pluck('nilai')->toArray();
            
            // ✅ PERBAIKAN: IKM = (Total/9) × 25
            $ikm = null;
            if (count($nilai) === IKMConstants::UNSUUR_COUNT) {
                $average = array_sum($nilai) / IKMConstants::UNSUUR_COUNT;
                $ikm = round($average * IKMConstants::KONVERSI_IKM, 2); // × 25
            }
            
            return [
                'nama' => $item->responden->nama,
                'usia' => $item->responden->usia,
                'jenis_kelamin' => $item->responden->jenis_kelamin,
                'pendidikan' => $item->responden->pendidikan,
                'pekerjaan' => $item->responden->pekerjaan,
                'layanan' => $item->layanan->nama_layanan,
                'ikm' => $ikm,
                'tanggal' => $item->submitted_at->format('d/m/Y H:i'),
            ];
        })->toArray();
    }

    /**
     * Get Strongest/Weakest Unsur
     */
    private function getStrongestWeakestUnsur($unsurData, $type = 'max')
    {
        if (empty($unsurData)) return null;

        $result = null;
        $value = $type === 'max' ? -INF : INF;
        
        foreach ($unsurData as $item) {
            if ($type === 'max' && $item['nilai'] > $value) {
                $value = $item['nilai'];
                $result = $item;
            } elseif ($type === 'min' && $item['nilai'] < $value) {
                $value = $item['nilai'];
                $result = $item;
            }
        }

        return $result;
    }
}