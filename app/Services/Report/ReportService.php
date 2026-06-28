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
     * Get all OPD stats dengan filter (untuk Pimpinan Utama)
     */
    public function getAllOPDStats($periodeId = null): array
    {
        $opds = OPD::where('is_active', true)->get();
        $stats = [];

        foreach ($opds as $opd) {
            $query = SurveiResponse::whereHas('layanan', function ($q) use ($opd) {
                $q->where('opd_id', $opd->id);
            })->where('status', 'completed');

            if ($periodeId) {
                $query->where('periode_id', $periodeId);
            }

            $responses = $query->with('jawabans')->get();
            $ikm = $this->calculateIKMFromResponses($responses);

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
     * Get OPD stats untuk single OPD (untuk Admin OPD & Pimpinan OPD)
     */
    public function getOPDStats(int $opdId, $periodeId = null): array
    {
        $query = SurveiResponse::whereHas('layanan', function ($q) use ($opdId) {
            $q->where('opd_id', $opdId);
        })->where('status', 'completed');

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $responses = $query->with(['responden', 'layanan', 'jawabans.pertanyaan.unsur'])->get();
        $ikm = $this->calculateIKMFromResponses($responses);

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

        // Data responden terbaru (20 data)
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
                $nilai = $item->jawabans->pluck('nilai')->toArray();
                $ikm = count($nilai) === 9 ? round((array_sum($nilai) / 9) * 25, 2) : null;
                return [
                    'nama' => $item->responden->nama,
                    'usia' => $item->responden->usia,
                    'jenis_kelamin' => $item->responden->jenis_kelamin,
                    'pendidikan' => $item->responden->pendidikan,
                    'pekerjaan' => $item->responden->pekerjaan,
                    'layanan' => $item->layanan->nama_layanan,
                    'ikm' => $ikm,
                    'submitted_at' => $item->submitted_at,
                ];
            });

        // Unsur terkuat dan terlemah
        $unsurValues = $this->calculateUnsurValues($responses);
        $unsurTerkuat = !empty($unsurValues) ? $this->getStrongestWeakestUnsur($unsurValues, 'max') : null;
        $unsurTerlemah = !empty($unsurValues) ? $this->getStrongestWeakestUnsur($unsurValues, 'min') : null;

        return [
            'total_responses' => $responses->count(),
            'ikm' => $ikm,
            'per_layanan' => $perLayanan,
            'recent_respondents' => $recentRespondents,
            'unsur_terkuat' => $unsurTerkuat,
            'unsur_terlemah' => $unsurTerlemah,
        ];
    }

    /**
     * Get data untuk Super Admin Dashboard
     */
    public function getSuperAdminDashboardData($periodeId = null)
    {
        $query = SurveiResponse::where('status', 'completed');
        
        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $responses = $query->with(['responden', 'layanan.opd', 'jawabans.pertanyaan.unsur'])->get();

        // 1. IKM per OPD
        $ikmPerOPD = $this->calculateIKMPerOPD($responses);

        // 2. Tren IKM per OPD (Multi-Line)
        $trenData = $this->calculateTrendPerOPD($periodeId);

        // 3. Distribusi Survei per OPD
        $distribusiOPD = $this->getDistribusiOPD($responses);

        // 4. Per Unsur per OPD (Radar)
        $unsurPerOPD = $this->calculateUnsurPerOPD($responses);

        // 5. Demografi Responden
        $demografi = $this->getDemografi($responses);

        // 6. 5 OPD Terbaik & Terendah
        $topBottomOPD = $this->getTopBottomOPD($ikmPerOPD);

        // 7. Detail Responden Terbaru
        $recentRespondents = $this->getRecentRespondents($responses);

        // 8. Statistik Umum
        $stats = [
            'total_opd' => OPD::where('is_active', true)->count(),
            'total_layanan' => Layanan::where('is_active', true)->count(),
            'total_responden' => $responses->count(),
            'ikm_overall' => $this->calculateOverallIKM($responses),
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
    }

    /**
     * Get periods for filter
     */
    public function getPeriods()
    {
        return PeriodeSurvei::orderBy('created_at', 'desc')->get();
    }

    /**
     * Get data untuk Admin OPD Dashboard
     */
    public function getAdminOPDDashboardData(int $opdId, $periodeId = null)
    {
        $query = SurveiResponse::whereHas('layanan', function ($q) use ($opdId) {
            $q->where('opd_id', $opdId);
        })->where('status', 'completed');

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $responses = $query->with(['responden', 'layanan', 'jawabans.pertanyaan.unsur'])->get();

        // 1. IKM per Layanan
        $ikmPerLayanan = $this->calculateIKMPerLayanan($responses);

        // 2. Tren IKM (Line Chart)
        $trenData = $this->calculateTrenLayanan($opdId, $periodeId);

        // 3. Per Unsur (Radar)
        $unsurData = $this->calculateUnsurFromResponses($responses);

        // 4. Distribusi Survei per Layanan (Pie)
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
        $ikmOverall = $this->calculateIKMFromResponses($responses);
        $stats = [
            'total_survei' => $responses->count(),
            'total_layanan' => Layanan::where('opd_id', $opdId)->where('is_active', true)->count(),
            'ikm' => $ikmOverall,
            'target_ikm' => 88.31,
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
    }

    // ==========================================
    // PRIVATE METHODS
    // ==========================================

    private function calculateIKMFromResponses($responses)
    {
        if ($responses->isEmpty()) return null;

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

        if ($count === 0) return null;

        $averageIKM = round($totalIKM / $count, 2);
        return [
            'ikm' => $averageIKM,
            'category' => $this->ikmCalculator->getCategory($averageIKM),
        ];
    }

    private function calculateOverallIKM($responses)
    {
        $result = $this->calculateIKMFromResponses($responses);
        return $result ? $result['ikm'] : null;
    }

    private function calculateIKMPerOPD($responses)
    {
        $opdData = [];
        $grouped = $responses->groupBy(function ($item) {
            return $item->layanan->opd_id ?? 'unknown';
        });

        foreach ($grouped as $opdId => $items) {
            $opd = OPD::find($opdId);
            if (!$opd) continue;

            $ikm = $this->calculateIKMFromResponses($items);
            if ($ikm) {
                $opdData[] = [
                    'opd_id' => $opd->id,
                    'nama_opd' => $opd->nama_opd,
                    'total_survei' => $items->count(),
                    'ikm' => $ikm['ikm'],
                    'category' => $ikm['category'],
                ];
            }
        }

        usort($opdData, function ($a, $b) {
            return $b['ikm'] <=> $a['ikm'];
        });

        return $opdData;
    }

    private function calculateTrendPerOPD($periodeId = null)
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
                $responses = SurveiResponse::whereHas('layanan', function ($q) use ($opd) {
                    $q->where('opd_id', $opd->id);
                })->where('status', 'completed')
                  ->where('periode_id', $periode->id)
                  ->with('jawabans')
                  ->get();

                $ikm = $this->calculateIKMFromResponses($responses);
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

    private function getDistribusiOPD($responses)
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

    private function calculateUnsurPerOPD($responses)
    {
        $result = [];
        $grouped = $responses->groupBy(function ($item) {
            return $item->layanan->opd_id ?? 'unknown';
        });

        foreach ($grouped as $opdId => $items) {
            $opd = OPD::find($opdId);
            if (!$opd) continue;

            $unsurValues = [];
            foreach ($items as $response) {
                foreach ($response->jawabans as $jawaban) {
                    $unsurId = $jawaban->pertanyaan->unsur_id;
                    if (!isset($unsurValues[$unsurId])) {
                        $unsurValues[$unsurId] = [];
                    }
                    $unsurValues[$unsurId][] = $jawaban->nilai;
                }
            }

            $unsurData = [];
            foreach ($unsurValues as $unsurId => $values) {
                $unsur = \App\Models\UnsurSurvei::find($unsurId);
                if ($unsur) {
                    $unsurData[] = [
                        'unsur' => $unsur->nama_unsur,
                        'nilai' => round(array_sum($values) / count($values), 2),
                    ];
                }
            }

            $result[] = [
                'opd_id' => $opd->id,
                'nama_opd' => $opd->nama_opd,
                'data' => $unsurData,
            ];
        }

        return $result;
    }

    private function getDemografi($responses)
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

    private function getTopBottomOPD($ikmPerOPD)
    {
        $top = array_slice($ikmPerOPD, 0, 5);
        $bottom = array_slice($ikmPerOPD, -5, 5);
        return ['top' => $top, 'bottom' => $bottom];
    }

    private function getRecentRespondents($responses, $limit = 20)
    {
        return $responses->sortByDesc('submitted_at')->take($limit)->map(function ($item) {
            $nilai = $item->jawabans->pluck('nilai')->toArray();
            $ikm = count($nilai) === 9 ? round((array_sum($nilai) / 9) * 25, 2) : null;
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

    private function getLayananStats($opdId, $periodeId = null)
    {
        $layanans = Layanan::where('opd_id', $opdId)
            ->where('is_active', true)
            ->get();

        $result = [];
        foreach ($layanans as $layanan) {
            $query = SurveiResponse::where('layanan_id', $layanan->id)
                ->where('status', 'completed');
            
            if ($periodeId) {
                $query->where('periode_id', $periodeId);
            }

            $responses = $query->with('jawabans')->get();
            $ikm = $this->calculateIKMFromResponses($responses);

            $result[] = [
                'id' => $layanan->id,
                'nama_layanan' => $layanan->nama_layanan,
                'total' => $responses->count(),
                'ikm' => $ikm,
            ];
        }

        return $result;
    }

    private function calculateUnsurValues($responses)
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
            $unsur = \App\Models\UnsurSurvei::find($unsurId);
            if ($unsur) {
                $result[$unsur->nama_unsur] = round(array_sum($values) / count($values), 2);
            }
        }

        return $result;
    }

    /**
     * Get Strongest/Weakest Unsur
     * 
     * @param array $unsurData Array dengan format [['unsur' => '...', 'nilai' => ...]]
     * @param string $type 'max' untuk terkuat, 'min' untuk terlemah
     * @return array|null
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

    /**
     * Calculate IKM per Layanan
     */
    private function calculateIKMPerLayanan($responses)
    {
        $result = [];
        $grouped = $responses->groupBy('layanan_id');

        foreach ($grouped as $layananId => $items) {
            $layanan = Layanan::find($layananId);
            if (!$layanan) continue;

            $ikm = $this->calculateIKMFromResponses($items);
            if ($ikm) {
                $result[] = [
                    'layanan_id' => $layanan->id,
                    'nama_layanan' => $layanan->nama_layanan,
                    'total_survei' => $items->count(),
                    'ikm' => $ikm['ikm'],
                    'category' => $ikm['category'],
                ];
            }
        }

        usort($result, function ($a, $b) {
            return $b['ikm'] <=> $a['ikm'];
        });

        return $result;
    }

    /**
     * Calculate Trend per Layanan
     */
    private function calculateTrenLayanan($opdId, $periodeId = null)
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

                $ikm = $this->calculateIKMFromResponses($responses);
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
     * Calculate Unsur from Responses
     */
    private function calculateUnsurFromResponses($responses)
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
            $unsur = \App\Models\UnsurSurvei::find($unsurId);
            if ($unsur) {
                $result[] = [
                    'unsur' => $unsur->nama_unsur,
                    'nilai' => round(array_sum($values) / count($values), 2),
                ];
            }
        }

        // Sort by unsur order
        usort($result, function ($a, $b) {
            return strcmp($a['unsur'], $b['unsur']);
        });

        return $result;
    }

    /**
     * Get Distribusi per Layanan
     */
    private function getDistribusiLayanan($responses)
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
     * Get Recent Respondents for OPD
     */
    private function getRecentRespondentsForOPD($responses, $limit = 20)
    {
        return $responses->sortByDesc('submitted_at')->take($limit)->map(function ($item) {
            $nilai = $item->jawabans->pluck('nilai')->toArray();
            $ikm = count($nilai) === 9 ? round((array_sum($nilai) / 9) * 25, 2) : null;
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
}